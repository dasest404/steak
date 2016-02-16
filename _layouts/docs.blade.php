@extends('_layouts.master')

@section('body')

    <div class="ui top fixed large borderless menu">
        <div class="ui container">
            @include('_partials.menu')
        </div>
    </div>

    <main class="docs content">
        <div class="ui text container">
            @yield('content')
        </div>
    </main>

@stop
