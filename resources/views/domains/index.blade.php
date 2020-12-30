@extends('layouts.app')

@section('title', 'All domains')

@section('content')
<ul class="list-group">
    @foreach($domains as $domain)
        <li class="list-group-item">
            <a href="{{route('domains.show', ['id' => $domain->id])}}">{{$domain->name}}</a>
            <a href="{{route('domains.destroy', ['id' => $domain->id])}}" data-confirm="Вы уверены?" data-method="delete" rel="nofollow">Удалить</a>
        </li>
    @endforeach
</ul>
@endsection