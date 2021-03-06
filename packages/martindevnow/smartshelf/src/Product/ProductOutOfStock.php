<?php

namespace Martindevnow\Smartshelf\Product;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Martindevnow\Smartshelf\Retailer\Location;

class ProductOutOfStock extends Model
{
    use SoftDeletes;

    /**
     * Fields which may be mass-assigned
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'location_id',
        'oos_at',
        'restocked_at',
    ];

    /**
     * Fields to format as Carbon instances
     *
     * @var array
     */
    protected $dates = [
        'oos_at',
        'restocked_at'
    ];

//    /**
//     * Return the amount of time this Pusher was OOS
//     *
//     * @param Carbon $deadline
//     * @param Carbon $startTime
//     * @return int
//     */
//    public function getOOSTime(Carbon $deadline = null, Carbon $startTime = null)
//    {
//        // If Start time selected, choose the later one (start or OOS_at)
//        if ($startTime && $startTime->gt($this->oos_at))
//            $oos_start_time = $startTime;
//        else
//            $oos_start_time = $this->oos_at;
//
//        // If End time selected, choose that, otherwise until restocked
//        if ($this->restocked_at)
//            $endTime = $this->restocked_at;
//        elseif ($deadline)
//            $endTime = $deadline;
//
//        if (isset($endTime))
//            return $endTime->diffInMinutes($oos_start_time);
//
//        return Carbon::now()->diffInMinutes($oos_start_time);
//
//
//
//        if ($deadline == null)
//            $deadline = Carbon::now();
//
//        if (! $this->restocked_at || ! $this->restocked_at instanceof Carbon || $this->restocked_at == null)
//            return $deadline->diffInMinutes($this->oos_at);
//
//        return $this->restocked_at->diffInMinutes($this->oos_at);
//    }

    /**
     * Returns the Product which is OOS
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Returns the Location where the OOS occurred
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
