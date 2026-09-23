@include('auth.partials.head')

<style>
    /* ------------------------------
        ROOT CSS VARIABLES (4 colors)
    --------------------------------- */
    :root {
        --warm-peach: #B08D57;
        --deep-forest: #40111F;
        --vibrant-mint: #8C7355;
        --ember-brown: #5C1A2E;
        --bg-light: #FAF7F2;

        --card-white: #ffffff;
        --text-dark: #1a2e24;
        --shadow-elegant: 0 20px 35px -12px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.02);
        --shadow-focus: 0 0 0 3px rgba(254, 145, 75, 0.2);
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

    /* main container */
    .auth-container {
        width: 100%;
        max-width: 520px;
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

    /* card style */
    .auth-card {
        background: var(--card-white);
        border-radius: 2rem;
        padding: 2rem 1.8rem 2.2rem;
        box-shadow: var(--shadow-elegant);
        border: 1px solid rgba(254, 145, 75, 0.2);
        transition: all 0.2s ease;
    }

    /* form panel */
    .form-panel {
        display: block;
        animation: fadeSlide 0.3s ease;
    }

    @keyframes fadeSlide {
        from {
            opacity: 0;
            transform: translateX(8px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* brand header */
    .brand-header {
        text-align: center;
        margin-bottom: 1.8rem;
    }

    .logo-mark img {
        max-width: 140px;
        height: auto;
        margin-bottom: 0.5rem;
    }

    .color-strip {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin: 16px 0 8px;
    }

    .color-dot {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
        cursor: default;
    }

    .color-dot:nth-child(1) {
        background: #FE914B;
    }

    .color-dot:nth-child(2) {
        background: #004727;
    }

    .color-dot:nth-child(3) {
        background: #0A9051;
    }

    .color-dot:nth-child(4) {
        background: #7F3100;
    }

    .color-dot:hover {
        transform: scale(1.15);
    }

    h2 {
        font-size: 1.8rem;
        font-weight: 700;
        margin-top: 0.5rem;
        color: var(--deep-forest);
        letter-spacing: -0.3px;
    }

    .sub {
        color: #5f6c64;
        font-size: 0.9rem;
        margin-top: 0.25rem;
        margin-bottom: 1.8rem;
        border-left: 3px solid var(--warm-peach);
        padding-left: 0.75rem;
    }

    /* input groups */
    .input-group {
        margin-bottom: 1.2rem;
        position: relative;
    }

    .input-group i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--warm-peach);
        font-size: 1rem;
        pointer-events: none;
        z-index: 2;
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
        box-shadow: var(--shadow-focus);
    }

    .input-field::placeholder {
        color: #c2cdc4;
        font-weight: 400;
    }

    /* password row with eye */
    .password-wrapper {
        position: relative;
    }

    .toggle-pwd {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #bdaa9a;
        cursor: pointer;
        font-size: 1rem;
        z-index: 3;
        transition: color 0.2s;
    }

    .toggle-pwd:hover {
        color: var(--ember-brown);
    }

    /* options row */
    .options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 0.5rem 0 1.6rem;
        font-size: 0.85rem;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #3c4f44;
        cursor: pointer;
    }

    .checkbox-label input {
        accent-color: var(--vibrant-mint);
        width: 1rem;
        height: 1rem;
        cursor: pointer;
    }

    .terms-link {
        color: var(--ember-brown);
        text-decoration: none;
        font-weight: 600;
        border-bottom: 1px solid rgba(127, 49, 0, 0.3);
        transition: all 0.2s;
    }

    .terms-link:hover {
        color: var(--deep-forest);
        border-bottom-color: var(--deep-forest);
    }

    /* buttons */
    .action-btn {
        width: 100%;
        background: linear-gradient(100deg, var(--deep-forest) 0%, var(--vibrant-mint) 100%);
        border: none;
        padding: 0.9rem;
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
        margin-top: 0.5rem;
    }

    .action-btn:hover {
        background: linear-gradient(100deg, #00351f, var(--vibrant-mint));
        transform: translateY(-2px);
        box-shadow: 0 12px 22px rgba(0, 71, 39, 0.25);
    }

    .action-btn:active {
        transform: translateY(1px);
    }

    hr {
        margin: 1.5rem 0 0.5rem;
        border: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, #FE914B40, #0A905140, transparent);
    }

    .footer-note {
        font-size: 0.7rem;
        text-align: center;
        margin-top: 1rem;
        color: #9aab9f;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    /* feedback message */
    .feedback-msg {
        margin-top: 1rem;
        padding: 0.7rem;
        border-radius: 1.2rem;
        font-size: 0.8rem;
        text-align: center;
        transition: 0.2s;
        display: none;
    }

    /* register specific row - responsive */
    .register-row {
        display: flex;
        gap: 0.8rem;
        flex-wrap: wrap;
    }

    .register-row .input-group {
        flex: 1;
        min-width: 120px;
    }

    /* responsive */
    @media (max-width: 480px) {
        .auth-card {
            padding: 1.5rem;
        }

        h2 {
            font-size: 1.5rem;
        }

        .register-row {
            flex-direction: column;
            gap: 0;
        }
    }
</style>




<div class="auth-container">
    <div class="auth-card">
        <!-- brand section with logo -->
        <div class="brand-header">
            <div class="logo-mark">
                <img src="{{ $settings?->logo ? asset('storage/' . $settings->logo) : asset('images/logo2.jpeg') }}" alt="Logo">
            </div>

        </div>

        <!-- REGISTER FORM PANEL (only registration) -->
        <div class="form-panel">
            <h2>create account</h2>
            <div class="sub">
                <i class="fas fa-spa" style="color: #FE914B;"></i> join the green collective
            </div>

            <form action="{{ route('register.submit') }}" method="POST">

                @csrf

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

                <!-- Row: First Name + Last Name -->
                <div class="register-row">
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="name" class="input-field" id="regFirstName"
                            placeholder="First name" autocomplete="given-name" required value="{{ old('name') }}">
                    </div>

                </div>

                <!-- Email field with OTP -->
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" class="input-field" id="regEmail" placeholder="Email address"
                        autocomplete="email" required value="{{ old('email') }}">
                    <div id="emailOtpSection" style="display: none; margin-top: 0.5rem;">
                        <div style="display: flex; gap: 8px;">
                            <input type="text" id="emailOtpInput" class="input-field" placeholder="Enter 6-digit OTP" maxlength="6" style="padding-left: 1rem;">
                            <button type="button" id="verifyEmailOtp" class="action-btn" style="width: auto; padding: 0.8rem 1.2rem; font-size: 0.85rem;">Verify</button>
                        </div>
                        <small id="emailOtpMsg" style="font-size: 0.75rem; margin-top: 4px; display: block;"></small>
                    </div>
                </div>



                <!-- Password field with toggle -->
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <div class="password-wrapper">
                        <input type="password" name="password" class="input-field" id="regPassword"
                            placeholder="Create password" autocomplete="new-password" required
                            style="padding-right: 2.5rem;">
                        <button type="button" class="toggle-pwd" id="toggleRegPwd" aria-label="Show password">
                            <i class="far fa-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <!-- Confirm Password field with toggle -->
                <div class="input-group">
                    <i class="fas fa-check-circle"></i>
                    <div class="password-wrapper">
                        <input type="password" name="password_confirmation" class="input-field" id="regConfirmPassword"
                            placeholder="Confirm password" autocomplete="off" required style="padding-right: 2.5rem;">
                        <button type="button" class="toggle-pwd" id="toggleConfirmPwd" aria-label="Show password">
                            <i class="far fa-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <!-- Terms & Conditions -->
                <div class="options">
                    <label class="checkbox-label">
                        <input type="checkbox" id="regTerms" required>
                        <span>I agree to the <a href="#" id="termsLink" class="terms-link">Terms &
                                Conditions</a></span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="action-btn" id="submitRegBtn">
                    <span>Register</span> <i class="fas fa-leaf"></i>
                </button>

                <!-- Feedback message area -->
                <div id="regFeedback" class="feedback-msg"></div>
            </form>

        </div>
    </div>
</div>

<script>
(function() {
    let emailVerified = false;

    // Send OTP function
    window.sendOtp = async function(type, value) {
        if (!value || (type === 'email' && !value.includes('@'))) {
            showOtpMsg('Please enter a valid email', 'error');
            return;
        }

        const section = document.getElementById('emailOtpSection');
        section.style.display = 'block';
        showOtpMsg('Sending OTP...', 'info');

        try {
            const res = await fetch('/otp/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ type: 'email', value })
            });
            const data = await res.json();
            if (data.success) {
                showOtpMsg(data.message, 'success');
                startResendTimer();
            } else {
                showOtpMsg(data.message, 'error');
            }
        } catch (e) {
            showOtpMsg('Failed to send OTP. Please try again.', 'error');
        }
    };

    // Verify OTP function
    window.verifyOtp = async function(value) {
        const input = document.getElementById('emailOtpInput');
        const otp = input?.value?.trim();
        if (!otp || otp.length !== 6) {
            showOtpMsg('Please enter a 6-digit OTP', 'error');
            return;
        }

        try {
            const res = await fetch('/otp/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ type: 'email', value, otp })
            });
            const data = await res.json();
            if (data.success) {
                showOtpMsg('✓ ' + data.message, 'success');
                emailVerified = true;
                input.disabled = true;
                document.getElementById('verifyEmailOtp').disabled = true;
            } else {
                showOtpMsg(data.message, 'error');
            }
        } catch (e) {
            showOtpMsg('Verification failed. Please try again.', 'error');
        }
    };

    function showOtpMsg(msg, status) {
        const el = document.getElementById('emailOtpMsg');
        if (!el) return;
        el.textContent = msg;
        el.style.color = status === 'success' ? '#0f5132' : status === 'error' ? '#842029' : '#666';
        el.style.background = status === 'success' ? '#d1e7dd' : status === 'error' ? '#f8d7da' : 'transparent';
        el.style.padding = '4px 8px';
        el.style.borderRadius = '8px';
    }

    function startResendTimer() {
        let seconds = 60;
        const btn = document.getElementById('sendEmailOtp');
        if (btn) {
            btn.disabled = true;
            btn.textContent = seconds + 's';
            const interval = setInterval(() => {
                seconds--;
                btn.textContent = seconds + 's';
                if (seconds <= 0) {
                    clearInterval(interval);
                    btn.disabled = false;
                    btn.textContent = 'Send OTP';
                }
            }, 1000);
        }
    }

    // Auto-send OTP when email field loses focus
    const emailInput = document.getElementById('regEmail');
    let emailBlurTimeout;
    emailInput?.addEventListener('blur', function() {
        clearTimeout(emailBlurTimeout);
        emailBlurTimeout = setTimeout(() => sendOtp('email', this.value), 500);
    });

    // Verify button click
    document.getElementById('verifyEmailOtp')?.addEventListener('click', () => {
        verifyOtp(document.getElementById('regEmail').value);
    });

    // Prevent form submit if email not verified
    document.querySelector('form')?.addEventListener('submit', function(e) {
        if (!emailVerified) {
            e.preventDefault();
            showOtpMsg('Please verify your email first', 'error');
            document.getElementById('emailOtpSection').style.display = 'block';
            return false;
        }
    });
})();
</script>
