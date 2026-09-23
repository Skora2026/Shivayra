@include('auth.partials.head')
@include('auth.partials.login-form-style')


<div class="login-container">
    <div class="login-card">
        <!-- brand area with 4 colors visible -->
        <div class="brand-header">
            <div class="logo-mark">
                <img src="{{ $settings?->logo ? asset('storage/' . $settings->logo) : asset('images/logo2.jpeg') }}" alt="Logo">
            </div>

            <h1>welcome back</h1>
            <div class="sub">
                <i class="fas fa-seedling" style="color: #0A9051; margin-right: 6px;"></i>
                sign in to your account
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger" style="border-radius: 1.2rem; margin-bottom: 1.5rem; padding: 0.8rem; font-size: 0.85rem; background-color: #f8d7da; border: 1px solid #f5c2c7; color: #842029;">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success" style="border-radius: 1.2rem; margin-bottom: 1.5rem; padding: 0.8rem; font-size: 0.85rem; background-color: #d1e7dd; border: 1px solid #badbcc; color: #0f5132;">
                {{ session('success') }}
            </div>
        @endif

        <!-- STATIC LOGIN FORM -->
        <form action="{{ route('login.submit') }}" method="POST">
    @csrf
            <!-- email / username field -->
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email"  name="email" class="input-field" id="emailField" placeholder="Email address"
                    autocomplete="email" required>
            </div>

            <!-- password field -->
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password"  name="password" class="input-field" id="passwordField" placeholder="Password"
                    autocomplete="current-password" required>
            </div>

            <!-- options row -->
            <div class="options">
                <label class="checkbox-label">
                    <input type="checkbox" id="rememberMe"> <span>Keep me signed in</span>
                </label>
                <a href="{{ route('password.request') }}" class="forgot-link" id="forgotStatic">Forgot password?</a>
            </div>

            <!-- login button -->
            <button type="submit" class="login-btn" id="loginStaticBtn">
                <span>Log in</span> <i class="fas fa-arrow-right"></i>
            </button>

            <div class="signup-text">
                Don't have an account? <a href="{{ route('register') }}" id="signupStatic">Create account</a>
            </div>

            <!-- Divider -->
            <div style="display: flex; align-items: center; gap: 12px; margin: 1.2rem 0;">
                <hr style="flex: 1; border: 0; height: 1px; background: #e0d8cf;">
                <span style="font-size: 0.75rem; color: #9aab9f; text-transform: uppercase; letter-spacing: 1px;">or continue with</span>
                <hr style="flex: 1; border: 0; height: 1px; background: #e0d8cf;">
            </div>

            <!-- Google Sign In -->
            <a href="{{ route('google.redirect') }}" class="google-btn" style="display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 0.8rem; border: 1.5px solid #e9e4df; border-radius: 1.6rem; background: white; font-family: 'Montserrat', sans-serif; font-weight: 600; font-size: 0.9rem; color: #3c4f44; text-decoration: none; transition: all 0.2s; cursor: pointer;">
                <svg width="20" height="20" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                Sign in with Google
            </a>




            <!-- demo message (only for showing feedback, no alert spam) -->
            <div id="feedbackMsg" class="demo-message" style="display: none;"></div>
        </form>
    </div>
</div>