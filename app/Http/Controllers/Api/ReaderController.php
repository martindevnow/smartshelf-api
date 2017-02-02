<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Martindevnow\Smartshelf\Engineering\Repositories\ReaderRepository;

class ReaderController extends ApiController
{
    public function setup(Request $request, ReaderRepository $readerRepository) {

        $payload = $request->all();
        $header = $this->getHeader($payload);

        return $readerRepository->setupPushersFromReader($payload, $header);
    }
}
