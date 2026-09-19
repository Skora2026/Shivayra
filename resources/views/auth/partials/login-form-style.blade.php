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

        /* extended */
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

    /* simple card container */
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

    /* static form card */
    .login-card {
        background: var(--card-white);
        border-radius: 2rem;
        padding: 2rem 1.8rem 2.2rem;
        box-shadow: var(--shadow-elegant);
        border: 1px solid rgba(254, 145, 75, 0.2);
        transition: all 0.2s ease;
    }

    /* logo / brand using the 4 colors */
    .brand-header {
        text-align: center;
        margin-bottom: 1.8rem;
    }

    .logo-mark img {
        width: 100;
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

    /* input groups - clean & static */
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

    /* options row */
    .options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 0.5rem 0 1.6rem;
        font-size: 0.8rem;
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
        margin: 0;
    }

    .forgot-link, .register-link, .form-check-label {
        color: var(--ember-brown);
        text-decoration: none;
        font-weight: 500;
        border-bottom: 1px dashed rgba(127, 49, 0, 0.4);
        display: inline-block;
        padding: 8px 2px;
    }

    .forgot-link:hover {
        color: var(--deep-forest);
    }

    /* login button - static but interactive */
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

    .login-btn:active {
        transform: translateY(1px);
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

    hr {
        margin: 1rem 0 0.2rem;
        border: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, #FE914B40, #0A905140, transparent);
    }

    /* simple message box for demo */
    .demo-message {
        margin-top: 1rem;
        padding: 0.6rem;
        background: #FEF1E6;
        border-radius: 1.2rem;
        font-size: 0.75rem;
        text-align: center;
        color: #7F3100;
        border-left: 3px solid #FE914B;
        transition: 0.2s;
    }
</style>