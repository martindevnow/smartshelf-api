<?php

namespace Martindevnow\Smartshelf\Retailer;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Martindevnow\Smartshelf\ACL\User;
use Martindevnow\Smartshelf\Product\Product;
use Martindevnow\Smartshelf\Product\ProductLowStock;
use Martindevnow\Smartshelf\Product\ProductOutOfStock;
use Martindevnow\Smartshelf\Core\Contact;
use Martindevnow\Smartshelf\Core\Report;
use Martindevnow\Smartshelf\Core\ReportSubscription;
use Martindevnow\Smartshelf\Engineering\Pusher;
use Martindevnow\Smartshelf\Engineering\PusherLowStock;
use Martindevnow\Smartshelf\Engineering\PusherOutOfStock;
use Martindevnow\Smartshelf\Engineering\Reader;

class Location extends Model
{
    use SoftDeletes;

    /**
     * The FK for this table
     *
     * @var string
     */
    protected $foreign_key = 'location_id';

    /**
     * Fields which may be mass-assigned
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'code',
        'template',
        'banner_id',
    ];

    /*
     * Relationships
     */
    /**
     * A Location belongsTo a Banner
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function banner()
    {
        return $this->belongsTo(Banner::class);
    }

    /**
     * A Location can hasMany Readers
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function readers()
    {
        return $this->hasMany(Reader::class);
    }

    /**
     * A Location hasOne Address
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function address()
    {
        return $this->hasOne(Address::class);
    }

    /**
     * A Location can hasMany Pushers
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function allPushers()
    {
        return $this->hasMany(Pusher::class, 'location_id', 'id');
    }

    /**
     * A Location can hasMany Pushers
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pushers()
    {
        return $this->allPushers()->where('active', 1);
    }

    /**
     * A Location can hasMany Pushers
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activePushers()
    {
        return $this->pushers();
    }

    /**
     * A Location can hasMany PusherOutOfStocks
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pusherOutOfStocks()
    {
        return $this->hasMany(PusherOutOfStock::class);
    }

    /**
     * A Location can hasMany ProductOutOfStocks
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productOutOfStocks()
    {
        return $this->hasMany(ProductOutOfStock::class);
    }

    /**
     * A Location can hasMany PusherLowStocks
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pusherLowStocks()
    {
        return $this->hasMany(PusherLowStock::class);
    }

    /**
     * A Location can hasMany ProductLowStocks
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productLowStocks()
    {
        return $this->hasMany(ProductLowStock::class);
    }

    /**
     * Many Locations can be assigned to Many Users
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'location_user');
    }

    /**
     * A Location (among other entities) can hasMany Reports
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    /**
     * A Location can hasMany ReportSubscriptions
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reportSubscriptions()
    {
        return $this->hasMany(ReportSubscription::class);
    }
}
