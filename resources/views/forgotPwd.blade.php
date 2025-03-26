<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Forgot Password</title>
        <link rel="stylesheet" type="text/css" href="{{ asset('css/forgotPwd.css') }}">
    </head>
    <body>
        <div class="container">
            <img src="{{ asset('images/AlloymontLogo.png') }}" alt="AlloyMont Logo" class="logo"/>
            <!-- Left side: Form content -->
            <div class="form-container">
                <h1 class="title"><b>Forgot </b>Your Password?</h1>
                <p class="subtitle">Don't Worry! We'll email you to reset your password</p>

                <!-- Success Message -->
                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                <form action="{{ route('password.email') }}" method="POST" style="width: 100%;">
                    @csrf
                    <input type="email" name="email" placeholder="Email address" required />
                    @error('email')
                    <div class="error-message">{{ $message }}</div>
                    @enderror
                    <button type="submit" class="continue">Send Password Reset Link</button>
                </form>

                <p class="login-link">
                    Back to <a href="{{ route('login') }}">Sign In</a>
                </p>
            </div>

            <!-- Right Side: Image -->
            <div class="image-container">
                <img src="{{ asset('images/Alloymont3.png') }}" alt="Alloymont Illustration" />
            </div>
        </div>

        <script>
            // Automatically hide the success message after 5 seconds
            setTimeout(function() {
                const alert = document.querySelector('.alert');
                if (alert) {
                    alert.style.display = 'none';
                }
            }, 5000); // 5000 milliseconds = 5 seconds
        </script>
    </body>
</html>