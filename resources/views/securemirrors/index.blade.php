@extends('adminlte::page')

@section('title', 'Mirrors')

@section('content_header')
    <h1>Mirrors</h1>
@stop

@section('content')
<div class="container my-5">
    {{-- <h1 class="mb-4">Secure Mirrors</h1> --}}
    <a href="{{ route('securemirrors.create') }}" class="btn btn-success mb-4">Create New Secure Mirror</a>
    <hr>
    <ul class="list-group">
        @foreach ($securemirrors as $securemirror)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>{{ $securemirror->title }}</strong> 
                    <span class="text-muted">({{ $securemirror->domain }})</span>
                </div>
                <div>
                    {{-- <a href="{{ route('securemirrors.show', $securemirror->id) }}" class="btn btn-primary btn-sm me-2">View</a> --}}
                    {{-- <a href="{{ route('securemirrors.edit', $securemirror->id) }}" class="btn btn-warning btn-sm me-2">Edit</a> --}}
                    <form action="{{ route('securemirrors.destroy', $securemirror->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </div>
            </li>
        @endforeach
    </ul>
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