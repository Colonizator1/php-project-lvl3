@extends('layouts.app')

@section('title', 'Domain {{$domain->id}}')

@section('content')
<ul>
    <li>id: {{$domain->id}}</li>
    <li>name: {{$domain->name}}</li>
    <li>create: {{$domain->created_at}}</li>
    <li>update: {{$domain->updated_at}}</li>
</ul>
{{ Form::open(['url' => route('domains_check.store', $domain->id)]) }}
    <div class="form-row">
        <div class="col-12 col-md-3">
            {{Form::submit('Run check', ['class' => 'btn btn-block btn-lg btn-primary'])}}
        </div>
    </div>
{{ Form::close() }}
@if (isset($domainChecks) && count($domainChecks) > 0)
    <ul class="list-group">
        @foreach($domainChecks as $check)
            <li>id: {{$check->id}}</li>
            <li>create: {{$check->created_at}}</li>
            <li>update: {{$check->updated_at}}</li>
        @endforeach
    </ul>
@else
    <p>Wed didn't checked this domain</p>
@endif
@endsection