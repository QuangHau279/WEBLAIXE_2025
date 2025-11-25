@extends('admin.layout.layout')

@section('content')
<!-- Page Content -->
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Bằng lái
                    <small>Thêm</small>
                </h1>
            </div>
            <!-- /.col-lg-12 -->
            <div class="col-lg-12" style="padding-bottom:70px">
             @if(count($errors)>0)
             <div class="alert alert-danger">
                @foreach($errors->all() as $err)
                {{ $err }}<br>
                @endforeach
            </div>
            @endif

            <form action="{{ route('admin.banglai_store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>Tên</label>
                    <input type="text" name="ten" value="{{ old('ten') }}" placeholder="Tên" class="form-control">
                </div>
                <div class="form-group">
                    <label>Số câu hỏi</label>
                    <input type="number" step="1" name="socauhoi" value="{{ old('socauhoi') }}" placeholder="Số câu hỏi" class="form-control">
                </div>
                <div class="form-group">
                    <label>Số câu đúng tối thiểu</label>
                    <input type="number" step="1" name="mincauhoidung" value="{{ old('mincauhoidung') }}" placeholder="Số câu hỏi đúng tối thiểu" class="form-control">
                </div>
                <div class="form-group">
                    <label>Thời gian (phút)</label>
                    <input type="number" step="1" name="thoigian" value="{{ old('thoigian') }}" placeholder="Thời gian" class="form-control">
                </div>
                <div class="form-group">
                    <label>Kích hoạt</label>
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" value="1" {{ old('active') ? 'checked' : '' }}>
                </div>
                <button type="reset" class="btn btn-default">Làm Mới</button>
                <button type="submit" class="btn btn-primary">Lưu</button>
            </form>
        </div>
    </div>
    <!-- /.row -->
</div>
<!-- /.container-fluid -->
</div>
<!-- /#page-wrapper -->
@endsection
