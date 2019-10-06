@extends('layouts.app')

@section('content')
    <channels-table :items='@json($channels)'
                    create-action="{{ route('channel.create') }}">

    </channels-table>
@endsection