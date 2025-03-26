<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Reset Password</title>
        <link rel="stylesheet" type="text/css" href="{{ asset('css/forgotPwd.css') }}">
    </head>
    <body>
        <div class="container">
            <img src="{{ asset('images/AlloymontLogo.png') }}" alt="AlloyMont Logo" class="logo"/>
            <!-- Left side: Form content -->
            <div class="form-container">
                <h1 class="title">Let's <b>Reset </b>Your Password</h1>

                <form action="{{ route('password.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">
                    <input type="password" name="password" placeholder="Enter new password" required>
                    <input type="password" name="password_confirmation" placeholder="Confirm new password" required>
                    <button type="submit" class="continue">Reset Password</button>
                </form>


            </div>

            <!-- Right Side: Image -->
            <div class="image-container">
                <img src="{{ asset('images/Alloymont3.png') }}" alt="Alloymont Illustration" />
            </div>
        </div>
    </body>
</html>
