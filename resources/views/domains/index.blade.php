@extends('layouts.app')

@section('title', 'All domains')

@section('content')
<div class="container-lg">
<h1 class="mt-5 mb-3">Domains</h1>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Url</th>
                <th>Last check</th>
                <th>Status Code</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @foreach($domains as $domain)
            <tr>
                <td>{{$domain->id}}</td>
                <td><a href="{{route('domains.show', ['domain' => $domain->id])}}">{{$domain->name}}</a></td>
                <td>
                    {{optional($domain->last_check)->created_at}}
                </td>
                <td>
                    {{optional($domain->last_check)->status_code}}
                </td>
                <td>
                    <i class="far fa-trash-alt"></i>
                    <a href="{{route('domains.destroy', ['domain' => $domain->id])}}" data-confirm="Are you sure?" data-method="delete" rel="nofollow">Delete</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection