<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Alloymont Login</title>
        <link rel="stylesheet" type="text/css" href="{{ asset('css/login.css') }}">
    </head>
    <body>
        <div class="container">
            <img src="{{ asset('images/AlloymontLogo.png') }}" alt="AlloyMont Logo" class="logo"/>
            <!-- Left side: Form content -->
            <div class="form-container">
                <h1>Sign In</h1>
                <p class="subtitle">to access Alloymont Workspace</p>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Input -->
                    <div class="input-container">
                        <input type="email" name="email" placeholder="Email address" value="{{ old('email') }}" />
                        @error('email')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password Input -->
                    <div class="input-container">
                        <input type="password" name="password" placeholder="Password" />
                        @error('password')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="continue">Sign In</button>
                </form>

                    

                <p class="login-link">
                    Don't have an account yet? <a href="{{ route('register') }}">Sign Up</a>
                </p>

                <p class="reset-link">
                    <a href="{{ route('password.request') }}">Forgot your password?</a>
                </p>
            </div>

            <!-- Right Side: Image -->
            <div class="image-container">
                <img src="{{ asset('images/AlloymontBg.png') }}" alt="Alloymont Illustration" />
            </div>
        </div>
    </body>
</html>
