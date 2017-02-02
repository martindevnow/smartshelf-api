<?php

namespace Martindevnow\Smartshelf\Engineering\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Martindevnow\Smartshelf\Engineering\Events\LowStockAlert;
use Martindevnow\Smartshelf\Engineering\Pusher;
use Martindevnow\Smartshelf\Engineering\PusherLowStock;
use Martindevnow\Smartshelf\Product\ProductLowStock;

class LogLowStockEvent
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Handle the event.
     *
     * @param  LowStockAlert  $event
     */
    public function handle(LowStockAlert $event)
    {
        $pusher = $event->pusher;
        if ($pusher->pusherLowStocks()->where('restocked_at', null)->count())
        {
            // there already is a low stock
            // otherwise, make a new one
            return true;
        }
        
        $pusher->pusherLowStocks()->create([
            'location_id'   => $pusher->location_id,
            'product_id'    => $pusher->product_id,
            'low_stock_at'  => $event->time,
            'restocked_at'  => null,
        ]);
        
        $lowStockFacings = PusherLowStock::where('location_id', $pusher->location_id)
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
            $prodLowStock = ProductLowStock::where('location_id', $pusher->location_id)
                ->where('product_id', $pusher->product_id)
                ->get();
            if ($prodLowStock->count())
                return true;
            
            ProductLowStock::create([
                'location_id'   => $pusher->location_id,
                'product_id'    => $pusher->product_id,
                'low_stock_at'  => $event->time,
                'restocked_at'  => null,
            ]);
        }
    }
}
