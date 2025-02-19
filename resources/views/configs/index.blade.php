@extends('adminlte::page')

@section('title', 'Configurations')

@section('content_header')
    <h1>Configurations</h1>
@stop

@section('content')
<div class="container my-5">
    <a href="{{ route('configs.create') }}" class="btn btn-success">Create New Configuration</a>
    
    @if(session('success'))
        <div class="mt-3 alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table mt-3">
        <thead>
            <tr>
                <th style='width: 20%'>Key</th>
                <th style='width: 65%'>Value</th>
                <th style='width: 15%'>Actions</th>
            </tr>
        </thead>
        <tbody style="height=300px;overflow-y:auto;">
            @foreach($configs as $config)
            <tr>
                <td>{{ $config->key }}</td>
                <td>{{ $config->value }}</td>
                <td>
                    <a href="{{ route('configs.edit', $config) }}" class="btn btn-warning">Edit</a>
                    <form action="{{ route('configs.destroy', $config) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger" 
                        {{-- onclick="return confirm('Are you sure?')" --}}
                        >Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
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