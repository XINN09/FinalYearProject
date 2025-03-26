<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Create Account - Contractor</title>
        <link rel="stylesheet" type="text/css" href="{{ asset('css/register.css') }}">
    </head>
    <body>
        <div class="container">
            <img src="{{ asset('images/AlloymontLogo.png') }}" alt="AlloyMont Logo" class="logo"/>

            <!-- Left side Form content -->
            <div class="form-container">
                <h1>Contractor Info</h1>

                <!-- Form to register contractor -->
                <form method="POST" action="{{ route('registerContractor') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token ?? '' }}">
                    <div class="scroll-box">
                        <div class="form-field">
                            <h4>Company Name</h4>
                            <input type="text" name="companyName" placeholder="Enter your company name" value="{{ old('companyName') }}" required />
                            @error('companyName')
                            <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-field">
                            <h4>Business Address</h4>
                            <input type="text" name="businessAddress" placeholder="Enter your business address" value="{{ old('businessAddress') }}" required />
                            @error('businessAddress')
                            <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-field">
                            <h4>Register No/License No</h4>
                            <input type="text" name="registerNo" placeholder="Enter your registration number" value="{{ old('registerNo') }}" required />
                            @error('registerNo')
                            <div class="error-message">{{ $message }}</div>
                            @enderror
                            <div class="format-note">
                                <p><strong>Note:</strong> The registration number must be new format:</p>
                                <ul>
                                    <li>Be <strong>12 digits</strong> long.</li>
                                    <li>Start with <strong>20</strong>XXXXXXXXXX</li>
                                </ul>
                                <p><strong>Examples:</strong> <code>201234567890</code>, <code>209876543210</code></p>
                            </div>
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
