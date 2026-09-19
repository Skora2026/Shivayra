@include('auth.partials.head')

<style>
    :root {
        --warm-peach: #B08D57;
        --deep-forest: #40111F;
        --vibrant-mint: #8C7355;
        --ember-brown: #5C1A2E;
        --bg-light: #FAF7F2;
        --card-white: #ffffff;
        --text-dark: #1a2e24;
        --shadow-elegant: 0 20px 35px -12px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.02);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Montserrat', sans-serif;
        background: linear-gradient(145deg, #FAF7F2 0%, #F1E8DC 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }

    .login-container {
        width: 100%;
        max-width: 440px;
        animation: fadeUp 0.5s ease-out;
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .login-card {
        background: var(--card-white);
        border-radius: 2rem;
        padding: 2rem 1.8rem 2.2rem;
        box-shadow: var(--shadow-elegant);
        border: 1px solid rgba(254, 145, 75, 0.2);
        transition: all 0.2s ease;
    }

    .brand-header {
        text-align: center;
        margin-bottom: 1.8rem;
    }

    .logo-mark img {
        width: 100px;
    }

    h1 {
        font-size: 1.8rem;
        font-weight: 700;
        margin-top: 0.25rem;
        color: var(--deep-forest);
        letter-spacing: -0.3px;
    }

    .sub {
        color: #5f6c64;
        font-size: 0.85rem;
        margin-top: 0.25rem;
        margin-bottom: 1.6rem;
        border-left: 3px solid var(--warm-peach);
        padding-left: 0.75rem;
        background: transparent;
    }

    .input-group {
        margin-bottom: 1.4rem;
        position: relative;
    }

    .input-group i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--warm-peach);
        font-size: 1.1rem;
        pointer-events: none;
    }

    .input-field {
        width: 100%;
        padding: 0.85rem 1rem 0.85rem 2.8rem;
        font-size: 0.95rem;
        font-weight: 500;
        border: 1.5px solid #e9e4df;
        border-radius: 1.6rem;
        background: white;
        font-family: 'Montserrat', sans-serif;
        transition: all 0.2s;
        outline: none;
        color: var(--text-dark);
    }

    .input-field:focus {
        border-color: var(--warm-peach);
        box-shadow: 0 0 0 3px rgba(254, 145, 75, 0.15);
    }

    .input-field::placeholder {
        color: #c2cdc4;
        font-weight: 400;
    }

    .login-btn {
        width: 100%;
        background: linear-gradient(100deg, var(--deep-forest) 0%, var(--vibrant-mint) 100%);
        border: none;
        padding: 0.85rem;
        border-radius: 2rem;
        font-weight: 700;
        font-size: 1rem;
        font-family: 'Montserrat', sans-serif;
        color: white;
        cursor: pointer;
        transition: 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 6px 14px rgba(0, 71, 39, 0.2);
    }

    .login-btn:hover {
        background: linear-gradient(100deg, #00351f, var(--vibrant-mint));
        transform: translateY(-1px);
        box-shadow: 0 12px 22px rgba(0, 71, 39, 0.25);
    }

    .signup-text {
        text-align: center;
        margin-top: 1.8rem;
        font-size: 0.85rem;
        color: #596f62;
    }

    .signup-text a {
        color: var(--warm-peach);
        font-weight: 600;
        text-decoration: none;
        border-bottom: 1px solid rgba(254, 145, 75, 0.4);
    }
</style>

<div class="login-container">
    <div class="login-card">
        <div class="brand-header">
            <div class="logo-mark">
                <img src="{{ $settings?->logo ? asset('storage/' . $settings->logo) : asset('images/logo2.jpeg') }}" alt="Logo">
            </div>
            <h1>Reset Password</h1>
            <div class="sub">
                <i class="fas fa-lock-open" style="color: #0A9051; margin-right: 6px;"></i>
                Enter the OTP received on email to reset password
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

        <form action="{{ route('password.update') }}" method="POST">
            @csrf
            
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" class="input-field" placeholder="Email address" value="{{ old('email', request()->get('email')) }}" required>
            </div>

            <div class="input-group">
                <i class="fas fa-key"></i>
                <input type="text" name="otp" class="input-field" placeholder="Enter 6-Digit OTP" maxlength="6" required>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" class="input-field" placeholder="New Password" required>
            </div>

            <div class="input-group">
                <i class="fas fa-shield-halved"></i>
                <input type="password" name="password_confirmation" class="input-field" placeholder="Confirm New Password" required>
            </div>

            <button type="submit" class="login-btn">
                <span>Reset Password</span> <i class="fas fa-check-double"></i>
            </button>

            <div class="signup-text">
                Back to <a href="{{ route('login') }}">Sign In</a>
            </div>
        </form>
    </div>
</div>
