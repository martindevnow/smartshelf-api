<?php

namespace Martindevnow\Smartshelf\Engineering;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Martindevnow\Smartshelf\Product\Product;
use Martindevnow\Smartshelf\Product\ProductOutOfStock;

class Reader extends Model
{
    use SoftDeletes;

    /**
     * Fields which may be mass-assigned
     *
     * @var array
     */
    protected $fillable = [
        'mac_address',
        'ip_address',
        'location_id',
    ];

    /**
     * Fetch a Reader by the mac_address
     * @param $mac_address
     * @return mixed
     */
    public static function findByMacAddress($mac_address) {
        return Reader::whereMacAddress($mac_address)->firstOrFail();
    }





















    //
    //
    //
    //
    //
    //      this is the old colde vvvvvvvvvv
    //
    //
    //
    //

    public function setupPushersFromReader($payload)
    {
        $pushers = $this->pushers();
        $pushers->update(['active' => 'false']);
        $pushers = $pushers->get();
        
        if ($pushers->count() == null)
        {
            // initial setup
            foreach ($payload as $p_data)
            {
                $product = Product::byUpc($p_data['upc']);
                $p[] = $this->pushers()->create([
                    'location_id'   => $this->location_id,
                    'product_id'    => $product->id,

                    'tray_tag'      => $p_data['tray_tag'],
                    'shelf_number'  => $p_data['shelf_no'],
                    'location_number'   => $p_data['location_no'],
                    'total_tags'        => $p_data['tottag'],
                ]);
            }
        }
        else
        {
            // TODO: Build the modification of POG here
            // enable modification of POG here!!
        }
    }

    public function uploadInventoryDataFromReader($payload, $header) {
        $pushers = $this->pushers()->with('product')->get();
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
                $p_data['data'] = $this->calculateItemCount($p_data, $pusher->product);
            }
        }
        else
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
                $new_invs[] = $pusher->inventories()->create([
                    'location_id'   => $pusher->location_id,
                    'reader_id'     => $pusher->reader_id,
                    'product_id'    => $pusher->product_id,
                    'tags_blocked'  => $p_data['data_TAGSBLKED'],
                    'paddle_exposed'  => $p_data['paddle_exposed'],
                    'item_count'    => $p_data['data'],
                    'status'        => $this->itemCountToStatus($p_data['data']),
                    'oos'           => ($p_data['data'] == 0),
                    'created_at'    => Carbon::createFromTimestamp($header['timestamp']),
                ]);

                $this->updatePusher($pusher, end($new_invs), $header);
            }
        }
    }

    public function updatePusher(Pusher &$pusher, Inventory $latest_inv, $header) {
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

    public function itemCountToStatus($item_count)
    {
        if ($item_count == 0)
            return "OOS";
        if ($item_count == -1)
            return "RESTOCK";
        return $item_count;
    }
    public function calculateItemCount($p_data, $product)
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
        //dd ([$command, shell_exec($command)] );
        return trim(shell_exec($command));
    }


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



    /**
     * A reader can deactivate all Pushers
     */
    public function deactivateAllPushers()
    {
        $this->pushers()->update(['active' => 0]);
    }

    /**
     * Return the Active pushers by Shelf and Location
     *
     * @return mixed
     */
    public function getPushersByShelfAttribute()
    {
        return $this->activePushers()
            ->orderBy('shelf_number', 'ASC')
            ->orderBy('location_number', 'ASC')
            ->get();
    }

    /**
     * Return an Array of the shelves on this Reader
     *
     * @return mixed
     */
    public function getShelvesArray()
    {
        return $this->pushers()
            ->groupBy('shelf_number')
            ->lists('shelf_number');
    }

    /**
     * A Reader reports on location's status
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productOutOfStocks()
    {
        return $this->location->productOutOfStocks();
    }

    /**
     * A Reader belongsTo a Location
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location()
    {
        return $this->belongsTo(\Martindevnow\Smartshelf\Retailer\Location::class);
    }

    /**
     * A Reader can hasMany Pushers
     * (This is the default, to only retrieve the active pushers)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pushers()
    {
        return $this->allPushers()->where('active', 1);
    }

    /**
     * A Reader can hasMany Pushers
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function allPushers()
    {
        return $this->hasMany(\Martindevnow\Smartshelf\Engineering\Pusher::class, 'reader_id', 'id');
    }

    /**
     * A Reader can hasMany Pushers
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activePushers()
    {
        return $this->pushers();
    }
    
    public function snoozes() {
        return $this->hasMany(Snooze::class);
    }

    public function isSnoozed(){
        return !! $this->snoozes()->where('end_date', '>=', Carbon::now()->toDateTimeString())->count();
    }
}
