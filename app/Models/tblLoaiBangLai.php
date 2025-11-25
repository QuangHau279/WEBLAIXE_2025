<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tblLoaiBangLai extends Model
{
    //use HasFactory;

    protected $table = 'tblLoaiBangLai';

    public $timestamps = false;

    protected $fillable = ['id', 'ten', 'socauhoi', 'mincauhoidung', 'active', 'thoigian'];
}
