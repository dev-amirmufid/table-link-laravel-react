<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasUuids;

    protected $fillable = [
        'item_code',
        'item_name',
        'minimum_price',
        'maximum_price',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
