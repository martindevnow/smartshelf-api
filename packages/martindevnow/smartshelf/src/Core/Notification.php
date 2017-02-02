<?php

namespace Martindevnow\Smartshelf\Core;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    /**
     * Fields which may be mass-assigned
     *
     * @var array
     */
    protected $fillable = [
        'event_class',
        'view_file',
        'forMobile',
    ];

    /**
     * Database Table Name
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * Static Named Constructor
     *
     * @param $event_class
     * @return mixed
     */
    public static function byListener($event_class)
    {
        return static::where('event_class', $event_class)->first();
    }

    /**
     * Returns a short version of the
     * full class path and name
     *
     * @return mixed
     */
    public function shortClassName()
    {
        $vars = explode('\\', $this->event_class);
        $class = array_pop($vars);
        return str_ireplace("Notification", '', $class);
    }

    /**
     * Return the contact for this notification only at one location
     *
     * @param $location_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getContactsByLocationId($location_id)
    {
        return $this->contacts()
            ->where('active', true)
            ->wherePivot('location_id', '=', $location_id)
            ->get();
    }

    /**
     * Notification can be sent to many contacts
     * but contacts can receive more than one
     * notification based on the relations
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function contacts()
    {
        return $this->belongsToMany(Contact::class)
            ->withPivot('location_id');
    }
}
