<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Profile</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('css/userProfile.css') }}">
        <style>
            .hidden {
                display: none;
            }

            .error-message {
                color: red;
                font-size: 12px;
                display: none;
            }
        </style>
    </head>
    <body>
        <aside class="sidebar">
            @include('generalComponent.userNav')
        </aside>

        <div class="main-content">
            <h2 class="title">Profile</h2>

            <div class="profile-card">
                <div class="profile-header">
                    @if($user->profilePicture)
                    <img src="{{ asset('storage/' . $user->profilePicture) }}" alt="User Avatar" class="user_icon" />
                    @else
                    <div class="user-avatar" id="userAvatar">
                        {{ strtoupper(substr($user->userName, 0, 1)) }}
                    </div>
                    @endif
                    <div>
                        <h3 class="header">{{ $user->userName }}</h3> 
                        <p class="header user-role">{{ ucfirst($role) }}</p>
                    </div>
                    <button class="editButton">Edit</button>
                </div>

                <div class="profile-details">
                    <div class="details-row">
                        <p><strong>Full Name:</strong><br> {{ $user->userName }}</p>
                        <p><strong>Gender:</strong><br>
                            <span id="genderText">{{ $user->userGender ?? '-' }}</span>
                            <select id="genderSelect" class="custom-select hidden">
                                <option value="Female" {{ $user->userGender == 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Male" {{ $user->userGender == 'Male' ? 'selected' : '' }}>Male</option>
                            </select>
                        </p>
                    </div>
                    <div class="details-row" style="padding-bottom: 30px; border-bottom: 1px solid #aeaeae">
                        <p><strong>Country/Region:</strong><br> {{ $user->country }}</p>
                        <p><strong>Language:</strong><br> {{ $user->language ?? 'Not Provided' }}</p>
                    </div>
                    <div class="details-row">
                        <p><strong>Email:</strong> <br> <span id="emailAdd">{{ $user->email }}</span></p>
                        <span class="lock-icon">🔒</span>
                        <p><strong>Phone Number:</strong><br><span id="phoneNum" contenteditable="true" class="editable-phone">{{ $user->userPhone ?? 'Not Provided' }}</span></p>
                        <button class="editPhoneButton">Edit Phone Number</button>
                    </div>
                </div>
            </div>

            <div class="profile-card role-card">
                @if($role == 'Contractor')
                <div class="role-header">
                    <h3>Role Information</h3>

                    <button class="editRoleButton">Edit</button>
                </div>
                <form id="contractorForm" class="hidden">
                    @csrf
                    <div class="form-group">
                        <label for="companyName">Company Name:</label>
                        <input type="text" id="companyName" name="companyName" value="{{ $user->contractor->companyName ?? 'N/A' }}">
                    </div>
                    <div class="form-group">
                        <label for="businessAddress">Business Address:</label>
                        <input type="text" id="businessAddress" name="businessAddress" value="{{ $user->contractor->businessAddress ?? 'N/A' }}">
                    </div>
                    <div class="form-group">
                        <label for="registerNo">Register No:</label>
                        <input type="text" id="registerNo" name="registerNo" value="{{ $user->contractor->registerNo ?? 'N/A' }}">
                    </div>
                    <div class="form-group">
                        <label for="companyLogo">Company Logo:</label>
                        <input type="file" id="companyLogo" name="company_logo">
                    </div>
                    <button type="submit" class="saveRoleButton">Save</button>
                </form>
                <div id="contractorDetails">
                    <p><strong>Company Name:</strong>{{ $user->contractor->companyName ?? 'N/A' }}</p>
                    <p><strong>Business Address:</strong> {{ $user->contractor->businessAddress ?? 'N/A' }}</p>
                    <p><strong>Register No:</strong> {{ $user->contractor->registerNo ?? 'N/A' }}</p>
                </div>
                <p><strong>Company Logo:</strong></p>
                @if($user->contractor->companyLogo)
                <img src="{{ asset('storage/' . $user->contractor->companyLogo) }}" alt="Company Logo" class="company-logo" />
                @else
                <form id="companyLogoForm" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="company_logo" id="company_logo" required>
                    <button type="submit">Upload Logo</button>
                </form>
                @endif
                @elseif($role == 'Worker')
                <div class="role-header">
                    <h3>Role Information</h3>
                    <button class="editRoleButton">Edit</button>
                </div>
                <form id="workerForm" class="hidden">
                    @csrf
                    <div class="form-group">
                        <label>Worker Type:</label>
                        <div style="display: flex; margin-top: 10px;">
                            <label><input type="radio" name="workerType" value="Full-time" {{ $user->worker->workerType == 'Full-time' ? 'checked' : '' }}> Full-time</label>
                            <label><input type="radio" name="workerType" value="Part-time" {{ $user->worker->workerType == 'Part-time' ? 'checked' : '' }}> Part-time</label>
                            <label><input type="radio" name="workerType" value="Freelance" {{ $user->worker->workerType == 'Freelance' ? 'checked' : '' }}> Freelance</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Availability Status:</label>
                        <div style="display: flex; margin-top: 10px;">
                            <label><input type="radio" name="availabilityStatus" value="Available" {{ $user->worker->availabilityStatus == 'Available' ? 'checked' : '' }}> Available</label>
                            <label><input type="radio" name="availabilityStatus" value="On-leave" {{ $user->worker->availabilityStatus == 'On-leave' ? 'checked' : '' }}> On-leave</label>
                            <label><input type="radio" name="availabilityStatus" value="Unavailable" {{ $user->worker->availabilityStatus == 'Unavailable' ? 'checked' : '' }}> Unavailable</label>
                        </div>
                    </div>
                    <button type="submit" class="saveRoleButton">Save</button>
                </form>
                <div id="workerDetails">
                    <p><strong>Worker Type:</strong> {{ $user->worker->workerType ?? 'N/A' }}</p>
                    <p><strong>Availability Status:</strong> {{ $user->worker->availabilityStatus ?? 'N/A' }}</p>
                </div>
                @elseif($role == 'Homeowner')
                <div class="role-header">
                    <h3>Role Information</h3>
                    <button class="editRoleButton">Edit</button>
                </div>
                <form id="homeownerForm" class="hidden">
                    @csrf
                    <div class="form-group">
                        <label for="homeAddress">Home Address:</label>
                        <input type="text" id="homeAddress" name="homeAddress" value="{{ $user->homeowner->homeAddress ?? 'N/A' }}">
                    </div>
                    <button type="submit" class="saveRoleButton">Save</button>
                </form>
                <div id="homeownerDetails">
                    <p><strong>Home Address:</strong> {{ $user->homeowner->homeAddress ?? 'N/A' }}</p>
                </div>

                @endif
            </div>

            <div class="profile-card password-card">
                <h3>Security</h3>
                <form method="POST" action="{{ route('updatePassword') }}" class="password-form">
                    @csrf
                    <div class="current-password-group">
                        <label for="currentPassword">Current Password</label>
                        <span class="lock-icon">🔒</span>
                        <input type="password" id="currentPasswordDisplay" value="............." readonly>
                        <button type="button" id="changePasswordButton">Change</button>
                    </div>
                    <div id="passwordFields" class="hidden">
                        <div class="password-group">
                            <label for="currentPassword">Current Password</label>
                            <input type="password" id="currentPassword" name="currentPassword">
                        </div>
                        <div class="password-group">
                            <label for="newPassword">New Password</label>
                            <input type="password" id="newPassword" name="newPassword" disabled>
                        </div>
                        <div class="password-group">
                            <label for="confirmPassword">Confirm Password</label>
                            <input type="password" id="confirmPassword" name="confirmPassword" disabled>
                        </div>
                        <div class="password-note">
                            <p class="password-rules">
                                Password must contain:
                            <ul>
                                <li>At least 8 characters</li>
                                <li>One uppercase letter</li>
                                <li>One lowercase letter</li>
                                <li>One digit</li>
                                <li>One special character (@$!%*?&)</li>
                            </ul>
                            </p>
                        </div>
                        <div class="password-actions">
                            <button type="submit" class="changePasswordButton">Update Password</button>
                            <button type="button" id="backButton">Back</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                // Edit Gender Button
                const editBtn = document.querySelector(".editButton");
                const genderText = document.getElementById("genderText");
                const genderSelect = document.getElementById("genderSelect");

                if (editBtn && genderText && genderSelect) {
                    editBtn.addEventListener("click", function () {
                        if (editBtn.textContent === "Edit") {
                            genderText.classList.add("hidden");
                            genderSelect.classList.remove("hidden");
                            editBtn.textContent = "Cancel";
                        } else {
                            genderText.classList.remove("hidden");
                            genderSelect.classList.add("hidden");
                            editBtn.textContent = "Edit";
                        }
                    });

                    genderSelect.addEventListener("change", function () {
                        const selectedGender = genderSelect.value;
                        fetch("{{ route('updateGender') }}", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify({gender: selectedGender})
                        })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        location.reload();
                                    } else {
                                        alert("Failed to update gender.");
                                    }
                                })
                                .catch(error => console.error("Error:", error));
                    });
                }

                // Edit Phone Number Button
                const editPhoneBtn = document.querySelector(".editPhoneButton");
                const phoneNumSpan = document.getElementById("phoneNum");
                const phoneErrorMessage = document.createElement("div");
                phoneErrorMessage.id = "phoneErrorMessage";
                phoneErrorMessage.style.color = "red";
                phoneErrorMessage.style.display = "none";
                phoneNumSpan.parentNode.insertBefore(phoneErrorMessage, phoneNumSpan.nextSibling);

                let isEditing = false;

                if (editPhoneBtn && phoneNumSpan) {
                    editPhoneBtn.addEventListener("click", function () {
                        if (isEditing) {
                            const updatedPhone = phoneNumSpan.textContent.trim();

                            if (validatePhoneNumber(updatedPhone)) {
                                phoneErrorMessage.style.display = "none";
                                updatePhoneNumber(updatedPhone);
                                phoneNumSpan.contentEditable = "false";
                                phoneNumSpan.classList.remove("editable-phone");
                                editPhoneBtn.textContent = "Edit Phone Number";
                            } else {
                                phoneErrorMessage.textContent = "Invalid phone number format.";
                                phoneErrorMessage.style.display = "block";
                            }
                        } else {
                            phoneNumSpan.contentEditable = "true";
                            phoneNumSpan.classList.add("editable-phone");
                            phoneNumSpan.focus();
                            editPhoneBtn.textContent = "Save";
                        }

                        isEditing = !isEditing;
                    });
                }

                function validatePhoneNumber(phone) {
                    const phoneRegex = /^(01[0-9]-?\d{7,8}|0[3-9]-?\d{6,8})$/;
                    return phoneRegex.test(phone);
                }

                function updatePhoneNumber(phone) {
                    fetch("{{ route('updatePhone') }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({phone: phone})
                    })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert("Phone number updated successfully!");
                                } else {
                                    alert("Failed to update phone number.");
                                }
                            })
                            .catch(error => console.error("Error:", error));
                }

                const editRoleButton = document.querySelector(".editRoleButton");
                const roleForm = document.querySelector("#workerForm, #homeownerForm");
                const roleDetails = document.querySelector("#workerDetails, #homeownerDetails");

                if (editRoleButton && roleForm && roleDetails) {
                    editRoleButton.addEventListener("click", function () {
                        roleForm.classList.remove("hidden");
                        roleDetails.classList.add("hidden");
                        editRoleButton.classList.add("hidden");
                    });

                    roleForm.addEventListener("submit", function (e) {
                        e.preventDefault();

                        const formData = new FormData(roleForm);

                        fetch("{{ route('updateRoleDetails') }}", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: formData
                        })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        alert("Role details updated successfully!");
                                        location.reload();
                                    } else {
                                        alert("Failed to update role details.");
                                    }
                                })
                                .catch(error => console.error("Error:", error));
                    });
                }

                const changePasswordButton = document.getElementById("changePasswordButton");
                const currentPasswordDisplay = document.getElementById("currentPasswordDisplay");
                const passwordFields = document.getElementById("passwordFields");
                const currentPasswordInput = document.getElementById("currentPassword");
                const newPasswordInput = document.getElementById("newPassword");
                const confirmPasswordInput = document.getElementById("confirmPassword");
                const backButton = document.getElementById("backButton");

                if (changePasswordButton && currentPasswordDisplay && passwordFields && backButton) {
                    changePasswordButton.addEventListener("click", function () {
                        // Hide the current password display and show the password fields
                        currentPasswordDisplay.parentElement.classList.add("hidden");
                        passwordFields.classList.remove("hidden");
                        currentPasswordInput.focus();
                    });

                    // Back button functionality
                    backButton.addEventListener("click", function () {
                        // Reset the password fields
                        currentPasswordInput.value = "";
                        newPasswordInput.value = "";
                        confirmPasswordInput.value = "";
                        newPasswordInput.setAttribute("disabled", true);
                        confirmPasswordInput.setAttribute("disabled", true);

                        // Hide the password fields and show the current password display
                        passwordFields.classList.add("hidden");
                        currentPasswordDisplay.parentElement.classList.remove("hidden");
                    });

                    // Validate current password on Enter or when clicking outside
                    currentPasswordInput.addEventListener("keypress", function (e) {
                        if (e.key === "Enter") {
                            e.preventDefault();
                            validateCurrentPassword();
                        }
                    });

                    currentPasswordInput.addEventListener("blur", function () {
                        validateCurrentPassword();
                    });

                    function validateCurrentPassword() {
                        const enteredPassword = currentPasswordInput.value;

                        if (enteredPassword) {
                            fetch("{{ route('validateCurrentPassword') }}", {
                                method: "POST",
                                headers: {
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                    "Content-Type": "application/json"
                                },
                                body: JSON.stringify({current_password: enteredPassword})
                            })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            // Enable new password fields if the current password is correct
                                            newPasswordInput.removeAttribute("disabled");
                                            confirmPasswordInput.removeAttribute("disabled");
                                        } else {
                                            alert("Current password is incorrect.");
                                            currentPasswordInput.value = "";
                                            currentPasswordInput.focus();
                                        }
                                    })
                                    .catch(error => console.error("Error:", error));
                        }
                    }

                    document.querySelector(".password-form").addEventListener("submit", function (e) {
                        e.preventDefault();

                        const newPassword = newPasswordInput.value;
                        const confirmPassword = confirmPasswordInput.value;

                        if (newPassword !== confirmPassword) {
                            alert("New password and confirm password do not match.");
                            return;
                        }

                        if (newPassword === currentPasswordInput.value) {
                            alert("New password cannot be the same as the current password.");
                            return;
                        }

                        fetch("{{ route('updatePassword') }}", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify({
                                current_password: currentPasswordInput.value,
                                new_password: newPassword,
                                new_password_confirmation: confirmPassword
                            })
                        })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        alert("Password updated successfully!");
                                        location.reload();
                                    } else {
                                        alert(data.error || "Failed to update password.");
                                    }
                                })
                                .catch(error => console.error("Error:", error));
                    });
                }
            });

            document.addEventListener('DOMContentLoaded', () => {
                const userAvatar = document.getElementById('userAvatar');

                if (userAvatar) {
                    const userID = "{{ Auth::user()->userID }}"; // Example: "U250001"
                    const lastDigit = parseInt(userID.slice(-1), 10) || 0;  // Default to 0 if error

                    const colors = [
                        '#ff6b6b', // 0 - Red
                        '#ffb400', // 1 - Orange
                        '#ffdd57', // 2 - Yellow
                        '#9cd326', // 3 - Lime
                        '#1dd1a1', // 4 - Teal
                        '#48dbfb', // 5 - Light Blue
                        '#5f27cd', // 6 - Purple
                        '#f368e0', // 7 - Pink
                        '#ff9ff3', // 8 - Light Pink
                        '#222f3e'  // 9 - Dark Gray
                    ];

                    userAvatar.style.backgroundColor = colors[lastDigit];
                }
            });
        </script>
    </body>
</html>
