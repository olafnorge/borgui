@extends('layouts.app')

@section('title', __('Not Found'))
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="clearfix">
                <h1 class="float-left display-3 mr-4">404</h1>
                <h4 class="pt-3">Oops! You're lost.</h4>
                <p class="text-muted">{{ __('Not Found') }}</p>
            </div>
        </div>
    </div>
@endsection
