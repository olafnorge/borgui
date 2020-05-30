@extends('layouts.app')

@section('title', __('Unauthorized'))
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="clearfix">
                <h1 class="float-left display-3 mr-4">401</h1>
                <h4 class="pt-3">Oops! You're unauthorized.</h4>
                <p class="text-muted">{{ __('Unauthorized') }}</p>
            </div>
        </div>
    </div>
@endsection
