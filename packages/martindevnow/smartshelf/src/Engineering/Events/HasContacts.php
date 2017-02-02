<?php

namespace Martindevnow\Smartshelf\Engineering\Events;

use Martindevnow\Smartshelf\Core\Notification;

trait HasContacts {

    /**
     * Get the contacts for a Notification
     *
     * @return array
     */
    public function getContacts()
    {
        $notification = Notification::where('event_class', get_class($this))->first();

        if (! $notification )
            return [];

        return $notification->contacts;
    }
}