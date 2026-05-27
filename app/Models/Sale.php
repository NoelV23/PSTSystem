<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'user_id',
        'reference_number',
        'total_amount',
        'payment_method',
        'is_delivered',
        'delivered_to',
        'delivery_address',
        'delivery_date',
        'delivery_note',
        'customer_pickup',
        'delivery_contact_phone',
        'delivery_fee',
        'is_installation',
        'installation_address',
        'description',
        'status',
        'customer_name',
    ];

    protected $casts = [
        'customer_pickup' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function installationProductUsages()
    {
        return $this->hasMany(InstallationProductUsage::class);
    }
} 