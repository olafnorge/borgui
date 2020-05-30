@extends('layouts.app')

@section('title', __('Service Unavailable'))
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="clearfix">
                <h1 class="float-left display-3 mr-4">503</h1>
                <h4 class="pt-3">Houston, we have a problem!</h4>
                <p class="text-muted">{{ __($exception->getMessage() ?: 'Service Unavailable') }}</p>
            </div>
        </div>
    </div>
@endsection
