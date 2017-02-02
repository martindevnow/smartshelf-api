<?php

namespace Martindevnow\Smartshelf\Engineering\Repositories;

use Martindevnow\Smartshelf\Engineering\Reader;
use Martindevnow\Smartshelf\Product\Product;

class ReaderRepository {

    public function setupPushersFromReader($payload, $header) {
        $reader = Reader::with('pushers')
            ->where('mac_address', $header['reader_mac']);

        if ($reader->count() != 1)
            return response('This reader does not exist in the DB', 409);

        $reader = $reader->first();

        $pushers_builder = $reader->pushers();
        if ($pushers_builder->count() != null && $pushers_builder->count() != 0) {
            $pushers_builder->update(['active' => false]);
        }

        // setting up a brand new reader for the first time
        foreach ($payload as $p_data) {
            $product = Product::findOrCreateByUpc($p_data['upc']);
            $p[] = $reader->pushers()->create([
                'location_id'   => $reader->location_id,
                'product_id'    => $product->id,

                'tray_tag'      => $p_data['tray_tag'],
                'shelf_number'  => $p_data['shelf_no'],
                'location_number'   => $p_data['location_no'],
                'total_tags'        => $p_data['tottag'],
            ]);
        }
        return response('Reader was setup.', 200);

    }
}