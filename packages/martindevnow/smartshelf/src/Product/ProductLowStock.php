<?php

namespace Martindevnow\Smartshelf\Product;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Martindevnow\Smartshelf\Product\Product;
use Martindevnow\Smartshelf\Retailer\Location;

class ProductLowStock extends Model
{
    use SoftDeletes;


    /**
     * Fields that may be mass-assigned
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'location_id',
        'low_stock_at',
        'restocked_at',
    ];

    /**
     * Dates to be formatted as Carbon
     *
     * @var array
     */
    protected $dates = [
        'low_stock_at',
        'restocked_at'
    ];

//    /**
//     * Return the amount of time this Pusher was LowStock
//     *
//     * @return int
//     */
//    public function getLowStockTime()
//    {
//        if (! $this->restocked_at || ! $this->restocked_at instanceof Carbon || $this->restocked_at == null)
//            return 0;
//
//        return $this->restocked_at->diffInMinutes($this->low_stock_at);
//    }

    /**
     * Returns the Product which is LowStock
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Returns the Location where the LowStock occurred
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
