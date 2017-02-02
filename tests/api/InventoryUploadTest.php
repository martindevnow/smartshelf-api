<?php

use Carbon\Carbon;
use Martindevnow\Smartshelf\Engineering\Inventory;
use Martindevnow\Smartshelf\Engineering\Pusher;
use App\ReaderConfig;
use Martindevnow\Smartshelf\Engineering\Reader;
use Martindevnow\Smartshelf\Engineering\Repositories\InventoryRepository;
use Martindevnow\Smartshelf\Engineering\Repositories\ReaderRepository;
use Martindevnow\Smartshelf\Product\Product;
use Martindevnow\Smartshelf\Retailer\Location;

class InventoryUploadTest extends TestCase {

    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /** NOT PASSING */
    // TODO: work on this one
    public function it_fetches_the_latest_inv_for_each_pusher_id () {
        /** @var ReaderConfig $readerC */
        $readerC = $this->createAStoreAndUploadPOG(3,1);
        $readerC->addInventoryData('FF001', 2, 0);
        $readerC->addInventoryData('FF002', 2, 0);
        $readerC->addInventoryData('FF003', 2, 0);
        $this->uploadInventory($readerC);

        $readerC->time->addMinutes(10);
        $readerC->addInventoryData('FF001', 1, 0);
        $readerC->addInventoryData('FF002', 2, 0);
        $readerC->addInventoryData('FF003', 2, 0);
        $this->uploadInventory($readerC);

        $pusher_ids = $readerC->getPushers()->pluck('id')->toArray();

        $invRepo = new InventoryRepository();
        /** @var Reader $reader */
        $inv = $invRepo->fetchLatestInventoryForPushers($pusher_ids);
        // this returns an empty collection.....
        dd ($inv);
    }

    /** @test */
    public function it_can_send_a_post_request_to_upload_inventory_data()
    {
        /** @var ReaderConfig $readerC */
        $readerC = $this->createAStoreAndUploadPOG(3,1);
        $readerC->addInventoryData('FF001', 2, 0);
        $readerC->addInventoryData('FF002', 2, 0);
        $readerC->addInventoryData('FF003', 2, 0);
        $this->uploadInventory($readerC);

        $inventory = Inventory::all();
        $this->assertCount(3, $inventory);
    }

    /** @test */
    public function it_can_update_inventory()
    {
        /** @var ReaderConfig $readerConfig */
        $readerConfig = $this->createAStoreAndUploadPOG();

        $readerConfig->addInventoryData('FF001', 3, 0);
        $readerConfig->addInventoryData('FF002', 3, 0);
        $readerConfig->addInventoryData('FF003', 3, 0);
        $readerConfig->addInventoryData('FF004', 3, 0);
        $readerConfig->addInventoryData('FF005', 3, 0);
        $readerConfig->addInventoryData('FF006', 3, 0);
        $readerConfig->time->addHours(1);
        $result = $this->uploadInventory($readerConfig);


        $readerConfig->addInventoryData('FF001', 2, 0);
        $readerConfig->addInventoryData('FF002', 2, 0);
        $readerConfig->addInventoryData('FF003', 2, 0);
        $readerConfig->addInventoryData('FF004', 2, 0);
        $readerConfig->addInventoryData('FF005', 2, 0);
        $readerConfig->addInventoryData('FF006', 2, 0);
        $readerConfig->time->addHours(1);
        $result = $this->uploadInventory($readerConfig);

        $inventory = Inventory::all();
        $this->assertCount(12,$inventory);
    }

    /** @test */
    public function it_does_not_create_duplicate_inventory()
    {
        /** @var ReaderConfig $readerConfig */
        $readerConfig = $this->createAStoreAndUploadPOG();

        $readerConfig->addInventoryData('FF001', 3, 0);
        $readerConfig->addInventoryData('FF002', 3, 0);
        $readerConfig->addInventoryData('FF003', 3, 0);
        $readerConfig->addInventoryData('FF004', 3, 0);
        $readerConfig->addInventoryData('FF005', 3, 0);
        $readerConfig->addInventoryData('FF006', 3, 0);
        $readerConfig->time->addHours(1);
        $result = $this->uploadInventory($readerConfig);


//        $readerConfig->addInventoryData('FF001', 2, 0);
//        $readerConfig->addInventoryData('FF002', 2, 0);
//        $readerConfig->addInventoryData('FF003', 2, 0);
        $readerConfig->addInventoryData('FF004', 2, 0);
        $readerConfig->addInventoryData('FF005', 2, 0);
        $readerConfig->addInventoryData('FF006', 2, 0);
        $readerConfig->time->addHours(1);
        $result = $this->uploadInventory($readerConfig);

        $inventory = Inventory::all();
        $this->assertCount(9,$inventory);
    }

    /** @test */
    public function it_DOES_create_duplicate_inventory_that_is_different_from_last_transmission()
    {
        /** @var ReaderConfig $readerConfig */
        $readerConfig = $this->createAStoreAndUploadPOG();

        $readerConfig->addInventoryData('FF001', 3, 0);
        $readerConfig->addInventoryData('FF002', 3, 0);
        $readerConfig->addInventoryData('FF003', 3, 0);
        $readerConfig->addInventoryData('FF004', 3, 0);
        $readerConfig->addInventoryData('FF005', 3, 0);
        $readerConfig->addInventoryData('FF006', 3, 0);
        $readerConfig->time->addHours(1);
        $result = $this->uploadInventory($readerConfig);


//        $readerConfig->addInventoryData('FF001', 2, 0);
//        $readerConfig->addInventoryData('FF002', 2, 0);
//        $readerConfig->addInventoryData('FF003', 2, 0);
        $readerConfig->addInventoryData('FF004', 2, 0);
        $readerConfig->addInventoryData('FF005', 2, 0);
        $readerConfig->addInventoryData('FF006', 2, 0);
        $readerConfig->time->addHours(1);
        $result = $this->uploadInventory($readerConfig);


        $readerConfig->addInventoryData('FF001', 2, 0);
        $readerConfig->addInventoryData('FF002', 2, 0);
        $readerConfig->addInventoryData('FF003', 2, 0);
        $readerConfig->addInventoryData('FF004', 3, 0);
        $readerConfig->addInventoryData('FF005', 3, 0);
        $readerConfig->addInventoryData('FF006', 3, 0);
        $readerConfig->time->addHours(1);
        $result = $this->uploadInventory($readerConfig);

        $inventory = Inventory::all();
        $this->assertCount(15,$inventory);
    }

    /** @test */
    public function it_updates_the_pusher_to_oos_appropriately()
    {
        /** @var ReaderConfig $readerConfig */
        $readerConfig = $this->createAStoreAndUploadPOG();

        $readerConfig->addInventoryData('FF005', 3, 0);
        $readerConfig->time->addHours(1);
        $result = $this->uploadInventory($readerConfig);

        $readerConfig->addInventoryData('FF005', 0, 1);
        $readerConfig->time->addHours(1);
        $result = $this->uploadInventory($readerConfig);

        $pushers = Pusher::all();

        $pusher = $pushers->where('tray_tag', 'FF005')->get(4);
        $pusher_fresh = $pusher->fresh(['inventories']);

        $this->assertEquals(0, $pusher_fresh->tags_blocked);
        $this->assertEquals(1, $pusher_fresh->oos);

        $inventory = Inventory::all();
        $this->assertCount(2,$inventory);
    }

    /** @test */
    public function a_pusher_inventory_upload_for_second_time_can_find_latest_inventory()
    {
        $readerConfig = $this->createAStoreAndUploadPOG(1, 1);

        $readerConfig->addInventoryData('FF001', 3, 0);
        $readerConfig->time->addHours(1);
        $result = $this->uploadInventory($readerConfig);

        $pusher = $readerConfig->getPushers()->first();

        $latestInventory = $pusher->latestInventory;

        $this->assertTrue($latestInventory instanceof \Martindevnow\Smartshelf\Engineering\Inventory);
        $this->assertTrue($latestInventory->tags_blocked == 3);
        $this->assertTrue($latestInventory->paddle_exposed == 0);

        $readerConfig->addInventoryData('FF001', 1, 0);
        $readerConfig->time->addHours(1);
        $result = $this->uploadInventory($readerConfig);

        $pusher = $pusher->fresh();
        $this->assertCount(2, $pusher->inventories);
    }

    /** @test */
    public function a_pusher_with_no_inventory_returns_null_latestInventory()
    {
        $readerConfig = $this->createAStoreAndUploadPOG(1, 1);
        $pusher = $readerConfig->getPushers()->first();

        $latestInventory = $pusher->latestInventory;

        $this->assertEquals(null, $latestInventory);
    }


    /** @test */
    public function it_updates_the_pusher_to_oos_and_low_stock_appropriately()
    {
        /** @var ReaderConfig $readerConfig */
        $readerConfig = $this->createAStoreAndUploadPOG();

        $readerConfig->addInventoryData('FF001', 3, 0);
        $readerConfig->addInventoryData('FF002', 3, 0);
        $readerConfig->addInventoryData('FF003', 3, 0);
        $readerConfig->addInventoryData('FF004', 3, 0);
        $readerConfig->addInventoryData('FF005', 3, 0);
        $readerConfig->addInventoryData('FF006', 3, 0);
        $readerConfig->time->addHours(1);
        $result = $this->uploadInventory($readerConfig);

        $push = Pusher::all();
        $inv = Inventory::all();

        $readerConfig->addInventoryData('FF001', 3, 0); // No change
        $readerConfig->addInventoryData('FF002', 2, 0);
        $readerConfig->addInventoryData('FF003', 1, 0);
        $readerConfig->addInventoryData('FF004', 0, 0);
        $readerConfig->addInventoryData('FF005', 0, 1);
        $readerConfig->addInventoryData('FF006', 3, 0); // No Change
        $readerConfig->time->addHours(1);
        $result = $this->uploadInventory($readerConfig);

        $pushers = Pusher::all();
        $pusher = $pushers->where('tray_tag', 'FF001')->get(0);
        $this->assertEquals(3, $pusher->tags_blocked);



        $pusher = $pushers->where('tray_tag', 'FF002')->get(1);
        $pusher_fresh = $pusher->fresh(['inventories']);

        $this->assertEquals(2, $pusher_fresh->tags_blocked);
        $this->assertEquals(0, $pusher_fresh->oos);
        $this->assertEquals(0, $pusher_fresh->low_stock);



        $pusher = $pushers->where('tray_tag', 'FF003')->get(2);
        $pusher_fresh = $pusher->fresh(['inventories']);

        $this->assertEquals(1, $pusher_fresh->tags_blocked);
        $this->assertEquals(0, $pusher_fresh->oos);
        $this->assertEquals(0, $pusher_fresh->low_stock);



        $pusher = $pushers->where('tray_tag', 'FF004')->get(3);
        $pusher_fresh = $pusher->fresh(['inventories']);

        $this->assertEquals(0, $pusher_fresh->tags_blocked);
        $this->assertEquals(0, $pusher_fresh->oos);
        $this->assertEquals(1, $pusher_fresh->low_stock);



        $pusher = $pushers->where('tray_tag', 'FF005')->get(4);
        $pusher_fresh = $pusher->fresh(['inventories']);

        $this->assertEquals(0, $pusher_fresh->tags_blocked);
        $this->assertEquals(1, $pusher_fresh->oos);

        $pusher = $pushers->where('tray_tag', 'FF006')->first();
        $pusher_fresh = $pusher->fresh(['inventories']);

        $inv = Inventory::all();

        $this->assertEquals(3, $pusher_fresh->tags_blocked);
        $this->assertEquals(0, $pusher_fresh->oos);
        $this->assertEquals(0, $pusher_fresh->low_stock);

        $inventory = Inventory::all();
        $this->assertCount(10,$inventory);
    }

    /** @test */
    public function it_creates_the_out_of_stock_entries_properly()
    {
        $readerConfig = $this->createAStoreAndUploadPOG();

        $readerConfig->addInventoryData('FF001', 3, 0);
        $readerConfig->addInventoryData('FF002', 3, 0);
        $readerConfig->time->addHours(1);
        $result = $this->uploadInventory($readerConfig);

        $readerConfig->addInventoryData('FF001', 0, 1);
        $readerConfig->addInventoryData('FF002', 3, 0);
        $readerConfig->time->addHours(1);
        $result = $this->uploadInventory($readerConfig);

        $pusher = Pusher::where('tray_tag', 'FF001')->first();
        $this->assertCount(1, $pusher->pusherOutOfStocks);
    }

    /** @test */
    public function it_restocks_the_out_of_stock_entries_properly()
    {
        $readerConfig = $this->createAStoreAndUploadPOG();
        $time = Carbon::now()->subDays(1)->startOfDay()->addHours(8); // 8AM yesterday

                                                            // CURRENT_TIME
        $readerConfig->addInventoryData('FF001', 3, 0);
        $readerConfig->addInventoryData('FF002', 3, 0);
        $readerConfig->time->addHours(1);                   // 9 am
        $result = $this->uploadInventory($readerConfig);

        $readerConfig->addInventoryData('FF001', 0, 1);
        $readerConfig->addInventoryData('FF002', 3, 0);
        $readerConfig->time->addHours(1);                   // 10am
        $result = $this->uploadInventory($readerConfig);

        $readerConfig->addInventoryData('FF001', 2, 0);
        $readerConfig->addInventoryData('FF002', 3, 0);
        $readerConfig->time->addHours(1);                   // 11 am
        $result = $this->uploadInventory($readerConfig);

        $pusher = Pusher::where('tray_tag', 'FF001')->first();
        $this->assertCount(1, $pusher->pusherOutOfStocks);


        $oos = $pusher->pusherOutOfStocks->first();                     // EXPECTED_TIME
        $this->assertEquals($time->addHours(2), $oos->oos_at);          // 10am
        $this->assertEquals($time->addHours(1), $oos->restocked_at);    // 11 am
    }


    /** @test */
    public function it_creates_the_low_stock_entries_properly()
    {
        $readerConfig = $this->createAStoreAndUploadPOG();

        $readerConfig->addInventoryData('FF001', 3, 0);
        $readerConfig->addInventoryData('FF002', 3, 0);
        $readerConfig->time->addHours(1);
        $result = $this->uploadInventory($readerConfig);

        $readerConfig->addInventoryData('FF001', 0, 0);
        $readerConfig->addInventoryData('FF002', 3, 0);
        $readerConfig->time->addHours(1);
        $result = $this->uploadInventory($readerConfig);


        $pusher = Pusher::where('tray_tag', 'FF001')->first();
        $this->assertCount(1, $pusher->pusherLowStocks);
    }

    /** @test */
    public function it_restocks_the_low_stock_entries_properly()
    {
        $readerConfig = $this->createAStoreAndUploadPOG();
        $time = Carbon::now()->subDays(1)->startOfDay()->addHours(8); // 8AM yesterday

        $readerConfig->addInventoryData('FF001', 3, 0);
        $readerConfig->addInventoryData('FF002', 3, 0);
        $readerConfig->time->addHours(1);
        $result = $this->uploadInventory($readerConfig);    // 9 am

        $readerConfig->addInventoryData('FF001', 0, 0);
        $readerConfig->addInventoryData('FF002', 3, 0);
        $readerConfig->time->addHours(1);
        $result = $this->uploadInventory($readerConfig);    // 10 AM

        $readerConfig->addInventoryData('FF001', 2, 0);
        $readerConfig->addInventoryData('FF002', 3, 0);
        $readerConfig->time->addHours(1);
        $result = $this->uploadInventory($readerConfig);    // 11 AM


        $pusher = Pusher::where('tray_tag', 'FF001')->first();
        $this->assertCount(1, $pusher->pusherLowStocks);



        $low = $pusher->pusherLowStocks->first();                       // EXPECTED_TIME
        $this->assertEquals($time->addHours(2), $low->low_stock_at);    // 10 am
        $this->assertEquals($time->addHours(1), $low->restocked_at);    // 11 am
    }


    /** @test */
    public function inventory_can_go_oos()
    {
        $readerConfig = $this->createAStoreAndUploadPOG(1, 1);

        $readerConfig->addInventoryData('FF001', 2, 0);
        $readerConfig->time->addHours(1);
        $result = $this->uploadInventory($readerConfig);    // 9 am

        $reader = $readerConfig->getReader();
        $pusher = $readerConfig->getPushers()->first();
        $location = $reader->location;

        $this->assertCount(1, $pusher->inventories);

        /** @var Pusher $pusher */
        $pusher = $pusher->fresh(['product']);
        $inventory = $pusher->latestInventory;

        $this->assertEquals(0, $pusher->oos);
        $this->assertEquals(0, $inventory->oos);
        $this->assertNotEquals(0, $pusher->status);
        $this->assertNotEquals(0, $inventory->status);
        $this->assertNotEquals(0, $pusher->item_count);
        $this->assertNotEquals(0, $inventory->item_count);

        $this->assertCount(0, $pusher->pusherOutOfStocks);
        $this->assertCount(0, $pusher->product->productOutOfStocks);


        $readerConfig->addInventoryData('FF001', 0, 1);
        $readerConfig->time->addHours(1);
        $result = $this->uploadInventory($readerConfig);    // 9 am


        $pusher = $pusher->fresh(['product']);
        $this->assertCount(2, $pusher->inventories);

        $inventory = $pusher->latestInventory;

//        dd ($pusher->toArray());

        $this->assertEquals(1, $pusher->oos);
        $this->assertEquals(1, $inventory->oos);
        $this->assertEquals("OOS", $pusher->status);
        $this->assertEquals("OOS", $inventory->status);
        $this->assertEquals(0, $pusher->item_count);
        $this->assertEquals(0, $inventory->item_count);

        $this->assertCount(1, $pusher->pusherOutOfStocks);
        $this->assertCount(1, $pusher->product->productOutOfStocks);


        // And it can go back instock too
        $readerConfig->addInventoryData('FF001', 1, 0);
        $readerConfig->time->addHours(1);
        $result = $this->uploadInventory($readerConfig);    // 9 am

        /**
         * This part has changed... it now takes 1 tag being blocked to initiate restock.. etc
         *
         */
        $pusher = $pusher->fresh(['pusherOutOfStocks']);
        $inventory = $pusher->latestInventory;
        $latestOOS = $pusher->getLatestProductOutOfStock($location, false);

        $this->assertCount(3, $pusher->inventories);
        $this->assertEquals(0, $pusher->oos);
        $this->assertEquals(0, $inventory->oos);
        $this->assertNotEquals(0, $pusher->status);
        $this->assertNotEquals(0, $inventory->status);
        $this->assertNotEquals(0, $pusher->item_count);
        $this->assertNotEquals(0, $inventory->item_count);
        $this->assertCount(1, $pusher->pusherOutOfStocks);

        $this->assertNotNull($latestOOS->restocked_at);
        $this->assertCount(1, $pusher->product->productOutOfStocks);

        /** @var Product $product */
        $product = $pusher->product;
        $this->assertNotNull($pusher->getLatestProductOutOfStock($location, false)->restocked_at);


        $readerConfig->addInventoryData('FF001', 2, 0);
        $readerConfig->time->addHours(1);
        $result = $this->uploadInventory($readerConfig);    // 9 am

        $pusher = $pusher->fresh();
        $this->assertCount(4, $pusher->inventories);

        $inventory = $pusher->latestInventory;

        $this->assertEquals(0, $pusher->oos);
        $this->assertEquals(0, $inventory->oos);
        $this->assertNotEquals(0, $pusher->status);
        $this->assertNotEquals(0, $inventory->status);
        $this->assertNotEquals(0, $pusher->item_count);
        $this->assertNotEquals(0, $inventory->item_count);

        $this->assertCount(1, $pusher->pusherOutOfStocks);
        $this->assertNotNull($pusher->getLatestProductOutOfStock($location, false)->restocked_at);
        $this->assertCount(1, $pusher->product->productOutOfStocks);
        $this->assertNotNull($pusher->getLatestProductOutOfStock($location, false)->restocked_at);
    }


    /** @test */
    public function the_inventory_api_can_receive_a_post_request()
    {
        /** @var ReaderConfig $readerConfig */
        $readerConfig = $this->createAStoreAndUploadPOG(2, 1);

        $readerConfig->addInventoryData('FF001', 3, 0);
        $readerConfig->addInventoryData('FF002', 3, 0);
        $readerConfig->time->addHours(1);

        $result = $this->uploadInventory($readerConfig);

        $pushers = $readerConfig->getPushers();

        $this->assertCount(1, $pushers->first()->inventories);
        $this->assertCount(2, $pushers);
    }

    /** @test */
    public function the_inventory_api_returns_409_if_reader_isnt_setup()
    {
        $readerConfig = $this->createAStoreAndUploadPOG();
        $readerConfig->reader_mac = "12345";

        $this->uploadPlanogram($readerConfig);
        $this->assertResponseStatus(409);
    }

    /** @test */
    public function the_inventory_can_be_adjusted ()
    {
        /** @var ReaderConfig $readerC */
        $readerC = $this->createAStoreAndUploadPOG(1, 1);

        $readerC->addInventoryData('FF001', 3, 0);
        $this->uploadInventory($readerC);

        $readerC->time->addMinutes(10);
        $readerC->addInventoryData('FF001', 2, 0);
        $this->uploadInventory($readerC);

        $pusher = $readerC->getPushers()->first();

//        dd ([
//            'pusher' => $pusher->toArray(),
//            'inv'   => Inventory::where('pusher_id', $pusher->id)->get(),
//        ]);

        $this->assertCount(2, $pusher->inventories);
        $this->assertEquals(2, $pusher->latestInventory->tags_blocked);
    }

    /** @test */
    public function no_row_is_inserted_if_the_data_doesnt_change()
    {
        $readerC = $this->createAStoreAndUploadPOG(1,1);

        $readerC->addInventoryData('FF001', 3, 0);
        $this->uploadInventory($readerC);

        $readerC->time->addMinutes(10);

        $this->uploadInventory($readerC);

        $this->assertCount(1, $readerC->getPushers()->first()->inventories);
    }


    /** @test */
    public function rows_are_inserted_if_the_data_changes_and_reverts_back()
    {
        $readerC = $this->createAStoreAndUploadPOG(1,1);

        $readerC->addInventoryData('FF001', 3, 0);
        $this->uploadInventory($readerC);

        $readerC->time->addMinutes(10);
        $readerC->addInventoryData('FF001', 2, 0);
        $this->uploadInventory($readerC);

        $readerC->time->addMinutes(10);
        $readerC->addInventoryData('FF001', 3, 0);
        $this->uploadInventory($readerC);

        $this->assertCount(3, $readerC->getPushers()->first()->inventories);
    }

}