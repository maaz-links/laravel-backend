@extends('adminlte::page')

@section('title', 'Configurations')

@section('content_header')
    <h1>Configurations</h1>
@stop

@section('content')
    <div class="container my-5">
        <h2>Edit Mail Config</h2>
        @foreach ($errors->all() as $error)
            <div class="mt-3 alert alert-danger">{{ session('success') }}<li>{{ $error }}</li>
            </div>
        @endforeach
        <form action="{{ route('configs.update', $mail_config) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3">
                <label>Key</label>
                <input type="text" name="key" class="form-control" value="{{ $mail_config->key }}" required>
            </div>
            <div class="mb-3">
                <label>Value</label>
                {{-- <input type="text" name="value" class="form-control" value="{{ $mail_config->value }}" required> --}}
                <textarea name="value" class="form-control" rows="6" required>{{ $mail_config->value }}</textarea>
            </div>
            <button type="submit" class="btn btn-success">Update</button>
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
