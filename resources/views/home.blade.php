@extends('layouts.app')
@section('title', 'Page Analyzer')

@section('content')
<!-- Masthead -->
<header class="masthead text-white text-center">
    <div class="overlay"></div>
    <div class="container">
    <div class="row">
        <div class="col-xl-9 mx-auto">
            <h1 class="mb-5">Page Analyzer!</h1>
            <p>Chek web-pages for free</p>
        </div>
        <div class="col-md-10 col-lg-8 col-xl-7 mx-auto">
        {{ Form::open(['url' => route('domains.store')]) }}
            <div class="form-row">
                <div class="col-12 col-md-9 mb-2 mb-md-0">
                    {{Form::text('domain[name]', 'example.com', ['class' => 'form-control form-control-lg'])}}
                </div>
                <div class="col-12 col-md-3">
                    {{Form::submit('Check', ['class' => 'btn btn-block btn-lg btn-primary'])}}
                </div>
            </div>
        {{ Form::close() }}
        </div>
    </div>
    </div>
</header>

@endsection