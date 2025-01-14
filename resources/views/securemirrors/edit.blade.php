<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Secure Mirror</title>
</head>
<body>
    <h1>Edit Secure Mirror</h1>
    <form action="{{ route('securemirrors.update', $securemirror->id) }}" method="POST">
        @csrf
        @method('PUT')
        <label for="title">Title:</label>
        <input type="text" name="title" value="{{ $securemirror->title }}" required><br>
        <label for="domain">Domain:</label>
        <input type="text" name="domain" value="{{ $securemirror->domain }}" required><br>
        <button type="submit">Update</button>
    </form>
</body>
</html>
