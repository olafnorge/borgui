@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card p-4">
                <div class="card-body">
                    <a class="input-group btn btn-outline-secondary btn-block bg-light" href="{{ route('auth.redirect') }}">
                        <div class="input-group-prepend">
                            <img src="{{ mix('images/btn_google_dark_normal_ios.svg') }}" height="66">
                        </div>
                        <span class="form-control form-control-plaintext border-0 pt-4 text-secondary">Sign in with Google</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
