@extends('layouts.app')

@section('title', __('Forbidden'))
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="clearfix">
                <h1 class="float-left display-3 mr-4">403</h1>
                <h4 class="pt-3">Oops! It's forbidden to visit this page.</h4>
                <p class="text-muted">{{ __($exception->getMessage() ?: 'Forbidden') }}</p>
            </div>
        </div>
    </div>
@endsection
