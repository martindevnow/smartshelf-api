<?php

namespace Martindevnow\Smartshelf\Engineering\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Martindevnow\Smartshelf\Engineering\Events\InventoryOutOfStock;
use Martindevnow\Smartshelf\Engineering\Events\LowStockAlert;
use Martindevnow\Smartshelf\Engineering\Events\PusherWasRestocked;
use Martindevnow\Smartshelf\Engineering\Inventory;
use Martindevnow\Smartshelf\Engineering\Pusher;
use Martindevnow\Smartshelf\Engineering\Reader;

class InventoryRepository {

    public function uploadInventoryDataForReader($payload, $header) {
        $reader = Reader::findByMacAddress($header['reader_mac']);

        $pushers = $reader->pushers()->with('product')->get();
        // TODO: CONTINUE FROM HERE ---

        // get the IDs of the pushers into a simple array
        $pusher_ids = $pushers->pluck('id')->all();

        // fetch the most recent inventory for those pushers
        $prev_invs = $this->fetchLatestInventoryForPushers($pusher_ids);

        // first, check the data type... if TAG, then convert it to PACK and save it on the payload as 'data'
        if (! isset($header['data_type']) || $header['data_type'] == 'tag')
        {
            // need to run getItemCount on each payload using product depth which is in the pushers collection
            foreach ($payload as &$p_data)
            {
                $pusher = $pushers->where('tray_tag', $p_data['tray_tag'])->first();
                if ($pusher == null)
                    dd ([
                        'pusher'        => $pusher,
                        'tray_tag'      => $p_data['tray_tag'],
                        'allPushers'    => Pusher::all()->toArray(),
                    ]);
                $p_data['data'] = $this->calculateItemCount($p_data, $pusher->product);
                $p_data['product_id'] = $pusher->product_id;
            }
        }
        else // the data is already item_count data... no calculation required
        {
            foreach ($payload as &$p_data)
            {
                $p_data['data_TAGSBLKED'] = null;
                $p_data['paddle_exposed'] = null;
            }
        }

        // then, we can treat it all the same.
        // check if the data is new, using data compared to item_count
        foreach ($payload as &$p_data)
        {
            $pusher = $pushers->where('tray_tag', $p_data['tray_tag'])->first();
            $prev_inv = $prev_invs->where('pusher_id', $pusher->id)->first();

            if ($prev_inv != null && $prev_inv->item_count == $p_data['data'])
            {
                // no change recorded
            }
            else
            {
                // calculate KPIs
                $p_data['pusher_ooses'] = $this->calculatePusherOOSes($payload, $p_data);
                $p_data['product_item_count'] = $this->calculateProductItemCount($payload, $p_data);
                $p_data['number_of_pushers'] = $this->calculateNumberOfPushers($payload, $p_data);
                $p_data['prev_oos_at'] = null;
                $p_data['prev_item_count'] = 0;

                if ($prev_inv != null)
                {
                    if ($prev_inv->oos == 1 && $p_data['data'] > 0) // it was restocked, so add the prev_oos_at field
                        $p_data['prev_oos_at'] = $prev_inv->created_at;
                    $p_data['prev_item_count'] = $prev_inv->item_count;
                }

                $new_invs[] = $pusher->inventories()->create([
                    'location_id'   => $pusher->location_id,
                    'reader_id'     => $pusher->reader_id,
                    'product_id'    => $pusher->product_id,
                    'tags_blocked'  => $p_data['data_TAGSBLKED'],
                    'paddle_exposed'=> $p_data['paddle_exposed'],
                    'item_count'    => $p_data['data'],
                    'status'        => $this->itemCountToStatus($p_data['data']),
                    'oos'           => ($p_data['data'] == 0),
                    'prev_item_count'       => $p_data['prev_item_count'],
                    'prev_oos_at'           => $p_data['prev_oos_at'],
                    'pusher_ooses'          => $p_data['pusher_ooses'],
                    'product_item_count'    => $p_data['product_item_count'],
                    'created_at'    => Carbon::createFromTimestamp($header['timestamp']),
                ]);

                $this->updatePusher($pusher, end($new_invs), $header);
            }
        }
    }

    public function calculatePusherOOSes(Array $allData, $p_data) {
        $count = array_reduce($allData, function($numOOS, $pusher) use ($p_data) {
            if ($pusher['product_id'] == $p_data['product_id']
                && $pusher['data'] == 0
            )
                $numOOS ++;
            return $numOOS;
        });
        if (is_array($count) && isEmpty($count) || is_null($count))
            return 0;
        return $count;
    }

    public function calculateProductItemCount(Array $allData, $p_data) {
        $count = array_reduce($allData, function($carry, $pusher) use ($p_data) {
            if ($pusher['product_id'] == $p_data['product_id'])
                $carry += $pusher['data'];
            return $carry;
        });
        if (is_array($count) && isEmpty($count) || is_null($count))
            return 0;
        return $count;
    }

    public function calculateNumberOfPushers(Array $allData, $p_data) {
        $count = array_reduce($allData, function($numPushers, $pusher) use ($p_data) {
            if ($pusher['product_id'] == $p_data['product_id'])
                $numPushers ++;
            return $numPushers;
        });
        if (is_array($count) && isEmpty($count) || is_null($count))
            return 0;
        return $count;
    }




    /**
     * @param $pusher_ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchLatestInventoryForPushers($pusher_ids) {
        $ids = implode(',', $pusher_ids);
        $inventory = DB::select(
            'SELECT i1.* ' //', p.pack_depth_in, p.id as prod_id
            . 'FROM inventories i1 
    LEFT JOIN inventories i2
    ON (i1.pusher_id = i2.pusher_id AND i1.created_at < i2.created_at)
    WHERE i2.id IS NULL '
            // AND p.id = i1.product_id
            . 'AND i1.pusher_id IN ('. $ids .');');
        return Inventory::hydrate($inventory);
    }


    protected function calculateItemCount($p_data, $product)
    {
        $tags_blocked = $p_data['data_TAGSBLKED'];
        $depth = $product->pack_depth_in;
        $blocksPaddleTag = $product->blocksPaddleTag;

        $command =   base_path() . // don't use base path... unless running a console command that needs it
            '/bin/' . env('APP_OS', 'centos6') . '/get_ItemCount'
            . ' ' . $depth
            . ' ' . $tags_blocked;

        if ($blocksPaddleTag)
            $command .= ' ' . $p_data['paddle_exposed'];

        $command = "cd ". env('ITEM_COUNT_LOC', '..') ."; ". $command;
//        dd ([$command, shell_exec($command)] );
        return trim(shell_exec($command));
    }


    protected function itemCountToStatus($item_count)
    {
        if ($item_count == 0)
            return "OOS";
        if ($item_count == -1)
            return "RESTOCK";
        return $item_count;
    }


    protected function updatePusher(Pusher &$pusher, Inventory $latest_inv, $header) {
        if ($pusher->low_stock == false && $latest_inv->status == "RESTOCK")
        {
            // trigger low stock event
            Event::fire(new LowStockAlert($latest_inv));
            $pusher->low_stock = true;
            $pusher->low_stock_at = $header['timestamp'];

        }
        elseif ($pusher->oos == false && $latest_inv->oos == true)
        {
            Event::fire(new InventoryOutOfStock($latest_inv));
            $pusher->oos = true;
            $pusher->oos_at = $header['timestamp'];
        }
        elseif ( ( $pusher->oos == true || $pusher->status == "RESTOCK" ) && $latest_inv->oos == false)
        {
            Event::fire(new PusherWasRestocked($latest_inv));
            $pusher->low_stock = false;
            $pusher->oos = false;
            $pusher->low_stock_at = null;
            $pusher->oos_at = null;
        }

        $pusher->item_count     = $latest_inv->item_count;
        $pusher->tags_blocked   = $latest_inv->tags_blocked;
        $pusher->status         = $latest_inv->status;

        $pusher->save();
    }

}