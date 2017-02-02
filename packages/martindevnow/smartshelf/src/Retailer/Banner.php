<?php

namespace Martindevnow\Smartshelf\Retailer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use SoftDeletes;

    /**
     * Fields which may be mass-assigned
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'code',
        'retailer_id',
    ];

    // Relationships
    /**
     * A Banner belongsTo a Retailer
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function retailer()
    {
        return $this->belongsTo(Retailer::class);
    }

    /**
     * A Banner can hasMany Locations
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
