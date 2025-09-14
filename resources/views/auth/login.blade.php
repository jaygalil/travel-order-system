@extends('layouts.app')

@section('content')
<!-- Modern Login Page with Beautiful Design -->
<div class="login-container">
    <div class="container-fluid h-100">
        <div class="row h-100 align-items-center">
            <!-- Left Side - Branding/Info -->
            <div class="col-lg-6 d-none d-lg-flex login-info-section">
                <div class="login-info-content">
                    <div class="brand-section">
                        <div class="brand-icon">
                            <i class="fas fa-plane-departure"></i>
                        </div>
                        <h1 class="brand-title">{{ config('app.name', 'Travel Order System') }}</h1>
                        <p class="brand-subtitle">Your gateway to seamless business travel management</p>
                    </div>
                    
                    <div class="features-list">
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Streamlined approval workflows</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Real-time order tracking</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Advanced reporting & analytics</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Mobile-friendly interface</span>
                        </div>
                    </div>
                    
                    <!-- Animated Background Elements -->
                    <div class="floating-elements">
                        <div class="floating-element element-1"></div>
                        <div class="floating-element element-2"></div>
                        <div class="floating-element element-3"></div>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Login Form -->
            <div class="col-lg-6">
                <div class="login-form-section">
                    <div class="login-form-wrapper">
                        <!-- Mobile Brand (visible only on small screens) -->
                        <div class="mobile-brand d-lg-none">
                            <i class="fas fa-plane-departure"></i>
                            <h2>{{ config('app.name', 'Travel Order System') }}</h2>
                        </div>
                        
                        <div class="login-header">
                            <h2 class="login-title">Welcome Back</h2>
                            <p class="login-subtitle">Sign in to continue to your dashboard</p>
                        </div>
                        
                        <form method="POST" action="{{ route('login') }}" class="login-form" id="loginForm">
                            @csrf
                            
                            <!-- Email Field -->
                            <div class="form-group">
                                <div class="form-field">
                                    <div class="form-field-icon">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <input 
                                        id="email" 
                                        type="email" 
                                        class="form-control modern-input @error('email') is-invalid @enderror" 
                                        name="email" 
                                        value="{{ old('email') }}" 
                                        required 
                                        autocomplete="email" 
                                        autofocus
                                        placeholder=" "
                                    >
                                    <label for="email" class="floating-label">Email Address</label>
                                    @error('email')
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Password Field -->
                            <div class="form-group">
                                <div class="form-field">
                                    <div class="form-field-icon">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                    <input 
                                        id="password" 
                                        type="password" 
                                        class="form-control modern-input @error('password') is-invalid @enderror" 
                                        name="password" 
                                        required 
                                        autocomplete="current-password"
                                        placeholder=" "
                                    >
                                    <label for="password" class="floating-label">Password</label>
                                    <button type="button" class="password-toggle" id="passwordToggle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @error('password')
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Remember Me & Forgot Password -->
                            <div class="form-options">
                                <div class="form-check modern-checkbox">
                                    <input 
                                        class="form-check-input" 
                                        type="checkbox" 
                                        name="remember" 
                                        id="remember" 
                                        {{ old('remember') ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label" for="remember">
                                        Remember me
                                    </label>
                                </div>
                                
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="forgot-password-link">
                                        Forgot password?
                                    </a>
                                @endif
                            </div>
                            
                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-login" id="loginBtn">
                                <span class="btn-text">Sign In</span>
                                <div class="btn-spinner">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </button>
                            
                            <!-- Register Link -->
                            @if (Route::has('register'))
                                <div class="register-link">
                                    <p>Don't have an account? <a href="{{ route('register') }}">Create one here</a></p>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Modern Login Page Styles */
.login-container {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
}

.login-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 40% 80%, rgba(120, 219, 255, 0.3) 0%, transparent 50%);
    animation: backgroundShift 10s ease-in-out infinite alternate;
}

@keyframes backgroundShift {
    0% { transform: translateX(-10px) translateY(-5px) rotate(1deg); }
    100% { transform: translateX(10px) translateY(5px) rotate(-1deg); }
}

/* Left Side - Info Section */
.login-info-section {
    padding: 3rem;
    color: white;
    position: relative;
    z-index: 2;
}

.login-info-content {
    max-width: 500px;
    margin: auto;
}

.brand-section {
    text-align: center;
    margin-bottom: 3rem;
    animation: fadeInUp 1s ease-out;
}

.brand-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    color: #ffffff;
    animation: bounce 2s infinite;
}

.brand-title {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
    background: linear-gradient(45deg, #ffffff, #e0e7ff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.brand-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    margin-bottom: 0;
}

.features-list {
    animation: fadeInUp 1s ease-out 0.3s both;
}

.feature-item {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    font-size: 1rem;
    opacity: 0.9;
    transition: all 0.3s ease;
}

.feature-item:hover {
    opacity: 1;
    transform: translateX(10px);
}

.feature-item i {
    color: #10b981;
    margin-right: 0.75rem;
    font-size: 1.1rem;
}

/* Floating Elements */
.floating-elements {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
    z-index: -1;
}

.floating-element {
    position: absolute;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
}

.element-1 {
    width: 60px;
    height: 60px;
    top: 20%;
    left: 10%;
    animation-delay: 0s;
}

.element-2 {
    width: 80px;
    height: 80px;
    top: 60%;
    right: 15%;
    animation-delay: 2s;
}

.element-3 {
    width: 40px;
    height: 40px;
    bottom: 30%;
    left: 20%;
    animation-delay: 4s;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(180deg); }
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-10px); }
    60% { transform: translateY(-5px); }
}

/* Right Side - Login Form */
.login-form-section {
    padding: 2rem;
    height: 100vh;
    display: flex;
    align-items: center;
    position: relative;
    z-index: 2;
}

.login-form-wrapper {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 3rem;
    box-shadow: 
        0 25px 50px -12px rgba(0, 0, 0, 0.25),
        0 0 0 1px rgba(255, 255, 255, 0.2);
    width: 100%;
    max-width: 500px;
    margin: 0 auto;
    animation: slideInRight 0.8s ease-out;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.mobile-brand {
    text-align: center;
    margin-bottom: 2rem;
    color: #1f2937;
}

.mobile-brand i {
    font-size: 3rem;
    color: #3b82f6;
    margin-bottom: 0.5rem;
    display: block;
}

.mobile-brand h2 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
}

.login-header {
    text-align: center;
    margin-bottom: 2rem;
    animation: fadeInUp 1s ease-out 0.2s both;
}

.login-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.login-subtitle {
    color: #6b7280;
    font-size: 1rem;
    margin-bottom: 0;
}

/* Modern Form Styles */
.login-form {
    animation: fadeInUp 1s ease-out 0.4s both;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-field {
    position: relative;
}

.form-field-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    z-index: 2;
    transition: color 0.3s ease;
}

.modern-input {
    background: rgba(255, 255, 255, 0.8);
    border: 2px solid rgba(209, 213, 219, 0.5);
    border-radius: 16px;
    padding: 1rem 1rem 1rem 3rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    width: 100%;
    height: 56px;
}

.modern-input:focus {
    outline: none;
    border-color: #3b82f6;
    background: rgba(255, 255, 255, 0.95);
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    transform: translateY(-2px);
}

.modern-input:focus + .floating-label,
.modern-input:not(:placeholder-shown) + .floating-label {
    top: -8px;
    left: 12px;
    font-size: 0.75rem;
    color: #3b82f6;
    background: rgba(255, 255, 255, 0.9);
    padding: 0 4px;
}

.modern-input:focus ~ .form-field-icon {
    color: #3b82f6;
}

.floating-label {
    position: absolute;
    left: 3rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 1rem;
    transition: all 0.3s ease;
    pointer-events: none;
    z-index: 1;
}

.password-toggle {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    transition: color 0.3s ease;
    z-index: 2;
}

.password-toggle:hover {
    color: #3b82f6;
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.modern-checkbox {
    display: flex;
    align-items: center;
}

.modern-checkbox .form-check-input {
    width: 18px;
    height: 18px;
    border-radius: 4px;
    border: 2px solid #d1d5db;
    margin-right: 0.5rem;
    transition: all 0.3s ease;
}

.modern-checkbox .form-check-input:checked {
    background-color: #3b82f6;
    border-color: #3b82f6;
}

.forgot-password-link {
    color: #3b82f6;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: color 0.3s ease;
}

.forgot-password-link:hover {
    color: #2563eb;
    text-decoration: underline;
}

.btn-login {
    width: 100%;
    height: 56px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    border: none;
    border-radius: 16px;
    color: white;
    font-size: 1.1rem;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
}

.btn-login:active {
    transform: translateY(0);
}

.btn-login.loading .btn-text {
    opacity: 0;
}

.btn-login.loading .btn-spinner {
    opacity: 1;
}

.btn-spinner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.register-link {
    text-align: center;
    margin-top: 1rem;
}

.register-link p {
    margin: 0;
    color: #6b7280;
}

.register-link a {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease;
}

.register-link a:hover {
    color: #2563eb;
    text-decoration: underline;
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Error Styles */
.modern-input.is-invalid {
    border-color: #ef4444;
    animation: shake 0.5s ease-in-out;
}

.invalid-feedback {
    color: #ef4444;
    font-size: 0.875rem;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

/* Responsive Design */
@media (max-width: 991.98px) {
    .login-form-section {
        min-height: 100vh;
        padding: 1rem;
    }
    
    .login-form-wrapper {
        padding: 2rem 1.5rem;
        border-radius: 20px;
        margin: 1rem 0;
    }
    
    .login-title {
        font-size: 1.75rem;
    }
    
    .brand-title {
        font-size: 2rem;
    }
}

@media (max-width: 575.98px) {
    .login-form-wrapper {
        padding: 1.5rem 1rem;
        border-radius: 16px;
    }
    
    .form-options {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle functionality
    const passwordToggle = document.getElementById('passwordToggle');
    const passwordInput = document.getElementById('password');
    
    if (passwordToggle && passwordInput) {
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            if (type === 'text') {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    }
    
    // Form submission with loading state
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    
    if (loginForm && loginBtn) {
        loginForm.addEventListener('submit', function() {
            loginBtn.classList.add('loading');
            loginBtn.disabled = true;
            
            // Re-enable button after 5 seconds (in case of errors)
            setTimeout(() => {
                loginBtn.classList.remove('loading');
                loginBtn.disabled = false;
            }, 5000);
        });
    }
    
    // Input focus effects
    const inputs = document.querySelectorAll('.modern-input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
        
        // Handle autofill
        input.addEventListener('animationstart', function(e) {
            if (e.animationName === 'onAutoFillStart') {
                this.classList.add('auto-filled');
            }
        });
    });
    
    // Smooth animations on page load
    setTimeout(() => {
        document.body.classList.add('loaded');
    }, 100);
    
    // Add floating animation to form on hover
    const formWrapper = document.querySelector('.login-form-wrapper');
    if (formWrapper) {
        formWrapper.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        formWrapper.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    }
});

// Auto-fill detection CSS
const style = document.createElement('style');
style.textContent = `
@keyframes onAutoFillStart {
    from { /*auto-fill started*/ }
    to { /*auto-fill ended*/ }
}

@keyframes onAutoFillCancel {
    from { /*auto-fill cancelled*/ }
    to { /*auto-fill cancelled*/ }
}

input:-webkit-autofill {
    animation-name: onAutoFillStart;
    animation-duration: 0.001s;
}

input:not(:-webkit-autofill) {
    animation-name: onAutoFillCancel;
    animation-duration: 0.001s;
}
`;
document.head.appendChild(style);
</script>
@endpush
