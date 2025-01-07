<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API File Upload</title>
</head>

<body>
    <h1>Upload a File</h1>
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('fileupload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('post')
        <label>Name</label>
        <label>Upload File</label>
        <input type="file" name="filesupload[]" multiple><br>
        <button type="submit">SUBMIT</button>
    </form>
</body>

</html>
