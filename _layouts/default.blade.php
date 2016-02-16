@extends('_layouts.master')

@section('body')

    <div class="ui top fixed borderless main menu">
        <div class="ui text container">
            <div class="header item">
                steak
                <img class="logo" src="assets/logo.png">
            </div>
            <a href="." class="item">Overview</a>
            <a href="quick-start.html" class="item">Quickstart</a>
            <a href="configuration.html" class="item">Configuration</a>
            <a href="extending-steak.html" class="item">Extending</a>
        </div>
    </div>

    <main>
        <div class="ui text container">
            @yield('content')
        </div>
    </main>


@stop
