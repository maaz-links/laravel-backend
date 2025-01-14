<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Show Secure Mirror</title>
</head>
<body>
    <h1>{{ $securemirror->title }}</h1>
    <p>Domain: {{ $securemirror->domain }}</p>
    <a href="{{ route('securemirrors.index') }}">Back to List</a>
</body>
</html>
