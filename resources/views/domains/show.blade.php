@extends('layouts.app')

@section('title', 'Domain {{$domain->id}}')

@section('content')
<ul>
    <li>id: {{$domain->id}}</li>
    <li>name: {{$domain->name}}</li>
    <li>create: {{$domain->created_at}}</li>
    <li>update: {{$domain->updated_at}}</li>
</ul>
@endsection