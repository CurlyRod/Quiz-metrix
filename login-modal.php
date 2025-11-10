

<style>
    .microsoft-login {
    background: linear-gradient(135deg, #4e91f9 0%, #6a7efc 40%, #8664e2 70%, #a36fd6 100%);;
    color: white;
    font-size: 1.2rem;
    border-radius: 15px;
    transition: 0.3s ease-in-out;
    height: 125px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 25px;
    margin-bottom: 25px;
}

.microsoft-login:hover {
    background-color: #0078D4;
    color: white;
}

</style>
<!-- Microsoft Login Modal (First Modal) -->
<div class="modal fade" id="loginUser" tabindex="-1" aria-labelledby="ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg rounded">
            <div class="modal-header text-black">
                <h1 class="modal-title fs-5 fw-bolder" id="ModalLabel">Login</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body text-center p-4">
                <form id="saveStudent">
                    <div id="errorMessage" class="alert alert-danger d-none"></div>

                   <!-- Stylish Microsoft 365 Login Button -->
                   <a href="<?php  echo $authUrl  ?>" 
                    class="btn microsoft-login w-100 d-flex align-items-center justify-content-center">
                    <span>
                        <img src="https://365cloudstore.com/wp-content/uploads/2025/01/m365icon.webp" alt="m365 Logo "style="height: 100px; width: 100px;">
                        Login with Microsoft 365
                    </span>
                    </a>


                </form>
            </div>

            <div class="modal-footer" style="height: 70px;">
                <button class="btn btn-secondary w-100" data-bs-target="#standardLogin" data-bs-toggle="modal" style="background-color: #6366f1">Admin Login</button>
            </div>
        </div>
    </div>
</div>

<!-- Regular Login Modal (Second Modal) -->
<div class="modal fade" id="standardLogin" tabindex="-1" aria-labelledby="standardLoginLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg rounded">
            <div class="modal-header text-black">
                <h1 class="modal-title fs-5 fw-bolder" id="standardLoginLabel">Admin Login</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <form id="adminLoginForm" method="POST" action="admin_login.php">
                    <div id="errorMessage" class="alert alert-danger d-none"></div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username</label>
                        <input type="text" name="username" class="form-control rounded-3 shadow-sm" placeholder="Enter your Username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" name="password" class="form-control rounded-3 shadow-sm" placeholder="Enter your Password" required>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="btn w-100 rounded-3 shadow-sm" style="background-color: #6366f1; color: white;">
                        Login
                    </button>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary w-100" data-bs-target="#loginUser" data-bs-toggle="modal" style="background-color: #6366f1">Back to Microsoft Login</button>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const adminLoginForm = document.getElementById('adminLoginForm');
    
    if (adminLoginForm) {
        adminLoginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const errorMessage = document.getElementById('errorMessage');
            const submitButton = this.querySelector('button[type="submit"]');
            
            // Show loading state
            submitButton.disabled = true;
            submitButton.textContent = 'Logging in...';
            errorMessage.classList.add('d-none');
            
            fetch('admin_login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                console.log('Raw response:', text);
                
                try {
                    const data = JSON.parse(text);
                    
                    if (data.success) {
                        // Redirect to admin dashboard
                        window.location.href = '/admin/home/index.php';
                    } else {
                        errorMessage.textContent = data.message || 'Login failed.';
                        errorMessage.classList.remove('d-none');
                    }
                } catch (parseError) {
                    console.error('JSON Parse Error:', parseError);
                    errorMessage.textContent = 'Server error. Please try again.';
                    errorMessage.classList.remove('d-none');
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                errorMessage.textContent = 'Network error. Please check your connection.';
                errorMessage.classList.remove('d-none');
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.textContent = 'Login';
            });
        });
    }
});
</script>