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


            <!-- demo message (only for showing feedback, no alert spam) -->
            <div id="feedbackMsg" class="demo-message" style="display: none;"></div>
        </form>
    </div>
</div>