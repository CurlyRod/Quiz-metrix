 document.addEventListener('DOMContentLoaded', function() {
            // Password form toggle
            const togglePasswordFormBtn = document.getElementById('toggle-password-form');
            const currentPasswordForm = document.getElementById('current-password-form');
            const passwordForm = document.getElementById('password-form');
            const cancelCurrentPasswordBtn = document.getElementById('cancel-current-password');
            const verifyCurrentPasswordBtn = document.getElementById('verify-current-password');
            const cancelPasswordBtn = document.getElementById('cancel-password-change');
            
            // Show current password form when "Change" is clicked
            togglePasswordFormBtn.addEventListener('click', function() {
                // Disable the change button
                togglePasswordFormBtn.disabled = true;
                togglePasswordFormBtn.style.opacity = '0.5';
                togglePasswordFormBtn.style.cursor = 'not-allowed';
                
                // Always start with the verify password form
                // Hide change password form if it's open
                if (passwordForm.classList.contains('active')) {
                    passwordForm.classList.remove('active');
                    setTimeout(() => {
                        passwordForm.style.display = 'none';
                    }, 300);
                }
                
                // Show current password form
                currentPasswordForm.style.display = 'block';
                setTimeout(() => {
                    currentPasswordForm.classList.add('active');
                }, 10);
            });
            
            // Cancel current password verification
            cancelCurrentPasswordBtn.addEventListener('click', function() {
                currentPasswordForm.classList.remove('active');
                setTimeout(() => {
                    currentPasswordForm.style.display = 'none';
                    // Clear the input
                    document.getElementById('current-password').value = '';
                    
                    // Re-enable the change button
                    togglePasswordFormBtn.disabled = false;
                    togglePasswordFormBtn.style.opacity = '1';
                    togglePasswordFormBtn.style.cursor = 'pointer';
                }, 300);
            });
            
            // Verify current password and show change password form
            verifyCurrentPasswordBtn.addEventListener('click', function() {
                // Here you would typically verify the current password with the backend
                // For now, we'll just proceed to the next step
                
                // Hide current password form with animation
                currentPasswordForm.classList.remove('active');
                
                // Wait for the animation to complete before showing the next form
                setTimeout(() => {
                    currentPasswordForm.style.display = 'none';
                    
                    // Show change password form
                    passwordForm.style.display = 'block';
                    setTimeout(() => {
                        passwordForm.classList.add('active');
                    }, 10);
                }, 300);
            });
            
            // Cancel password change
            cancelPasswordBtn.addEventListener('click', function() {
                passwordForm.classList.remove('active');
                setTimeout(() => {
                    passwordForm.style.display = 'none';
                    // Clear the inputs
                    document.getElementById('new-password').value = '';
                    document.getElementById('confirm-password').value = '';
                    
                    // Re-enable the change button
                    togglePasswordFormBtn.disabled = false;
                    togglePasswordFormBtn.style.opacity = '1';
                    togglePasswordFormBtn.style.cursor = 'pointer';
                }, 300);
            });
            
            // Avatar toggle dropdown
            const avatarToggle = document.getElementById('avatar-toggle');
            const avatarDropdown = document.getElementById('avatar-dropdown');
            const profileAvatar = document.getElementById('profile-avatar');
            const avatarOptions = document.querySelectorAll('.avatar-option');
            
            avatarToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                avatarDropdown.style.display = avatarDropdown.style.display === 'block' ? 'none' : 'block';
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function() {
                avatarDropdown.style.display = 'none';
            });
            
            avatarDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });
            
            // Avatar selection
            avatarOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const avatarSrc = this.getAttribute('data-avatar');
                    profileAvatar.src = avatarSrc;
                    avatarDropdown.style.display = 'none';
                });
            });
            
            // Password update form submission
            const submitPasswordBtn = document.getElementById('submit-password-change');
            const newPasswordInput = document.getElementById('new-password');
            const confirmPasswordInput = document.getElementById('confirm-password');
            
            submitPasswordBtn.addEventListener('click', function() {
                const newPassword = newPasswordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                if (!newPassword || !confirmPassword) {
                    alert('Please fill in all password fields');
                    return;
                }
                
                if (newPassword !== confirmPassword) {
                    alert('Passwords do not match');
                    return;
                }
                
                // Here you would typically send the password update to the backend
                alert('Password updated successfully!');
                
                // Reset form and hide
                newPasswordInput.value = '';
                confirmPasswordInput.value = '';
                passwordForm.classList.remove('active');
                setTimeout(() => {
                    passwordForm.style.display = 'none';
                    
                    // Re-enable the change button
                    togglePasswordFormBtn.disabled = false;
                    togglePasswordFormBtn.style.opacity = '1';
                    togglePasswordFormBtn.style.cursor = 'pointer';
                }, 300);
            });

            // Password visibility toggle
            const passwordToggleBtns = document.querySelectorAll('.password-toggle-btn');
            passwordToggleBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const passwordInput = document.getElementById(targetId);
                    const icon = this.querySelector('i');
                    
                    // Toggle password visibility
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.className = 'bx bx-eye-alt';
                    } else {
                        passwordInput.type = 'password';
                        icon.className = 'bx bx-eye-closed';
                    }
                });
            });
        });