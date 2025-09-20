<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Farmers Network Admin - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-white: #FFFFFF;
            --whatsapp-green: #25D366;
            --whatsapp-dark-green: #128C7E;
            --text-dark: #333333;
            --text-light: #666666;
            --background-light: #F8F9FA;
            --gradient-green: linear-gradient(135deg, var(--whatsapp-dark-green) 0%, var(--whatsapp-green) 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--background-light);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            max-width: 500px;
            width: 100%;
            padding: 0 10px;
        }

        .login-card {
            background: var(--primary-white);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid #ddd;
        }

        .login-header {
            background: var(--gradient-green);
            padding: 30px 20px;
            text-align: center;
            color: white;
        }

        .login-logo {
            font-size: 3rem;
            color: white;
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .login-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .login-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .login-body {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
            display: block;
        }

        .form-control-custom {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control-custom:focus {
            border-color: var(--whatsapp-green);
            outline: none;
            background: white;
            box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.1);
        }

        .btn-login {
            width: 100%;
            background: var(--whatsapp-green);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-login:hover {
            background: var(--whatsapp-dark-green);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(18, 140, 126, 0.3);
        }

        .demo-info {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin-top: 25px;
        }

        .demo-title {
            color: var(--text-dark);
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .demo-title i {
            color: var(--whatsapp-green);
            margin-right: 8px;
        }

        .demo-credentials {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 8px;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .demo-credentials strong {
            color: var(--text-dark);
        }

        .security-notice {
            text-align: center;
            margin-top: 25px;
            padding: 15px;
            background: rgba(37, 211, 102, 0.1);
            border-radius: 8px;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .security-notice i {
            color: var(--whatsapp-green);
            margin-right: 5px;
        }

        .login-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        /* Back to home link */
        .back-home {
            position: absolute;
            top: 20px;
            left: 20px;
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            transition: color 0.3s ease;
        }

        .back-home:hover {
            color: var(--whatsapp-green);
        }

        .back-home i {
            margin-right: 8px;
        }

        /* Alert styles */
        .alert-custom {
            border-radius: 12px;
            border: 1px solid;
            margin-bottom: 20px;
        }

        .alert-success-custom {
            background: rgba(37, 211, 102, 0.1);
            border-color: var(--whatsapp-green);
            color: var(--whatsapp-dark-green);
        }

        .alert-danger-custom {
            background: rgba(220, 53, 69, 0.1);
            border-color: #dc3545;
            color: #721c24;
        }

        /* Logo and branding */
        .brand-logo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            z-index: -1;
            opacity: 0.05;
        }

        .brand-logo i {
            font-size: 8rem;
            color: var(--text-dark);
        }
    </style>
</head>
<body>
    <!-- Background Brand Logo -->
    <div class="brand-logo">
        <i class="fas fa-leaf"></i>
    </div>

    <!-- Back to home link -->
    <a href="{{ route('home') }}" class="back-home">
        <i class="fas fa-arrow-left"></i> Back to Home
    </a>

    <!-- Main Login Container -->
    <div class="login-container">
        <div class="login-card">
            <!-- Header Section -->
            <div class="login-header">
                
                <h4 class="login-title">Farmers Network Admin</h4>
                
            </div>

            <!-- Form Section -->
            <div class="login-body">
                <!-- Success Message -->
                @if (session('success'))
                    <div class="alert-custom alert-success-custom">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Error Message -->
                @if (session('error'))
                    <div class="alert-custom alert-danger-custom">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.submit') }}">
                    @csrf

                    <!-- Email Field -->
                    <div class="form-group">
                        <label class="form-label" for="email">
                            <i class="fas fa-envelope me-2"></i>Email Address
                        </label>
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               class="form-control-custom @error('email') is-invalid @enderror"
                               placeholder="Enter your admin email"
                               required
                               autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="form-group">
                        <label class="form-label" for="password">
                            <i class="fas fa-lock me-2"></i>Password
                        </label>
                        <input type="password"
                               id="password"
                               name="password"
                               class="form-control-custom @error('password') is-invalid @enderror"
                               placeholder="Enter your admin password"
                               required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="form-group">
                        <button type="submit" class="btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i>Access Admin Panel
                        </button>
                    </div>
                </form>

               
            </div>
        </div>

        <!-- Footer -->
        <div class="login-footer">
            <p>&copy; {{ date('Y') }} Farmers Network. Admin Portal.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
