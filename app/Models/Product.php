<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'category_id',
        'is_bundle',
        'base_unit',
        'default_length',
        'default_width',
        'default_height',
        'price_per_unit',
        'price_per_piece',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function bundleComponents()
    {
        return $this->hasMany(BundleComponent::class, 'bundle_product_id');
    }
} 