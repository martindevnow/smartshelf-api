<?php

namespace Martindevnow\Smartshelf\Retailer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use SoftDeletes;

    /**
     * Fields which may be mass-assigned
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'phone',
        'fax',
        'street_1',
        'street_2',
        'city',
        'province',
        'postal_code',
        'country',
    ];

    /**
     * An Address belongsTo a Location
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location ()
    {
        return $this->belongsTo(Location::class);
    }
}
