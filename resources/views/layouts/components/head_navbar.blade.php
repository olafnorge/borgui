<header class="app-header navbar">
    <button class="navbar-toggler sidebar-toggler d-lg-none mr-auto" type="button" data-toggle="sidebar-show">
        <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand" href="{{ route('repository.index') }}">
        <img class="navbar-brand-full" src="{{ mix('images/logo.svg') }}" width="89" height="25" alt="{{ config('app.name') }}">
        <img class="navbar-brand-minimized" src="{{ mix('images/logo_min.svg') }}" width="30" height="30" alt="{{ config('app.name') }}">
    </a>
    <button class="navbar-toggler sidebar-toggler d-md-down-none" type="button" data-toggle="sidebar-lg-show">
        <span class="navbar-toggler-icon"></span>
    </button>
    <ul class="nav navbar-nav ml-auto">
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                <img class="img-avatar" src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->email }}">
            </a>
            <div class="dropdown-menu dropdown-menu-right">
{{--                <div class="dropdown-header text-center">--}}
{{--                    <strong>Account</strong>--}}
{{--                </div>--}}
{{--                <a class="dropdown-item" href="#">--}}
{{--                    <i class="fa fa-user"></i> Profile</a>--}}
{{--                <a class="dropdown-item" href="#">--}}
{{--                    <i class="fa fa-wrench"></i> Settings</a>--}}
{{--                <div class="dropdown-divider"></div>--}}
                <button class="dropdown-item do-post" data-formid="logout-form">
                    <i class="fa fa-lock"></i> Logout
                    {{ Form::open(['route' => 'logout', 'hidden' => true, 'id' => 'logout-form', 'method' => 'POST']) }}{{ Form::close() }}
                </button>
            </div>
        </li>
    </ul>
</header>
