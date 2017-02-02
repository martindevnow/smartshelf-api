<?php

namespace Martindevnow\Smartshelf\Engineering\Events;

use Carbon\Carbon;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Martindevnow\Smartshelf\Engineering\Inventory;
use Martindevnow\Smartshelf\Engineering\Pusher;

class InventoryOutOfStock
{
    use SerializesModels;
    use HasContacts;

    /**
     * @var Pusher
     */
    public $pusher;
    /**
     * @var Inventory
     */
    public $inventory;
    /**
     * @var Carbon
     */
    public $time;

    /**
     * Create a new event instance.
     *
     * @param Inventory $inventory
     */
    public function __construct(Inventory $inventory)
    {
        $this->inventory = $inventory;
        $this->pusher = $inventory->pusher;
        $this->time = $inventory->created_at;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
