<?php

use Martindevnow\Smartshelf\Engineering\Pusher;
use Martindevnow\Smartshelf\Engineering\Reader;

class ReaderUniTest extends TestCase {

    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /** @test */
    public function a_reader_has_pushers() {
        $reader = factory(Reader::class)->create();

        $pushers = factory(Pusher::class, 4)->create([
            'reader_id' => $reader->id,
            'active'    => 1,
        ]);

        $this->assertCount(4, $reader->pushers);
    }

    /** @test */
    public function a_reader_can_have_inactive_pushers() {
        $reader = factory(Reader::class)->create();

        $pushers = factory(Pusher::class, 4)->create([
            'reader_id' => $reader->id,
            'active'    => 1,
        ]);
        $pushers = factory(Pusher::class, 4)->create([
            'reader_id' => $reader->id,
            'active'    => 0,
        ]);

        $this->assertCount(4, $reader->pushers);
        $this->assertCount(8, $reader->allPushers);
    }

}