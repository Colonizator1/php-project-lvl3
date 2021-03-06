@extends('layouts.app')

@section('title', 'All urls')

@section('content')
<div class="container-lg">
<h1 class="mt-5 mb-3">Urls</h1>
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
        @foreach($urls as $url)
            <tr>
                <td>{{$url->id}}</td>
                <td><a href="{{route('urls.show', ['url' => $url->id])}}">{{$url->name}}</a></td>
                <td>
                    {{optional($url->last_check)->created_at}}
                </td>
                <td>
                    {{optional($url->last_check)->status_code}}
                </td>
                <td>
                    <i class="far fa-trash-alt"></i>
                    <a href="{{route('urls.destroy', ['url' => $url->id])}}" data-confirm="Are you sure?" data-method="delete" rel="nofollow">Delete</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection