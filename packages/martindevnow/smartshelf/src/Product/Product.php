<?php

namespace Martindevnow\Smartshelf\Product;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Martindevnow\Smartshelf\Engineering\Inventory;
use Martindevnow\Smartshelf\Engineering\Pusher;
use Martindevnow\Smartshelf\Engineering\PusherLowStock;
use Martindevnow\Smartshelf\Engineering\PusherOutOfStock;
use Martindevnow\Smartshelf\Retailer\Location;

class Product extends Model
{
    use SoftDeletes;

    public $foreign_key = 'product_id';

    /**
     * Fields which may be mass-assigned
     *
     * @var array
     */
    protected $fillable = [
        'parent_id',
        'brand_id',

        'code',
        'upc',
        'carton_upc',
        'name',
        'flavor',

        'pack_size',
        'pack_quantity',
        'pack_depth_in',

        'blocksPaddleTag',
        'hasImage',
    ];


    /**
     * Fetch by UPC and create if the UPC doesn't exist in DB
     *
     * @param $upc
     * @return static
     */
    public static function findOrCreateByUpc($upc) {
        $product = Product::where('upc', $upc);
        if ($product->count())
            return $product->first();

        return Product::create([
            'upc'   => $upc,
        ]);
    }














    /**
     * Static Named Constructor
     *
     * @param $upc
     * @return mixed
     */
    public static function byUpc($upc)
    {
        $upc = padUpc($upc);
        $product = Product::whereUpc($upc);
        if ($product->count())
            return $product->first();

        return Product::create([
            'upc'           => $upc,
            'brand_id'      => 0,
            'carton_upc'    => 0,
            'code'          => "",
            'name'          => "",
            'flavor'        => "",
            'pack_size'     => "",
            'pack_quantity' => 0,
            'pack_depth_in' => 1.0,
            'hasImage'      => false,
            'blocksPaddleTag'   => true,
        ]);
    }

    /**
     * Return the product name
     *
     * @return string
     */
    public function simpleName()
    {
        return ( $this->brand_id ? $this->brand->name . " " : "" )
        . $this->flavor;
    }

    /**
     * Return the pack size and qty info
     *
     * @return string
     */
    public function packInfo()
    {
        return $this->pack_size . " " . $this->pack_quantity;
    }

    /**
     * Return the full product name
     *
     * @return string
     */
    public function fullName()
    {
        return $this->simpleName() . " " . $this->packInfo();
    }

    /**
     * Return an abbreviation of the product name
     *
     * @return string
     */
    public function shortName()
    {
        $words = explode(" ", $this->simpleName());
        $acronym = "";

        foreach ($words as $w) {
            if (isset($w[0]))
                $acronym .= $w[0];
        }
        return $acronym . " " . $this->packInfo();
    }

    /**
     * Return the Stock level for $this Product at a the given Location
     *
     * @param Location $location
     * @return int|mixed
     */
    public function getStockLevelByLocation(Location $location)
    {
        $pushers = $this->getPushersByLocation($location);
        $inv = 0;
        foreach ($pushers as $pusher)
        {
            /** @var Pusher $pusher */
            $inv += $pusher->item_count;
        }
        return $inv;
    }

    /**
     * Get all pushers with $this Product at the given Location
     *
     * @param Location $location
     * @return mixed
     */
    public function getPushersByLocation(Location $location)
    {
        return $this->pushers()->where('location_id', $location->id)->get();
    }

    /**
     * A Product belongsTo a Brand
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * A Product hasMany Pushers
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pushers()
    {
        return $this->hasMany(Pusher::class);
    }

    /**
     * A Product hasMany Inventories
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * A Product hasMany occurrences of the ProductOutOfStock
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productOutOfStocks()
    {
        return $this->hasMany(ProductOutOfStock::class);
    }

    /**
     * A Product hasMany occurrences of the ProductLowStock
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productLowStocks()
    {
        return $this->hasMany(ProductLowStock::class);
    }

    /**
     * A Product can hasMany PusherOutOfStocks
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pusherOutOfStocks()
    {
        return $this->hasMany(PusherOutOfStock::class);
    }

    /**
     * A Product can hasMany PusherLowStocks
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pusherLowStocks()
    {
        return $this->hasMany(PusherLowStock::class);
    }
}
