<?php

namespace Martindevnow\Smartshelf\Core;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Martindevnow\Smartshelf\ACL\User;
use Martindevnow\Smartshelf\Retailer\Location;

class Contact extends Model
{
    use SoftDeletes;

    /**
     * Fields which may be mass-assigned
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'title',
        'email',
        'cell_number',
        'cell_provider',
        'client_id',
    ];

    /**
     * Assign $this Contact to the given Notification and the given Location only
     *
     * @param Notification $notification
     * @param Location $location
     * @return mixed
     */
    public function receiveNotificationFromLocation(Notification $notification, Location $location)
    {
        return $this->notifications()->attach($notification->id, [
            'location_id'   => $location->id,
        ]);
    }

    /**
     * Get the Contact's email based on the Notification being for Mobile or Not
     *
     * @param $forMobile
     * @return mixed|string
     */
    public function getEmail($forMobile)
    {
        if ($forMobile)
            return $this->cellEmail();

        return $this->email;
    }

    /**
     * Get the Cell Email to send an SMS to the phone via the provider's email
     *
     * @return string
     */
    public function cellEmail()
    {
        return convertCellToEmail($this->cell_number, $this->cell_provider);
    }

    /**
     * Returns whether or not a notification is linked to this account
     *
     * @param $notification_id
     * @return bool
     */
    public function receivesNotification($notification_id)
    {
        return ! $this->notifications->filter(function($notification) use ($notification_id)
        {
            return $notification->id == $notification_id;
        })->isEmpty();
    }

    /**
     * Subscribe to a Report
     *
     * @param Location $location
     * @param $type
     * @param $format
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function subscribeToReport(Location $location, $type, $format = 'csv')
    {
        return $this->reportSubscriptions()->create([
            'location_id'   => $location->id,
            'report_type'   => $type,
            'report_format' => $format,
        ]);
    }

    /**
     * Subscribe to a Report
     *
     * @param Collection $locations
     * @param $types
     * @param $formats
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function subscribeToReports(Collection $locations, $types, $formats = 'csv')
    {
        if (! is_array($types))
            $types = [$types];

        if (! is_array($formats))
            $formats = [$formats];

        $subs = [];

        foreach ($locations as $location) {
            foreach ($types as $type) {
                foreach ($formats as $format)
                {
                    $subs[] = $this->subscribeToReport($location, $type, $format);
                }
            }
        }
        return $subs;
    }

    /**
     * UnSubscribe to a type of Report
     *
     * @param Location $location
     * @param $type
     * @param $format
     */
    public function unsubscribeToReport(Location $location, $type, $format = 'csv')
    {
        return ReportSubscription::where('contact_id', $this->id)
            ->where('location_id', $location->id)
            ->where('report_type', $type)
            ->where('report_format', $format)
            ->delete();
    }

    /**
     *
     *
     * @param $notification_id
     * @param int $value
     * @return string
     */
    public function toggleNotification($notification_id, $value = 0)
    {
        if ($value == 1)
            return $this->notifications()->attach($notification_id);

        return $this->notifications()->detach($notification_id);
    }

    /**
     * Define the relationship between Contacts and locations
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Define the relationship between Contacts and their notifications
     *
     * @return $this
     */
    public function notifications()
    {
        return $this->belongsToMany(Notification::class)->withPivot('location_id');
    }

    /**
     * A Contact is createdBy and ownedBy a User
     *
     * @return $this
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The Subscriptions that this Contact has for Reports
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reportSubscriptions()
    {
        return $this->hasMany(ReportSubscription::class);
    }
}
