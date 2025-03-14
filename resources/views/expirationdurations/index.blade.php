@extends('adminlte::page')

@section('title', 'Expiration Duration')

@section('content_header')
    <h1>Expiration Duration</h1>
@stop
@section('content')
<div class="container my-5">
    <div class='row'>
        <div class='col-6'>
            <a class="btn btn-success mb-4" href="{{ route('expirationdurations.create') }}">Create New Expiration
                Duration</a>
        </div>
        <div class='col-6'>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>Unlimited Duration:</strong>
                    @if ($unlimitstatus)
                        <span class="text-primary">Enabled</span>
                    @else
                        <span class="text-secondary">Disabled</span>
                    @endif
                </div>
                <div>
                    {{-- <a class="btn btn-warning btn-sm me-2">Edit</a> --}}
                    <form action="{{ route('expirationdurations.unlimited') }}" method="POST" class="d-inline">
                        @csrf
                        @method('POST')
                        @if ($unlimitstatus)
                            <button type="submit" class="btn btn-primary btn-sm">Disable</button>
                        @else
                            <button type="submit" class="btn btn-primary btn-sm">Enable</button>
                        @endif
                    </form>
                </div>
            </li>
        </div>
    </div>
    <hr>
    <ul class="list-group">
        @foreach($expirationDurations as $expirationDuration)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <strong>{{ $expirationDuration->title }}</strong> 
                    <span class="text-muted">| {{ $expirationDuration->duration }} minutes</span>
                </div>
                <div>
                    <a href="{{ route('expirationdurations.edit', $expirationDuration) }}" class="btn btn-warning btn-sm me-2">Edit</a>
                    <form action="{{ route('expirationdurations.destroy', $expirationDuration) }}" method="POST" class="d-inline">
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