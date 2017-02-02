<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Martindevnow\Smartshelf\Engineering\Repositories\InventoryRepository;
use Martindevnow\Smartshelf\Engineering\Repositories\ReaderRepository;

class InventoryController extends ApiController
{
    public function upload(Request $request, InventoryRepository $inventoryRepository)
    {
        $payload = $request->all();
        $header = $this->getHeader($payload);

        $inventoryRepository->uploadInventoryDataForReader($payload, $header);
    }
}
