<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Create Account - Worker</title>
        <link rel="stylesheet" type="text/css" href="{{ asset('css/register.css') }}">
    </head>
    <body>
        <div class="container">
            <img src="{{ asset('images/AlloymontLogo.png') }}" alt="AlloyMont Logo" class="logo"/>

            <!-- Left side Form content -->
            <div class="form-container">
                <h1>Worker Info</h1>

                <!-- Form to register worker -->
                <form method="POST" action="{{ route('registerWorker') }}">
                    @csrf

                    <!-- Availability Status Selection -->
                    <div class="form-field" style="margin-bottom: 40px;">
                        <h4 style="font-size: 18px;">Availability Status</h4>
                        <div class="radio-buttons">
                            <label>
                                <input type="radio" name="availabilityStatus" value="available" required /> Available
                            </label>
                            <label>
                                <input type="radio" name="availabilityStatus" value="on_leave" required /> On Leave
                            </label>
                            <label>
                                <input type="radio" name="availabilityStatus" value="unavailable" required /> Unavailable
                            </label>
                            @error('availabilityStatus')
                            <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Worker Type Selection -->
                    <div class="form-field">
                        <h4 style="font-size: 18px;">Worker Type</h4>
                        <div class="radio-buttons">
                            <label>
                                <input type="radio" name="workerType" value="full_time" required /> Full-time
                            </label>
                            <label>
                                <input type="radio" name="workerType" value="part_time" required /> Part-time
                            </label>
                            <label>
                                <input type="radio" name="workerType" value="freelance" required /> Freelance
                            </label>
                            @error('workerType')
                            <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
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
