<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tblCauHoi extends Model
{
    //use HasFactory;

    protected $table = 'tblCauHoi';

    public $timestamps = false;

    protected $fillable = ['id', 'stt', 'noidung', 'cauliet', 'giaithichdapan', 'active', 'in_250', 'stt_250', 'chuong'];

    public function dapAns()
    {
        // bảng đáp án là tblcautraloi, FK tới câu hỏi là 'CauHoiId'
        return $this->hasMany(tblCauTraLoi::class, 'CauHoiId', 'id');
    }

    // public function hinhAnhs()
    // {
    //     // bảng hình ảnh là tblhinhanh, FK 'CauHoiId'
    //     return $this->hasMany(tblHinhAnh::class, 'CauHoiId', 'id');
    // }
}
