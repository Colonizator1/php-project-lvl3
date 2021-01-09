@extends('layouts.app')

@section('title')
    Url {{$domain->name}}
@endsection

@section('content')
<div class="container-lg">
    <h1 class="mt-5 mb-3">Url: {{$domain->name}}</h1>
    <table class="table table-hover">
        <tr>
            <td>id</td><td>{{$domain->id}}</td>
        </tr>
        <tr>
            <td>url</td><td>{{$domain->name}}</td>
        </tr>
        <tr>
            <td>created_at</td><td>{{$domain->created_at}}</td>
        </tr>
        <tr>
            <td>updated_at</td><td>{{$domain->updated_at}}</td>
        </tr>
    </table>
    <h2 class="mt-5 mb-3">Checks</h2>
    {{ Form::open(['url' => route('domains_check.store', $domain->id)]) }}
        <div class="form-row">
            <div class="col-12 col-md-3">
                {{Form::hidden('domainName', $domain->name)}}
                {{Form::submit('Run check', ['class' => 'btn btn-block btn-lg btn-primary'])}}
            </div>
        </div>
    {{ Form::close() }}
    @if (isset($domainChecks) && count($domainChecks) > 0)
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Status Code</th>
                    <th>h1</th>
                    <th>Keywords</th>
                    <th>Description</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                </tr>
            </thead>
            <tbody>
            @foreach($domainChecks as $check)
                <tr>
                    <td>{{$check->id}}</td>
                    @switch($check->status)
                        @case('pending')
                            <td colspan="4" class='alert alert-warning'>Waiting for checking site...</td>
                            @break
                        @case('failed')
                            <td colspan="4" class='alert alert-danger'>Check failed...</td>
                            @break
                        @default
                            <td>{{$check->status_code}}</td>
                            <td>{{$check->h1}}</td>
                            <td>{{$check->keywords}}</td>
                            <td>{{$check->description}}</td>
                    @endswitch
                    <td>{{$check->created_at}}</td>
                    <td>{{$check->updated_at}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p>Wed didn't checked this site</p>
    @endif
</div>
@endsection