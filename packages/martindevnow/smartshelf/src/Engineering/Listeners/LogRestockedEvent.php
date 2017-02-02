<?php

namespace Martindevnow\Smartshelf\Engineering\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Martindevnow\Smartshelf\Engineering\Events\PusherWasRestocked;
use Martindevnow\Smartshelf\Product\ProductLowStock;
use Martindevnow\Smartshelf\Product\ProductOutOfStock;

class LogRestockedEvent
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
     * @param  PusherWasRestocked  $event
     * @return void
     */
    public function handle(PusherWasRestocked $event)
    {
        $pusher = $event->pusher;

        // LOW stocks
        $lowStocks = $pusher->pusherLowStocks()->where('restocked_at');
        if ($lowStocks->count() == 0)
        {
            // none in the table
        }
        else {
            $lowStocks = $lowStocks->update([
                'restocked_at' => $event->time,
            ]);

            $prodLowStocks = ProductLowStock::where('location_id', $pusher->location_id)
                ->where('product_id', $pusher->product_id)
                ->where('restocked_at', null);

            if ($prodLowStocks->count() == 0)
            {
                // do nothing
            }
            else
            {
                $prodLowStocks = $prodLowStocks->update([
                    'restocked_at'  => $event->time,
                ]);
            }
        }


        // OUT of stocks
        $ooses = $pusher->pusherOutOfStocks()->where('restocked_at');
        if ($ooses->count() == 0)
        {
            // none in the table
        }
        else {
            $ooses = $ooses->update([
                'restocked_at' => $event->time,
            ]);

            $prodOutOfStocks = ProductOutOfStock::where('location_id', $pusher->location_id)
                ->where('product_id', $pusher->product_id)
                ->where('restocked_at', null);

            if ($prodOutOfStocks->count() == 0)
            {
                // do nothing
            }
            else
            {
                $prodOutOfStocks = $prodOutOfStocks->update([
                    'restocked_at'  => $event->time,
                ]);
            }
        }


    }
}
