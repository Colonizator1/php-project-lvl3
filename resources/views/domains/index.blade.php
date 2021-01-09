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
                <td><a href="{{route('domains.show', ['id' => $domain->id])}}">{{$domain->name}}</a></td>
                <td>
                    @if ($domain->last_domain_check !== null)
                        Date: {{$domain->last_domain_check}}
                    @endif
                </td>
                <td>
                    @if ($domain->status_code !== null)
                        Status: {{$domain->status_code}}
                    @endif
                </td>
                <td>
                    <i class="far fa-trash-alt"></i>
                    <a href="{{route('domains.destroy', ['id' => $domain->id])}}" data-confirm="Вы уверены?" data-method="delete" rel="nofollow">Удалить</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection