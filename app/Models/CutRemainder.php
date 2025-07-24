<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CutRemainder extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'branch_id',
        'length_remaining',
        'height',
        'width',
        'area_remaining',
        'location_note',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
} 