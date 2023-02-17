<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = ['ip', 'visited_date', 'visited_time','visits'];

}