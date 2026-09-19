@include('auth.partials.head')
@include('auth.partials.login-form-style')

<div class="login-container">
    <div class="login-card">
        <!-- brand area -->
        <div class="brand-header">
            <div class="logo-mark">
                <img src="{{ $settings?->logo ? asset('storage/' . $settings->logo) : asset('images/logo2.jpeg') }}" alt="Logo">
            </div>

            <h1>owner access</h1>
            <div class="sub">
                <i class="fas fa-lock" style="color: #B08D57; margin-right: 6px;"></i>
                store administration
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

        <!-- LOGIN FORM -->
        <form action="{{ route('admin.login.submit') }}" method="POST">
            @csrf
            <!-- email field -->
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" class="input-field" id="emailField" placeholder="Email address"
                    autocomplete="email" required>
            </div>

            <!-- password field -->
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" class="input-field" id="passwordField" placeholder="Password"
                    autocomplete="current-password" required>
            </div>

            <!-- login button -->
            <button type="submit" class="login-btn" id="loginStaticBtn">
                <span>Log in</span> <i class="fas fa-arrow-right"></i>
            </button>
        </form>
    </div>
</div>
