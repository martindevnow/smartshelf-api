<?php

namespace Martindevnow\Smartshelf\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
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
     * Static Named Constructor
     *
     * @param $name
     * @return mixed
     */
    public static function byName($name)
    {
        return static::whereName($name)->firstOrFail();
    }

    /**
     * A Brand hasMany Products
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return$this->hasMany(Product::class);
    }

}
