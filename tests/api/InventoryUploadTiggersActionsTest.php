<?php

class InventoryUploadTiggersActionsTest extends TestCase {

    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /** @test */
    public function it_trigers_a_low_stock_event() {
        $readerC = $this->createAStoreAndUploadPOG(1,1);
    }
}