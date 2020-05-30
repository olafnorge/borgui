<!DOCTYPE html>
<!--
* CoreUI - Free Bootstrap Admin Template
* @version v2.1.12
* @link https://coreui.io
* Copyright (c) 2018 creativeLabs Łukasz Holeczek
* Licensed under MIT (https://coreui.io/license)
-->

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <base href="./">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="UI for borg">
    <meta name="author" content="Volker Machon">
    <meta name="keyword" content="borg,backup,ui">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        @hasSection('tile')
            @yield('title')
        @else
            {{ request()->route() && Breadcrumbs::exists(request()->route()->getName()) && Breadcrumbs::current()
                    ? Breadcrumbs::current()->title
                    : config('app.name') }}
        @endif
    </title>

    {{ Html::style(mix('css/app.css')) }}
    @stack('css')
</head>
<body class="app {{ Auth::check() ? 'header-fixed sidebar-fixed aside-menu-fixed sidebar-lg-show' : 'flex-row align-items-center' }}">

@includeWhen(Auth::check(), 'layouts.components.head_navbar')

<div class="app-body">
    @includeWhen(Auth::check(), 'layouts.components.side_navbar')

    <main class="main">
        @if(Auth::check())
            @if(Breadcrumbs::exists(request()->route()->getName()))
                {{ Breadcrumbs::render() }}
            @else
                {{ Breadcrumbs::render('fallback') }}
            @endif
        @endif

        <div class="container-fluid">
            <div class="animated fadeIn">
                @includeWhen(session('error'), 'components.flash', ['type' => 'danger', 'message' => session('error')])
                @includeWhen(session('warning'), 'components.flash', ['type' => 'warning', 'message' => session('warning')])
                @includeWhen(session('success'), 'components.flash', ['type' => 'success', 'message' => session('success')])
                @includeWhen(session('primary'), 'components.flash', ['type' => 'primary', 'message' => session('primary')])
                @includeWhen(session('resent'), 'components.flash', ['type' => 'success', 'message' => __('A fresh verification link has been sent to your email address.')])
                @yield('content')
            </div>
        </div>
    </main>

</div>

@includeWhen(Auth::check(), 'layouts.components.footer')
@includeWhen(Auth::check(), 'layouts.components.loading_modal')

{{ Html::script(mix('js/app.js')) }}
@stack('js')
</body>
</html>
