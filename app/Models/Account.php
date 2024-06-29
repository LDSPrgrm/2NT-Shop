<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'ign',
        'id',
        'server',
        'diamonds',
        'wdp_count',
        'start_date',
        'end_date',
        'days_left',
        'estimated_total_diamonds',
        'current_diamonds_auto',
        'current_diamonds_manual'
    ];
}
