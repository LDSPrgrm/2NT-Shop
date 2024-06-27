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
    ];
}
