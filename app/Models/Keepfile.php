<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keepfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','image','desc'
    ];
}
