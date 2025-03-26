<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Create Account - Role Selection</title>
        <link rel="stylesheet" type="text/css" href="{{ asset('css/register.css') }}">
    </head>
    <body>
        <div class="container">
            <img src="{{ asset('images/AlloymontLogo.png') }}" alt="AlloyMont Logo" class="logo"/>

            <!-- Left side Form content -->
            <div class="form-container">


                <form method="POST" action="{{ route('role') }}">
                    <h2>What best describes your current role?</h2>
                    @csrf

                    <input type="hidden" name="token" value="{{ $token ?? '' }}">
                    <!-- Display error messages if any -->
                    @if ($errors->any())
                    <div class="error-message">
                        @foreach ($errors->all() as $error)
                        {{ $error }}</li>
                        @endforeach
                    </div>
                    @endif

                    <div class="radio-buttons">
                        <label>
                            <input type="radio" name="user_type" value="c" {{ old('user_type') == 'c' ? 'checked' : '' }} />
                            Contractor
                        </label>
                        <label>
                            <input type="radio" name="user_type" value="h" {{ old('user_type') == 'h' ? 'checked' : '' }} />
                            Homeowner
                        </label>
                        <label>
                            <input type="radio" name="user_type" value="w" {{ old('user_type') == 'w' ? 'checked' : '' }} />
                            Worker
                        </label>
                    </div>
                    <button type="submit" class="continue2">Continue &gt;</button>
                </form>
            </div>

            <!-- Right Side Image -->
            <div class="image-container">
                <img src="{{ asset('images/Alloymont2.png') }}" alt="Alloymont Illustration" />
            </div>
        </div>
    </body>
</html>
