 <!-- Navigation -->
 <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="/">BLX</a>
    </div>
    <!-- /.navbar-header -->

    <ul class="nav navbar-top-links navbar-right">
        <!-- /.dropdown -->
        <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                Tài Khoản <i class="fa fa-caret-down"></i>
            </a>
            <ul class="dropdown-menu dropdown-user">
                @if(Auth::check())
                <li><a href="#"><i class="fa fa-user fa-fw"></i> {{ Auth::user()->name }}</a></li>
                <li class="divider"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" style="background: none; border: none; padding: 3px 20px; width: 100%; text-align: left; cursor: pointer; color: #333;">
                            <i class="fa fa-sign-out fa-fw"></i> Đăng xuất
                        </button>
                    </form>
                </li>
                @else
                <li><a href="{{ route('login') }}"><i class="fa fa-sign-in fa-fw"></i> Đăng nhập</a></li>
                @endif
            </ul>
            <!-- /.dropdown-user -->
        </li>
        <!-- /.dropdown -->
    </ul>
    <!-- /.navbar-top-links -->

    @include('admin.patials.menu')
    <!-- /.navbar-static-side -->
</nav>
