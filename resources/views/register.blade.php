<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Create Account</title>
        <link rel="stylesheet" type="text/css" href="{{ asset('css/register.css') }}">
    </head>
    <body>
        <div class="container">
            <img src="{{ asset('images/AlloymontLogo.png') }}" alt="AlloyMont Logo" class="logo"/>

            <!-- Left side Form content -->
            <div class="form-container">
                <h1>Create your account</h1>

                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token ?? '' }}">
                    <div class="scroll-box">
                        <h4>Full name</h4>
                        <input type="text" name="full_name" placeholder="Enter your full name" value="{{ old('full_name') }}" required />
                        @error('full_name')
                        <div class="error-message">{{ $message }}</div>
                        @enderror

                        <h4>Email</h4>
                        <input type="email" name="email" placeholder="Enter your email address" value="{{ old('email') }}" required />
                        @error('email')
                        <div class="error-message">{{ $message }}</div>
                        @enderror

                        <h4>Phone Number</h4>
                        <input type="number" name="phone" placeholder="Enter your phone number" value="{{ old('phone') }}" required />
                        @error('phone')
                        <div class="error-message">{{ $message }}</div>
                        @enderror

                        <h4>Password</h4>
                        <input type="password" name="password" placeholder="Enter your password" required />
                        @error('password')
                        <div class="error-message">{{ $message }}</div>
                        @enderror

                        <h4>Re-enter Password</h4>
                        <input type="password" name="password_confirmation" placeholder="Re-enter your password" required />
                        @error('password_confirmation')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                        
                        <div class="password-note">
                            <p style="font-weight: bold;">Password must be at least 8 characters long and include:</p>
                            <ul>
                                <li>At least one uppercase letter (A-Z)</li>
                                <li>At least one lowercase letter (a-z)</li>
                                <li>At least one digit (0-9)</li>
                                <li>At least one special character (@$!%*?&)</li>
                            </ul>
                        </div>
                    </div>

                    <div class="button-group">
                        <a href="{{ route('login', ['token' => session('pending_invite')]) }}" class="back-button">
                            &lt; Back to Sign In
                        </a>

                        <button type="submit" class="continue">Continue &gt;</button>
                    </div>
                </form>

                <p class="terms">
                    By proceeding, you agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                </p>
            </div>

            <!-- Right Side Image -->
            <div class="image-container">
                <img src="{{ asset('images/Alloymont2.png') }}" alt="Alloymont Illustration" />
            </div>
        </div>
    </body>
</html>
