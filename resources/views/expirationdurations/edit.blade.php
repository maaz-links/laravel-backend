@extends('adminlte::page')

@section('title', 'ExpirationDuration')

@section('content_header')
    <h1>Expiration Duration</h1>
@stop

@section('content')
<form action="{{ route('expirationdurations.update', $expirationDuration->id) }}" method="POST">
    @csrf
    @method('PUT')
    <label for="title">Title</label>
    <input type="text" name="title" id="title" value="{{ old('title', $expirationDuration->title) }}" required>

    <label for="duration">Duration (minutes)</label>
    <input type="number" name="duration" id="duration" value="{{ old('duration', $expirationDuration->duration) }}" required min="1">

    <button type="submit">Update Expiration Duration</button>
</form>
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.min.js"></script>
    <script> console.log("Hi, I'm using the Laravel-AdminLTE package!"); </script>
    <script
			  src="https://code.jquery.com/jquery-3.7.1.slim.js"
			  integrity="sha256-UgvvN8vBkgO0luPSUl2s8TIlOSYRoGFAX4jlCIm9Adc="
			  crossorigin="anonymous"></script>
@stop