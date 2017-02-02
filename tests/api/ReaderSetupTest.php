<?php

use App\ReaderConfig;
use Martindevnow\Smartshelf\Engineering\Pusher;
use Martindevnow\Smartshelf\Engineering\Reader;
use Martindevnow\Smartshelf\Retailer\Location;

class ReaderSetupTest extends TestCase {

    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /** @test */
    public function it_can_send_a_post_request_to_reader_setup()
    {
        $mac = '00:12:ff:23:12:ee';
        $location = Location::create(['name' => 'test', 'code'=> 'test', 'template' => 'nss', 'banner_id' => 1]);
        $reader = Reader::create(['mac_address' => $mac, 'location_id' => $location->id, 'ip_address' => '1.1.1.1']);
        $readerC = new ReaderConfig($mac, time());
        $readerC->addPusherLine('FF001', 'FS01', '5500034', '01', 6);
        $readerC->addPusherLine('FF002', 'FS01', '5500034', '02', 6);
        $readerC->addPusherLine('FF003', 'FS01', '5500034', '03', 6);
        $readerC->addPusherLine('FF004', 'FS01', '5500301', '04', 6);
        $readerC->addPusherLine('FF005', 'FS01', '5500301', '05', 6);
        $readerC->addPusherLine('FF006', 'FS01', '5500301', '06', 6);
        $this->uploadPlanogram($readerC);

        $pusher = Pusher::all();
        $this->assertCount(6, $pusher);
    }

    /** @test */
    public function quiet()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function the_reader_api_can_receive_a_post_request()
    {
        $readerC = $this->createAStoreAndUploadPOG();
        $mac = $readerC->reader_mac;

        $this->assertResponseOk();
    }

    /** @test */
    public function a_reader_can_have_a_planogram()
    {
        $readerC = $this->createAStoreAndUploadPOG();

        $reader = Reader::findByMacAddress($readerC->reader_mac);
        $this->assertCount(6, $reader->pushers);
    }

    /** @test */
    public function a_reader_can_replace_the_pog_with_a_new_pog()
    {
        // Upload default POG
        $readerC = $this->createAStoreAndUploadPOG();
        $reader = Reader::findByMacAddress($readerC->reader_mac);

        $i = 1;
        foreach ($reader->pushers as $pusher)
        {
            $this->assertEquals(1, $pusher->active);
            if ($i <= 3)
                $this->assertEquals(5500034, $pusher->product->upc);
            else
                $this->assertEquals(5500035, $pusher->product->upc);
            $i++;
        }
        $this->assertCount(6, $reader->pushers);

        $readerC->pushers = [];
        $readerC->addPusherLine('FF011', 'FS01', '5600034', '01', 6);
        $readerC->addPusherLine('FF012', 'FS01', '5600034', '02', 6);
        $readerC->addPusherLine('FF013', 'FS01', '5600034', '03', 6);
        $readerC->addPusherLine('FF014', 'FS01', '5600301', '04', 6);
        $readerC->addPusherLine('FF015', 'FS01', '5600301', '05', 6);
        $readerC->addPusherLine('FF016', 'FS01', '5600301', '06', 6);

        $this->uploadPlanogram($readerC);

        $reader = $reader->fresh(['pushers', 'pushers.product']);
        $pushers = $reader->pushers;

        $i = 1;
        foreach ($pushers as $pusher) {
            $this->assertEquals(1, $pusher->active);
            if ($i <= 3)
                $this->assertEquals(5600034, $pusher->product->upc);
            else
                $this->assertEquals(5600301, $pusher->product->upc);
            $i++;
        }
        $this->assertCount(6, $pushers);
        $this->assertCount(12, $reader->allPushers);
    }
}