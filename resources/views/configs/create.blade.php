@extends('adminlte::page')

@section('title', 'Configurations')

@section('content_header')
    <h1>Create New Configuration</h1>
@stop

@section('content')

    <div class="container my-5">
        {{-- <h2>Create Mail Config</h2> --}}
        @foreach ($errors->all() as $error)
            <div class="mt-3 alert alert-danger"><li>{{ $error }}</li>
            </div>
        @endforeach
        <form action="{{ route('configs.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>Key</label>
                <input type="text" name="key" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Value</label>
                <textarea name="value" class="form-control" rows="6" required></textarea>
            </div>
            <button type="submit" class="btn btn-success">Save</button>
            <a class="btn btn-success" href="{{ route('configs.index') }}">Back to List</a>
        </form>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.min.js"></script>
    <script>
        console.log("Hi, I'm using the Laravel-AdminLTE package!");
    </script>
    <script src="https://code.jquery.com/jquery-3.7.1.slim.js"
        integrity="sha256-UgvvN8vBkgO0luPSUl2s8TIlOSYRoGFAX4jlCIm9Adc=" crossorigin="anonymous"></script>
@stop
