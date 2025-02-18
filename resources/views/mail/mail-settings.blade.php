@extends('adminlte::page')

@section('title', 'Mail Settings')

@section('content_header')
    <h1>Edit Mail Settings</h1>
@stop

@section('content')
<div class="container my-5">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                
                <form method="POST" action="{{ route('mail-settings.update') }}">
                    @csrf  
                    
                    <div class="mb-3">
                        <label class="form-label">Mailer</label>
                        <input type="text" name="mail_mailer" class="form-control" value="{{ $settings->mail_mailer ?? '' }}" required>
                    </div>
                
                    <div class="mb-3">
                        <label class="form-label">Host</label>
                        <input type="text" name="mail_host" class="form-control" value="{{ $settings->mail_host ?? '' }}" required>
                    </div>
                
                    <div class="mb-3">
                        <label class="form-label">Port</label>
                        <input type="number" name="mail_port" class="form-control" value="{{ $settings->mail_port ?? '' }}" required>
                    </div>
                
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="mail_username" class="form-control" value="{{ $settings->mail_username ?? '' }}">
                    </div>
                
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="text" name="mail_password" class="form-control" value="{{ $settings->mail_password ?? '' }}">
                    </div>
                
                    <div class="mb-3">
                        <label class="form-label">Encryption</label>
                        <input type="text" name="mail_encryption" class="form-control" value="{{ $settings->mail_encryption ?? '' }}">
                    </div>
                
                    <div class="mb-3">
                        <label class="form-label">From Address</label>
                        <input type="email" name="mail_from_address" class="form-control" value="{{ $settings->mail_from_address ?? '' }}" required>
                    </div>
                
                    <div class="mb-3">
                        <label class="form-label">From Name</label>
                        <input type="text" name="mail_from_name" class="form-control" value="{{ $settings->mail_from_name ?? '' }}" required>
                    </div>
                
                    <button type="submit" class="btn btn-primary">Save Settings</button>
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