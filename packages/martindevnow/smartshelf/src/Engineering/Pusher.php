<?php

namespace Martindevnow\Smartshelf\Engineering;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Martindevnow\Smartshelf\Product\Product;
use Martindevnow\Smartshelf\Product\ProductOutOfStock;
use Martindevnow\Smartshelf\Retailer\Location;

class Pusher extends Model
{
    use SoftDeletes;

    protected $foreign_key = 'pusher_id';

    public $processed;
    public $hasOldData;
    public $dataChanged;
    public $currentInventory = null;
    public $previousInventory = null;



    protected $fillable = [
        'location_id',

        // Based on the Header of the Planogram File from the Reader
        'reader_id',

        // Details Imported by the Reader's Planogram File
        'product_id',
        'tray_tag',
        'shelf_number',
        'location_number',
        'total_tags',

        // Calculated on the Server by the Binary file Wei Feng made
        // (Calculated at the time of saving to the database)
        'tags_blocked',
        'item_count',
        'status',

        // Determined by the Server
        'oos',
        'oos_at',

        'low_stock',
        'low_stock_at',

        'low_stock_notified',
        'oos_notified',
        'timed_oos_notified',

        // Deactivated pushers when one breaks, etc
        //   (Deactivated automatically if a new POG is
        //    uploaded without the same specifications)
        'active',
    ];

    protected $guarded = ['*'];

    /**
     * Fields to convert to Carbon
     *
     * @var array
     */
    protected $dates = [
        'oos_at',
        'low_stock_at',
    ];

    /**
     * Activate this Pusher
     *
     * @return $this
     */
    public function activate()
    {
        if ( $this->active )
            return $this;

        $this->active = true;
        $this->save();
        return $this;
    }

    /**
     * Deactivate this Pusher
     *
     * @return $this
     */
    public function deactivate()
    {
        if ( ! $this->active )
            return $this;

        $this->active = false;
        $this->save();
        return $this;
    }

    /**
     * Clear any notifications (This Pusher has been restocked!)
     *
     * @return bool
     */
    public function clearNotificationFlags()
    {
        $this->oos_notified = false;
        $this->timed_oos_notified = false;
        $this->low_stock_notified = false;
        return $this->save();
    }

    /*
     * Pseudo Relationships
     */
    /**
     * Get the most recently uploaded Inventory entry
     *
     * @return mixed
     */
    public function latestInventory()
    {
        return $this->hasOne(Inventory::class, 'pusher_id', 'id')->latest();
    }

    public function getLatestInventory()
    {
        return Inventory::where('pusher_id', $this->id)
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    public function getLatestProductOutOfStock(Location $location, $pending = true)
    {
        if ($pending)
            return ProductOutOfStock::where('product_id', $this->product_id)
                ->where('location_id', $location->id)
                ->where('restocked_at', null)
                ->orderBy('oos_at', 'DESC')
                ->first();
        else
            return ProductOutOfStock::where('product_id', $this->product_id)
                ->where('location_id', $location->id)
                ->orderBy('oos_at', 'DESC')
                ->first();
    }



    /*
     * Relationships
     */
    /**
     * A Pusher belongsTo a Reader
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reader()
    {
        return $this->belongsTo(Reader::class);
    }

    /**
     * A Pusher belongsTo a Location
     * (must be same as the reader's location_id)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * A Pusher belongsTo a Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * A Pusher can hasMany Inventories
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * A Pusher can hasMany PusherOutOfStocks
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pusherOutOfStocks()
    {
        return $this->hasMany(PusherOutOfStock::class);
    }

    /**
     * A Pusher can hasMany PusherLowStocks
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pusherLowStocks()
    {
        return $this->hasMany(PusherLowStock::class);
    }
}
