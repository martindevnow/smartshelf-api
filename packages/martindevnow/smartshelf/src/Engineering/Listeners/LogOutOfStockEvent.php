<?php

namespace Martindevnow\Smartshelf\Engineering\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Martindevnow\Smartshelf\Engineering\Events\InventoryOutOfStock;
use Martindevnow\Smartshelf\Engineering\Pusher;
use Martindevnow\Smartshelf\Engineering\PusherOutOfStock;
use Martindevnow\Smartshelf\Product\ProductOutOfStock;

class LogOutOfStockEvent
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  InventoryOutOfStock $event
     * @return bool
     */
    public function handle(InventoryOutOfStock $event)
    {
        $pusher = $event->pusher;
        if ($pusher->pusherOutOfStocks()->where('restocked_at', null)->count())
        {
            // there already is a low stock
            // otherwise, make a new one
            return true;
        }

        $pusher->pusherOutOfStocks()->create([
            'location_id'   => $pusher->location_id,
            'product_id'    => $pusher->product_id,
            'oos_at'  => $event->time,
            'restocked_at'  => null,
        ]);

        $lowStockFacings = PusherOutOfStock::where('location_id', $pusher->location_id)
            ->where('product_id', $pusher->product_id)
            ->where('restocked_at', null)
            ->count();

        $totFacings = Pusher::where('location_id', $pusher->location_id)
            ->where('product_id', $pusher->product_id)
            ->where('active', true)
            ->count();

        if ($lowStockFacings == $totFacings)
        {
            // whole product is low stock
            $prodOutOfStock = ProductOutOfStock::where('location_id', $pusher->location_id)
                ->where('product_id', $pusher->product_id)
                ->get();
            if ($prodOutOfStock->count())
                return true;

            ProductOutOfStock::create([
                'location_id'   => $pusher->location_id,
                'product_id'    => $pusher->product_id,
                'oos_at'  => $event->time,
                'restocked_at'  => null,
            ]);
        }
    }
}
