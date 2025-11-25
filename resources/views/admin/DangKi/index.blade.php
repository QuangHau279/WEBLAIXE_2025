@php
  use Illuminate\Support\Str;
@endphp

@extends('admin.layout.layout')
@section('content')
<!-- Page Content -->

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12" style="margin-bottom: 50px;">
                <h1 class="page-header">Đăng kí
                    <small>Danh Sách</small>
                </h1>
                
                <table class="table table-striped table-bordered table-hover text-center" id="example">
                    <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th class="text-center">Name</th>
                            <th class="text-center">Phone</th>
                            <th class="text-center">License</th>
                            <th class="text-center">Created_at</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leads as $lead)
                        <tr class="odd gradeX">
                            <td>{{ $lead["id"] }}</td>
                            <td>
                                {{ $lead["name"] }}
                            </td>
                            <td>
                                {{ $lead["phone"] }}
                            </td>
                            <td>
                                {{ $lead["license"] }}
                            </td>
                            <td>
                                {{ $lead["created_at"] }}
                            </td>
                            
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</div>
<!-- /#page-wrapper -->

@endsection
@section('script')

<script src="admin_asset/bower_components/datatables/media/js/jquery.dataTables.min.js"></script>
<script src="admin_asset/bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js"></script>
<script src="js/bootstrap-flash-alert.js"></script>
@endsection
