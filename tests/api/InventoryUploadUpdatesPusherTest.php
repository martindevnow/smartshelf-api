<?php

use App\ReaderConfig;
use Martindevnow\Smartshelf\Engineering\Inventory;
use Martindevnow\Smartshelf\Engineering\Pusher;

class InventoryUploadUpdatesPusherTest extends TestCase {

    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /** @test */
    public function it_stores_item_count_correctly() {
        /** @var ReaderConfig $readerC */
        $readerC = $this->createAStoreAndUploadPOG(3,1);
        $readerC->addInventoryData('FF001', 2, 0);
        $readerC->addInventoryData('FF002', 2, 0);
        $readerC->addInventoryData('FF003', 2, 0);
        $this->uploadInventory($readerC);

        $pushers = Pusher::all();
        foreach ($pushers as $pusher) {
            $this->assertEquals($pusher->item_count, 5.5);
        }
    }

    /** @test */
    public function it_calculates_product_item_count_correctly_for_multiple_products() {
        /** @var ReaderConfig $readerC */
        $readerC = $this->createAStoreAndUploadPOG(6,2);
        $readerC->addInventoryData('FF001', 2, 0);
        $readerC->addInventoryData('FF002', 2, 0);
        $readerC->addInventoryData('FF003', 2, 0);
        $readerC->addInventoryData('FF004', 2, 0);
        $readerC->addInventoryData('FF005', 2, 0);
        $readerC->addInventoryData('FF006', 2, 0);
        $this->uploadInventory($readerC);

        $pushers = Pusher::all();
        foreach ($pushers as $pusher) {
            $this->assertEquals($pusher->product_item_count, $pusher->item_count * 3);
        }
    }

    /** @test */
    public function it_logs_prev_item_count_correctly() {
        /** @var ReaderConfig $readerC */
        $readerC = $this->createAStoreAndUploadPOG(3,1);
        $readerC->addInventoryData('FF001', 2, 0);
        $readerC->addInventoryData('FF002', 2, 0);
        $readerC->addInventoryData('FF003', 2, 0);
        $this->uploadInventory($readerC);

        $readerC->time->addHour();
        $readerC->addInventoryData('FF001', 1, 0);
        $readerC->addInventoryData('FF002', 1, 0);
        $readerC->addInventoryData('FF003', 1, 0);
        $this->uploadInventory($readerC);

        $pushers = Pusher::all();
        foreach ($pushers as $pusher) {
            if ($pusher->tags_blocked == 2)
                $this->assertEquals(0, $pusher->prev_item_count);
            else
                $this->assertEquals(5.5, $pusher->prev_item_count);
        }
    }

    /** @test */
    public function it_calculates_pusher_ooses_correctly_when_all_or_none_are_oos() {
        /** @var ReaderConfig $readerC */
        $readerC = $this->createAStoreAndUploadPOG(3,1);
        $readerC->addInventoryData('FF001', 2, 0);
        $readerC->addInventoryData('FF002', 2, 0);
        $readerC->addInventoryData('FF003', 2, 0);
        $this->uploadInventory($readerC);

        $readerC->time->addHour();

        $readerC->addInventoryData('FF001', 0, 1);
        $readerC->addInventoryData('FF002', 0, 1);
        $readerC->addInventoryData('FF003', 0, 1);
        $this->uploadInventory($readerC);

        $pushers = Pusher::all();
        foreach ($pushers as $pusher) {
            if ($pusher->tags_blocked == 2)
                $this->assertEquals(0, $pusher->pusher_ooses);
            else
                $this->assertEquals(3, $pusher->pusher_ooses);
        }
    }

    /** @test */
    public function it_calculates_pusher_ooses_correctly_when_only_some_are_oos() {
        /** @var ReaderConfig $readerC */
        $readerC = $this->createAStoreAndUploadPOG(3,1);
        $readerC->addInventoryData('FF001', 0, 1);
        $readerC->addInventoryData('FF002', 2, 0);
        $readerC->addInventoryData('FF003', 2, 0);
        $this->uploadInventory($readerC);

        $readerC->time->addHour();
        $oos_time = clone $readerC->time;

        $readerC->addInventoryData('FF001', 1, 0);
        $readerC->addInventoryData('FF002', 0, 1);
        $readerC->addInventoryData('FF003', 0, 1);
        $this->uploadInventory($readerC);

        $pushers = Pusher::all();
        foreach ($pushers as $pusher) {
            if ($pusher->created_at->format('H:i:s') != $oos_time->format('H:i:s'))
                $this->assertEquals(1, $pusher->pusher_ooses);
            else
                $this->assertEquals(2, $pusher->pusher_ooses);
        }
    }

    /** @test */
    public function it_calculates_prev_oos_at_correctly_when_restocked() {
        /** @var ReaderConfig $readerC */
        $readerC = $this->createAStoreAndUploadPOG(3,1);
        $readerC->addInventoryData('FF001', 0, 1);
        $readerC->addInventoryData('FF002', 2, 0);
        $readerC->addInventoryData('FF003', 2, 0);
        $this->uploadInventory($readerC);

        $oos_at = clone $readerC->time;
        $readerC->time->addHour();

        $readerC->addInventoryData('FF001', 2, 0);
        $readerC->addInventoryData('FF002', 0, 1);
        $readerC->addInventoryData('FF003', 0, 1);
        $this->uploadInventory($readerC);

        $pushers = Pusher::all();
        foreach ($pushers as $pusher) {
            if ($pusher->created_at->format('H:i:s') == $oos_at->format('H:i:s'))
                $this->assertEquals(null, $pusher->prev_oos_at);
            else
                if ($pusher->tags_blocked == 2)
                    $this->assertEquals($oos_at->format('H:i:s'), $pusher->prev_oos_at->format('H:i:s'));
        }
    }
}