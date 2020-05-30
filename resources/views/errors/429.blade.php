@extends('layouts.app')

@section('title', __('Too Many Requests'))
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="clearfix">
                <h1 class="float-left display-3 mr-4">429</h1>
                <h4 class="pt-3">Oops! You requested the page too often.</h4>
                <p class="text-muted">{{ __('Too Many Requests') }}</p>
            </div>
        </div>
    </div>
@endsection
