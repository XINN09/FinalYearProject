<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alloymont Logout</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/logout.css') }}">
</head>
<body>
    <div class="logout-container">
        <h1>You have been logged out</h1>
        <p>Thank you for using our service. See you again soon!</p>
        <a href="{{ route('login') }}" class="button">Login Again</a>
    </div>
</body>
</html>
