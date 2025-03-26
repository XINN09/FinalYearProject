<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Create Account - Homeowner</title>
        <link rel="stylesheet" type="text/css" href="{{ asset('css/register.css') }}">
    </head>
    <body>
        <div class="container">
            <img src="{{ asset('images/AlloymontLogo.png') }}" alt="AlloyMont Logo" class="logo"/>

            <!-- Left side Form content -->
            <div class="form-container">
                <h1>Homeowner Info</h1>

                <!-- Form to register contractor -->
                <form method="POST" action="{{ route('registerOwner') }}">
                    @csrf
                    <div class="form-field">
                        <h4>Home Address</h4>
                        <input type="text" name="homeAddress" placeholder="Enter your home address" value="{{ old('homeAddress') }}" required />
                        @error('homeAddress')
                        <div class="error-message">{{ $message }}</div>
                        @enderror

                    </div>

                    <!-- Buttons: Back and Sign Up -->
                    <div class="button-group">
                        <a href="{{ route('role') }}" class="back-button">&lt; Back</a>  
                        <button type="submit" class="continue">Sign Up &gt;</button>
                    </div>
                </form>
            </div>

            <!-- Right Side Image -->
            <div class="image-container">
                <img src="{{ asset('images/Alloymont2.png') }}" alt="Alloymont Illustration" />
            </div>
        </div>
    </body>
</html>
