@extends('layouts.app')

@section('title', 'Biển báo ' . $category->name)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('traffic-signs.index') }}">Biển báo Giao thông</a></li>
                    <li class="breadcrumb-item active">{{ $category->name }}</li>
                </ol>
            </nav>
            
            <h1 class="mb-4">Biển báo {{ $category->name }}</h1>
            
            @if($category->signs->isNotEmpty())
            <div class="row">
                @foreach($category->signs as $sign)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="card-title">{{ $sign->code ?? 'N/A' }} - {{ $sign->title }}</h3>

                            @if($sign->image_path)
                            <img src="{{ asset('images/signs/' . $sign->image_path) }}" class="card-img-top mb-3" alt="{{ $sign->title }}" style="height: 120px; object-fit: cover;">
                            @else
                            <div class="bg-light d-flex align-items-center justify-content-center mb-3" style="height: 120px;">
                                <span class="text-muted">Chưa có hình ảnh</span>
                            </div>
                            @endif
                            
                            @if($sign->description)
                            <p class="card-text text-muted">{{ $sign->description }}</p>
                            @endif
                            
                            <!-- @if($sign->source_attrib)
                            <small class="text-muted">{{ $sign->source_attrib }}</small>
                            @endif -->
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="alert alert-info">
                <h4>Chưa có biển báo nào</h4>
                <p>Danh mục {{ $category->name }} hiện chưa có dữ liệu biển báo.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
