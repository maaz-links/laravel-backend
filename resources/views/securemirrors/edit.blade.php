@extends('adminlte::page')

@section('title', 'Mirrors')

@section('content_header')
    <h1>Edit Secure Mirror</h1>
@stop

@section('content')
<div class="container my-5">
    @foreach ($errors->all() as $error)
            <div class="mt-3 alert alert-danger"><li>{{ $error }}</li>
            </div>
        @endforeach
    <form action="{{ route('securemirrors.update', $securemirror->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="title" class="form-label">Title:</label>
            <input type="text" name="title" id="title" value="{{ $securemirror->title }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="domain" class="form-label">Domain:</label>
            <input type="text" name="domain" id="domain"  value="{{ $securemirror->domain }}" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Save</button>
        <a class="btn btn-success" href="{{ route('securemirrors.index') }}">Back to List</a>
    </form>
</div>
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
