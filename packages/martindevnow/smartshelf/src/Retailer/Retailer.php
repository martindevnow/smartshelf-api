<?php

namespace Martindevnow\Smartshelf\Retailer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Retailer extends Model
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
    ];

    /**
     * A Retailer can hasMany Banners
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function banners()
    {
        return $this->hasMany(\Martindevnow\Smartshelf\Retailer\Banner::class);
    }
}
