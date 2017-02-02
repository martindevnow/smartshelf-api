<?php

namespace Martindevnow\Smartshelf\Engineering;

use App\Events\InventoryOutOfStock;
use App\Events\LowStockAlert;
use App\Events\PusherWasRestocked;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Martindevnow\Smartshelf\Product\Product;
use Martindevnow\Smartshelf\Retailer\Location;

class Inventory extends Model
{
    use SoftDeletes;

    /**
     * Fields which may be mass-assigned
     *
     * @var array
     */
    protected $fillable = [
        'pusher_id',
        'product_id',
        'reader_id',
        'location_id',
        'tags_blocked',
        'paddle_exposed',
        'status',
        'oos',
        'created_at',
        'updated_at',
        'item_count',
        'prev_item_count',
        'product_item_count',
        'pusher_ooses',
        'prev_oos_at',
        'number_of_pushers',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'prev_oos_at',
    ];


    /*
     * Relationships
     */

    /**
     * An Inventory belongsTo a Pusher
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pusher()
    {
        return $this->belongsTo(Pusher::class);
    }

    /**
     * An Inventory belongsTo a Reader
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reader()
    {
        return $this->belongsTo(Reader::class);
    }

    /**
     * A Pusher belongsTo a Location
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * This Inventory belongsTo a Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }



    public function setPreviousItemCount()
    {
        if ($this->pusher == null)
        {
            $prevInv = Inventory::where('pusher_id', $this->pusher_id)
                ->where('created_at', '<', $this->created_at)
                ->orderBy('created_at', 'DESC')
                ->first();
            if ($prevInv == null)
                return $this;

            $this->prev_item_count = $prevInv->item_count;
            return $this;
        }


        $prevInv = $this->pusher->getPreviousInventory($this);
        if ($prevInv == null)
            return $this;

        $this->prev_item_count = $prevInv->item_count;
        return $this;
    }

    public function setProductItemCount()
    {
        /** @var Product $product */
        $product = $this->product;
        $prodInv = $product->inventoriesAt($this->created_at, $this->location);

//        $prodInv = Inventory::where('product_id', $product->id)
//            ->where('active', '1')
//            ->where('location_id', $this->location_id);

        if (! $prodInv->count())
            return null;

//        if ($this->created_at->format('H:i') == "11:00")
//            dd ([$this->toArray(), $prodInv->toArray(), $prodInv->sum('oos')]);

        $this->pusher_ooses =       $prodInv->sum('oos');
        $this->product_item_count = $prodInv->sum('item_count');
        return $this;
    }

    public function setPusherOutOfStocks()
    {
        return null;
//
//        $pusherOOS = $this->product->pusherOutOfStockAt($this->created_at, $this->location_id);
//        $this->pusher_ooses = $pusherOOS->count() + $this->oos;
//        $this->save();
    }

    public function isInStock()
    {
        return !! (is_numeric($this->status) || $this->status == "RESTOCK");
    }

    public function isNotInStock()
    {
        return ! $this->isInStock();
    }

    public function isLowStock()
    {
        return !! ( $this->status == "RESTOCK" || $this->status == "OOS");
    }

    public function isNotLowStock()
    {
        return ! $this->isLowStock();
    }

    /**
     * Returns whether or not this is OOS
     *
     * @return bool
     */
    public function isOutOfStock()
    {
        return !! ($this->status == "OOS");
    }

    /**
     * Returns whether or not this is OOS
     *
     * @return bool
     */
    public function isNotOutOfStock()
    {
        return ! $this->isOutOfStock();
    }

}
