@extends('layouts.app')

@section('title', __('Page Expired'))
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="clearfix">
                <h1 class="float-left display-3 mr-4">419</h1>
                <h4 class="pt-3">Oops! The page is expired.</h4>
                <p class="text-muted">{{ __('Page Expired') }}</p>
            </div>
        </div>
    </div>
@endsection
