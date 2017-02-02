<?php

use App\ReaderConfig;
use Carbon\Carbon;
use Martindevnow\Smartshelf\Engineering\Reader;
use Martindevnow\Smartshelf\Retailer\Location;

abstract class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    /**
     *
     *
     * @param null $mac
     * @param null $time
     * @return ReaderConfig
     */
    public function createAStoreAndUploadPOG($facings = 6, $prods = 2, $mac = null, $time = null)
    {
        if ( ! $mac)
            $mac = '00:12:ff:23:12:ee';

        if ( ! $time)
            $time = Carbon::now()->subDays(1)->startOfDay()->addHours(8)->timestamp; // 8AM yesterday

        $location = Location::create(['name' => 'test', 'code'=> 'test', 'template' => 'nss', 'banner_id' => 1]);
        $reader = Reader::create(['mac_address' => $mac, 'location_id' => $location->id, 'ip_address' => '1.1.1.1']);

        $readerC = new ReaderConfig($mac, $time);

        $upc = 5500034;
        for ($i = 1; $i <= $facings; $i++)
        {
            $readerC->addPusherLine('FF00' . $i, 'FS01', $upc, '0' . $i, 6);
            if ($i == floor($facings / $prods))
                $upc ++;
        }

//        $readerC->addPusherLine('FF001', 'FS01', '5500034', '01', 6);
//        $readerC->addPusherLine('FF002', 'FS01', '5500034', '02', 6);
//        $readerC->addPusherLine('FF003', 'FS01', '5500034', '03', 6);
//        $readerC->addPusherLine('FF004', 'FS01', '5500301', '04', 6);
//        $readerC->addPusherLine('FF005', 'FS01', '5500301', '05', 6);
//        $readerC->addPusherLine('FF006', 'FS01', '5500301', '06', 6);

        $this->uploadPlanogram($readerC);
        return $readerC;
    }

    /**
     *
     *
     * @param null $reader_mac
     * @param null $time
     * @return ReaderConfig
     */
    public function createANewStoreInDB($reader_mac = null, $time = null)
    {
        if ( ! $reader_mac)
            $reader_mac = '00:12:ff:23:12:ee';

        if ( ! $time)
            $time = Carbon::now()->subDays(1)->startOfDay()->addHours(8)->timestamp; // 8AM yesterday

        $location = Location::create(['name' => 'test', 'code'=> 'test', 'template' => 'nss', 'banner_id' => 1]);
        $reader = Reader::create(['mac_address' => $reader_mac, 'location_id' => $location->id, 'ip_address' => '1.1.1.1']);

        return new ReaderConfig($reader_mac, $time);
    }

    /**
     * Make a call to the Inventory API
     *
     * @param string $uri
     * @return \Illuminate\Http\Response
     */
    protected function uploadPlanogram(ReaderConfig $readerConfig, $uri = '/api/reader/setup')
    {
        return $this->call('POST', url($uri), $readerConfig->exportReaderPOG(), [], []);
    }

    /**
     * Make a call to the Inventory API
     *
     * @param string $uri
     * @return \Illuminate\Http\Response
     */
    protected function uploadInventory(ReaderConfig $readerConfig, $uri = '/api/inventory/upload')
    {
        return $this->call('POST', url($uri), $readerConfig->exportInventoryData(), [], []);
    }
}
