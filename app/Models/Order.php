<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'ign',
        'id',
        'sender_ign',
        'sender_id',
        'item_name',
        'item_price',
        'quantity',
        'total_price',
        'month',
        'status',
        'order_date',
        'gift_date',
        'friends_since',
        'date_completed',
    ];
}
