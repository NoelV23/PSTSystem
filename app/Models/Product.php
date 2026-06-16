<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'variant_group_label',
        'sku',
        'category_id',
        'base_unit',
        'color',
        'thickness',
        'thickness_spec_label',
        'measurement_unit',
        'default_length',
        'default_width',
        'default_height',
        'description',
    ];

    protected $casts = [
        'default_length' => 'decimal:2',
        'default_width' => 'decimal:2',
        'default_height' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function setComponents()
    {
        return $this->hasMany(BundleComponent::class, 'bundle_product_id');
    }

    public function componentProducts()
    {
        return $this->belongsToMany(Product::class, 'bundle_components', 'bundle_product_id', 'component_product_id')
                    ->withPivot('quantity_required')
                    ->withTimestamps();
    }
} 