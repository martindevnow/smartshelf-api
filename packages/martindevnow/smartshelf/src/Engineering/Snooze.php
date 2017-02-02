<?php

namespace Martindevnow\Smartshelf\Engineering;

use Illuminate\Database\Eloquent\Model;

class Snooze extends Model
{
    public $fillable = [
        'reader_id',
        'end_date',
        'reason',
    ];

    public function reader() {
        return $this->belongsTo(Reader::class);
    }
}
