<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CauHoiController;
use App\Http\Controllers\HinhAnhController;
use App\Http\Controllers\ThiController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\TrafficSignController;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\TrafficSignDetectionController;
use App\Http\Controllers\TestDetectionController;

use App\Http\Controllers\TblLoaiBangLaiController;
use App\Http\Controllers\TblCauHoiController;
use App\Http\Controllers\TblCauTraLoiController;
use App\Http\Controllers\TblBoCauHoiCauHoiController;
use App\Http\Controllers\TblMoPhongController;
use App\Http\Controllers\TblHinhAnhController;
use App\Http\Controllers\TblMediaController;
use App\Http\Controllers\TblCauHoiBangLaiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TblBoCauHoiController;
use App\Http\Controllers\TblMoPhongBangLaiController;

/* ========= TRANG CƠ BẢN ========= */
Route::view('/', 'home.index')->name('home');
Route::get('/mo-phong', [SimulationController::class, 'index'])->name('simulation');
Route::get('/mo-phong/cau-hinh', [SimulationController::class, 'configPoints'])->name('simulation.config');
Route::post('/mo-phong/bat-dau-thi-thu', [SimulationController::class, 'startTest'])->name('simulation.start-test');
Route::post('/mo-phong/reset-thi-thu', [SimulationController::class, 'resetTest'])->name('simulation.reset-test');
Route::view('/bien-bao', 'pages.bienbao')->name('bienbao');

/* ========= BIỂN BÁO GIAO THÔNG ========= */
Route::prefix('traffic-signs')->name('traffic-signs.')->group(function () {
    Route::get('/', [TrafficSignController::class, 'index'])->name('index');
    Route::get('/{slug}', [TrafficSignController::class, 'show'])->name('show');
});

Route::get('/thuc-hanh-lai-xe', [PracticeController::class, 'videosThucHanh'])
     ->name('videothuchanh');

/* Ôn tập riêng cho Xe máy (A1) */
Route::view('/on-tap-xe-may', 'pages.Ontapxemay')->name('xemay');


/* ========= ÔN TẬP 600 CÂU (CHUNG) =========
   /on-tap             : menu -> redirect vào /on-tap/cau-hoi
   /on-tap/cau-hoi     : trang ôn tập mặc định
   /on-tap/cau-hoi/{stt}: mở trực tiếp câu {stt}
*/
Route::get('/on-tap', fn () => redirect()->route('practice.cauhoi'))->name('practice');

Route::get('/on-tap/cau-hoi/{stt?}', function ($stt = null) {
    // Dùng view đang có: resources/views/cauhoi/cauhoi.blade.php
    return view('cauhoi.cauhoi', ['initialStt' => $stt]);
})->whereNumber('stt')->name('practice.cauhoi');

/* Giữ link cũ không gãy */
Route::redirect('/cau-hoi', '/on-tap/cau-hoi', 301);
Route::get('/cau-hoi/{stt}', fn ($stt) => redirect()->route('practice.cauhoi', ['stt' => $stt]))
    ->whereNumber('stt');
Route::redirect('/cauhoi', '/on-tap/cau-hoi', 301);


/* ========= API CHO ÔN TẬP ========= */
Route::prefix('api')->name('api.')->group(function () {
    // Route tìm kiếm cho ôn tập lý thuyết 600 câu (phải đặt trước route {stt})
    Route::get('search', [CauHoiController::class, 'search'])->name('search');
    
    Route::prefix('cau-hoi')->name('cauhoi.')->group(function () {
        Route::get('grid',          [CauHoiController::class, 'grid'])->name('grid');
        Route::get('{stt}',         [CauHoiController::class, 'byStt'])->whereNumber('stt')->name('byStt');
        Route::get('{id}/hinh-anh', [HinhAnhController::class, 'byId'])->whereNumber('id')->name('hinhanh');
    });

    /* ========= API CHO XE MÁY (250 CÂU) ========= */
    Route::prefix('xe-may')->name('xemay.')->group(function () {
        Route::get('grid',          [CauHoiController::class, 'gridXeMay'])->name('grid');
        Route::get('search',        [CauHoiController::class, 'searchXeMay'])->name('search');
        Route::get('cau-hoi/{stt250}', [CauHoiController::class, 'bySttXeMay'])->whereNumber('stt250')->name('byStt');
    });

    /* ========= API THI THỬ ========= */
    Route::prefix('thi')->group(function () {
        Route::get('preset',   [ThiController::class, 'presets']);
        Route::post('tao-de',  [ThiController::class, 'create']);
        Route::post('nop-bai', [ThiController::class, 'submit']);
    });

    /* ========= API MÔ PHỎNG ========= */
    Route::prefix('simulation')->name('simulation.')->group(function () {
        Route::post('save-points', [SimulationController::class, 'savePoints'])->name('save-points');
        Route::get('video/{id}', [SimulationController::class, 'getVideo'])->whereNumber('id')->name('video');
    });

    /* ========= API ĐĂNG KÝ ========= */
    Route::post('leads', [LeadController::class, 'store'])->name('leads.store');

    /* ========= API NHẬN DIỆN BIỂN BÁO ========= */
    Route::post('detect-traffic-sign', [TrafficSignDetectionController::class, 'detect'])->name('detect-traffic-sign');
});


/* ========= TRANG THI THỬ ========= */
Route::view('/thi-thu', 'thi.thi')->name('thi.thu');
Route::get('/thi-thu/de/{id}', fn ($id) => view('thi.lamde', ['deId' => $id]))
    ->whereNumber('id')->name('thi.lamde');

Route::get('/chatbox', fn() => view('pages.chatbox'))->name('chatbox');

//PHUC
Route::get('/index', [DashboardController::class, 'home'])->name('dashboard');
Route::prefix('admin')->name('admin.')->group(function () {
Route::get('/', function () {
    return redirect()->route('dashboard');
});



Route::get('/banglai', [TblLoaiBangLaiController::class, 'index'])->name('banglai_list');
Route::get('banglai/update/{id}', [TblLoaiBangLaiController::class, 'edit'])->name('banglai_edit');
Route::put('banglai/update/{id}', [TblLoaiBangLaiController::class, 'update'])->name('banglai_update');
Route::get('banglai/add', [TblLoaiBangLaiController::class, 'create']);
Route::post('banglai/add', [TblLoaiBangLaiController::class, 'store'])->name('banglai_store');
Route::delete('banglai/delete/{id}', [TblLoaiBangLaiController::class, 'destroy'])->name('banglai_destroy');

Route::get('/cauhoi', [TblCauHoiController::class, 'index'])->name('cauhoi_list');
Route::get('cauhoi/update/{id}', [TblCauHoiController::class, 'edit'])->name('cauhoi_edit');
Route::put('cauhoi/update/{id}', [TblCauHoiController::class, 'update'])->name('cauhoi_update');
Route::get('cauhoi/add', [TblCauHoiController::class, 'create']);
Route::post('cauhoi/add', [TblCauHoiController::class, 'store'])->name('cauhoi_store');
Route::delete('cauhoi/delete/{id}', [TblCauHoiController::class, 'destroy'])->name('cauhoi_destroy');

Route::get('/cautraloi', [TblCauTraLoiController::class, 'index'])->name('cautraloi_list');
Route::get('cautraloi/update/{id}', [TblCauTraLoiController::class, 'edit'])->name('cautraloi_edit');
Route::put('cautraloi/update/{id}', [TblCauTraLoiController::class, 'update'])->name('cautraloi_update');
Route::get('cautraloi/add', [TblCauTraLoiController::class, 'create']);
Route::post('cautraloi/add', [TblCauTraLoiController::class, 'store'])->name('cautraloi_store');
Route::get('cautraloi/delete/{id}', [TblCauTraLoiController::class, 'destroy'])->name('cautraloi_destroy');

Route::get('media', [TblMediaController::class, 'listview'])->name('media-list');
Route::get('media-upload', [TblMediaController::class, 'imageUpload'] )->name('media.upload');
Route::post('media-upload', [TblMediaController::class, 'imageUploadPost'] )->name('media.upload.post');
Route::post('media-upload-editor', [TblMediaController::class, 'imageUploadEditorPost'] )->name('media_upload');
Route::get('media/delete/{image}', [TblMediaController::class, 'getDelete']);

Route::get('/hinhanh', [TblHinhAnhController::class, 'index'])->name('hinhanh_list');
Route::get('hinhanh/update/{id}', [TblHinhAnhController::class, 'edit'])->name('hinhanh_edit');
Route::put('hinhanh/update/{id}', [TblHinhAnhController::class, 'update'])->name('hinhanh_update');
Route::get('hinhanh/add', [TblHinhAnhController::class, 'create']);
Route::post('hinhanh/add', [TblHinhAnhController::class, 'store'])->name('hinhanh_store');
Route::get('hinhanh/delete/{id}', [TblHinhAnhController::class, 'destroy'])->name('hinhanh_destroy');

Route::get('/cauhoibanglai', [TblCauHoiBangLaiController::class, 'index'])->name('cauhoibanglai_list');
Route::get('cauhoibanglai/update/{id}', [TblCauHoiBangLaiController::class, 'edit'])->name('cauhoibanglai_edit');
Route::put('cauhoibanglai/update/{id}', [TblCauHoiBangLaiController::class, 'update'])->name('cauhoibanglai_update');
Route::get('cauhoibanglai/add', [TblCauHoiBangLaiController::class, 'create']);
Route::post('cauhoibanglai/add', [TblCauHoiBangLaiController::class, 'store'])->name('cauhoibanglai_store');
Route::get('cauhoibanglai/delete/{id}', [TblCauHoiBangLaiController::class, 'destroy'])->name('cauhoibanglai_destroy');

Route::get('bocauhoi', [TblBoCauHoiController::class, 'index'])->name('bocauhoi_list');
Route::get('bocauhoi/update/{id}', [TblBoCauHoiController::class, 'edit'])->name('bocauhoi_edit');
Route::put('bocauhoi/update/{id}', [TblBoCauHoiController::class, 'update'])->name('bocauhoi_update');
Route::get('bocauhoi/add', [TblBoCauHoiController::class, 'create']);
Route::post('bocauhoi/add', [TblBoCauHoiController::class, 'store'])->name('bocauhoi_store');
Route::get('bocauhoi/delete/{id}', [TblBoCauHoiController::class, 'destroy'])->name('bocauhoi_destroy');

Route::get('bochch', [TblBoCauHoiCauHoiController::class, 'index'])->name('bochch_list');
Route::get('bochch/update/{id}', [TblBoCauHoiCauHoiController::class, 'edit'])->name('bochch_edit');
Route::put('bochch/update/{id}', [TblBoCauHoiCauHoiController::class, 'update'])->name('bochch_update');
Route::get('bochch/add', [TblBoCauHoiCauHoiController::class, 'create']);
Route::post('bochch/add', [TblBoCauHoiCauHoiController::class, 'store'])->name('bochch_store');
Route::get('bochch/delete/{id}', [TblBoCauHoiCauHoiController::class, 'destroy'])->name('bochch_destroy');

Route::get('mophong', [TblMoPhongController::class, 'index'])->name('mophong_list');
Route::get('mophong/update/{id}', [TblMoPhongController::class, 'edit'])->name('mophong_edit');
Route::put('mophong/update/{id}', [TblMoPhongController::class, 'update'])->name('mophong_update');
Route::get('mophong/add', [TblMoPhongController::class, 'create']);
Route::post('mophong/add', [TblMoPhongController::class, 'store'])->name('mophong_store');
Route::get('mophong/delete/{id}', [TblMoPhongController::class, 'destroy'])->name('mophong_destroy');

Route::get('mophongbanglai', [TblMoPhongBangLaiController::class, 'index'])->name('mophongbanglai_list');
Route::get('mophongbanglai/update/{id}', [TblMoPhongBangLaiController::class, 'edit'])->name('mophongbanglai_edit');
Route::put('mophongbanglai/update/{id}', [TblMoPhongBangLaiController::class, 'update'])->name('mophongbanglai_update');
Route::get('mophongbanglai/add', [TblMoPhongBangLaiController::class, 'create']);
Route::post('mophongbanglai/add', [TblMoPhongBangLaiController::class, 'store'])->name('mophongbanglai_store');
Route::get('mophongbanglai/delete/{id}', [TblMoPhongBangLaiController::class, 'destroy'])->name('mophongbanglai_destroy');

Route::get('/dangki', [LeadController::class, 'index'])->name('dangki_list');
}
);
//END PHUC

