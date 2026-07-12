<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lampegan Motor Apps - Masuk</title>
    @filamentStyles
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            position: relative;
            overflow: hidden;
        }

        /* Background layer */
        .bg-layer {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: -2;
            background: #1a1a2e;
            transition: opacity 0.5s ease;
        }

        .bg-layer img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.35;
        }

        /* Overlay gradient */
        .bg-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: -1;
            background: linear-gradient(135deg, rgba(15,23,42,0.85) 0%, rgba(30,41,59,0.75) 50%, rgba(15,23,42,0.9) 100%);
        }

        /* Login card */
        .login-card {
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
            border: 1px solid rgba(255,255,255,0.1);
            position: relative;
            z-index: 1;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 8px;
        }

        .login-logo img {
            height: 60px;
            border-radius: 8px;
        }

        .login-title {
            text-align: center;
            color: #f8fafc;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .login-subtitle {
            text-align: center;
            color: #94a3b8;
            font-size: 0.95rem;
            margin-bottom: 28px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            color: #cbd5e1;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 6px;
        }

        .form-group label .required {
            color: #f87171;
            margin-left: 2px;
        }

        .form-group input[type="email"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.3);
            border-radius: 8px;
            color: #f1f5f9;
            font-size: 0.95rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        .form-group input:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
        }

        .form-group input::placeholder {
            color: #64748b;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 44px;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .remember-row input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #f59e0b;
        }

        .remember-row label {
            color: #cbd5e1;
            font-size: 0.875rem;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #1a1a2e;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Error message */
        .error-message {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 0.875rem;
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 20px;
            color: #64748b;
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <!-- Background Image Layer -->
    <div class="bg-layer" id="bgLayer">
        <img id="bgImage" src="" alt="" onload="this.style.opacity=0.35" onerror="this.style.display='none'">
    </div>
    <div class="bg-overlay"></div>

    <!-- Login Card -->
    <div class="login-card">
        <div class="login-logo">
            <img src="{{ asset('Images/logo/lampeganmtrbdg.jpg') }}" alt="Lampegan Motor" onerror="this.style.display='none'">
        </div>
        <h1 class="login-title">Lampegan Motor Apps</h1>
        <p class="login-subtitle">Masuk ke akun Anda</p>

        @if ($errors->any())
            <div class="error-message">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form wire:submit="authenticate">
            <div class="form-group">
                <label>Alamat email<span class="required">*</span></label>
                <input type="email" wire:model="data.email" placeholder="email@contoh.com" required autofocus>
            </div>

            <div class="form-group">
                <label>Kata sandi<span class="required">*</span></label>
                <div class="password-wrapper">
                    <input type="password" wire:model="data.password" placeholder="••••••••" id="password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="remember-row">
                <input type="checkbox" wire:model="data.remember" id="remember">
                <label for="remember">Ingat saya</label>
            </div>

            <button type="submit" class="btn-login" wire:loading.attr="disabled" wire:loading.class="opacity-50">
                <span wire:loading.remove wire:target="authenticate">Masuk</span>
                <span wire:loading wire:target="authenticate">Memproses...</span>
            </button>
        </form>

        <div class="login-footer">
            © {{ date('Y') }} Lampegan Motor Bandung
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        }

        // Automotive images - curated free images from Unsplash CDN
        // Changes daily based on day of year
        const automotiveImages = [
            'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?w=1920&q=60&auto=format',  // motorcycle road
            'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=1920&q=60&auto=format',  // motorcycle dashboard
            'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=1920&q=60&auto=format',  // motorcycle speed
            'https://images.unsplash.com/photo-1558618047-3c8c76a45081?w=1920&q=60&auto=format',  // motorcycle close
            'https://images.unsplash.com/photo-1609630875171-b1321377ee65?w=1920&q=60&auto=format',  // motorcycle garage
            'https://images.unsplash.com/photo-1580310614729-ccd69652491d?w=1920&q=60&auto=format',  // motorcycle workshop
            'https://images.unsplash.com/photo-1591637333184-19aa844564d3?w=1920&q=60&auto=format',  // motorcycle night
            'https://images.unsplash.com/photo-1525160442909-4c4bf0eb3c69?w=1920&q=60&auto=format',  // motorcycle mountain
            'https://images.unsplash.com/photo-1571008887538-b36bb32f4571?w=1920&q=60&auto=format',  // motorcycle city
            'https://images.unsplash.com/photo-1590885356249-b5e4db0e7cff?w=1920&q=60&auto=format',  // motorcycle sunset
            'https://images.unsplash.com/photo-1622185135505-2d795003994a?w=1920&q=60&auto=format',  // motorcycle trail
            'https://images.unsplash.com/photo-1564062287727-31c57e02031b?w=1920&q=60&auto=format',  // motorcycle rider
            'https://images.unsplash.com/photo-1596395819066-5c5f8be80b6c?w=1920&q=60&auto=format',  // motorcycle parked
            'https://images.unsplash.com/photo-1615172282427-9a57ef2d142f?w=1920&q=60&auto=format',  // motorcycle race
            'https://images.unsplash.com/photo-1598228723793-52759bba239c?w=1920&q=60&auto=format',  // motorcycle wheel
            'https://images.unsplash.com/photo-1547549082-6bc09f2049ae?w=1920&q=60&auto=format',  // motorcycle adventure
            'https://images.unsplash.com/photo-1580341289255-5b47c98a59dd?w=1920&q=60&auto=format',  // motorcycle chrome
            'https://images.unsplash.com/photo-1591996906080-0c5f8e1b9c4f?w=1920&q=60&auto=format',  // motorcycle classic
            'https://images.unsplash.com/photo-1558979158-65a1eaa08691?w=1920&q=60&auto=format',  // motorcycle touring
            'https://images.unsplash.com/photo-1571068316344-75bc76f77890?w=1920&q=60&auto=format',  // motorcycle helmet
            'https://images.unsplash.com/photo-1603811036447-e53fee1ae6dc?w=1920&q=60&auto=format',  // motorcycle sunset 2
            'https://images.unsplash.com/photo-1558980394-4c7c9299fe96?w=1920&q=60&auto=format',  // motorcycle neon
            'https://images.unsplash.com/photo-1619405399517-d7fce0f13302?w=1920&q=60&auto=format',  // motorcycle rain
            'https://images.unsplash.com/photo-1597007066011-fbd7b2e0b310?w=1920&q=60&auto=format',  // motorcycle garage 2
            'https://images.unsplash.com/photo-1543468939-13af18a64fa7?w=1920&q=60&auto=format',  // motorcycle road 2
            'https://images.unsplash.com/photo-1558980664-769d59546b3d?w=1920&q=60&auto=format',  // motorcycle jump
            'https://images.unsplash.com/photo-1590885356249-b5e4db0e7cff?w=1920&q=60&auto=format',  // motorcycle ride
            'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=1920&q=60&auto=format',  // motorcycle gauges
            'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?w=1920&q=60&auto=format',  // motorcycle travel
            'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=1920&q=60&auto=format',  // motorcycle sport
            'https://images.unsplash.com/photo-1622185135505-2d795003994a?w=1920&q=60&auto=format',  // motorcycle trail 2
            'https://images.unsplash.com/photo-1564062287727-31c57e02031b?w=1920&q=60&auto=format',  // motorcycle rider 2
            'https://images.unsplash.com/photo-1596395819066-5c5f8be80b6c?w=1920&q=60&auto=format',  // motorcycle parked 2
            'https://images.unsplash.com/photo-1615172282427-9a57ef2d142f?w=1920&q=60&auto=format',  // motorcycle race 2
            'https://images.unsplash.com/photo-1598228723793-52759bba239c?w=1920&q=60&auto=format',  // motorcycle wheel 2
            'https://images.unsplash.com/photo-1547549082-6bc09f2049ae?w=1920&q=60&auto=format',  // motorcycle adventure 2
            'https://images.unsplash.com/photo-1580341289255-5b47c98a59dd?w=1920&q=60&auto=format',  // motorcycle chrome 2
            'https://images.unsplash.com/photo-1591996906080-0c5f8e1b9c4f?w=1920&q=60&auto=format',  // motorcycle classic 2
            'https://images.unsplash.com/photo-1558979158-65a1eaa08691?w=1920&q=60&auto=format',  // motorcycle touring 2
            'https://images.unsplash.com/photo-1571068316344-75bc76f77890?w=1920&q=60&auto=format',  // motorcycle helmet 2
        ];

        // Get day of year for daily rotation
        function getDayOfYear() {
            const now = new Date();
            const start = new Date(now.getFullYear(), 0, 0);
            const diff = now - start;
            return Math.floor(diff / (1000 * 60 * 60 * 24));
        }

        // Set background image
        function setDailyBackground() {
            const dayOfYear = getDayOfYear();
            const imageIndex = dayOfYear % automotiveImages.length;
            const imageUrl = automotiveImages[imageIndex];

            const img = document.getElementById('bgImage');
            img.src = imageUrl;
            img.alt = 'Lampegan Motor Background';

            // Fallback: if image fails to load after 5 seconds, hide it gracefully
            setTimeout(() => {
                if (!img.complete || img.naturalHeight === 0) {
                    img.style.display = 'none';
                }
            }, 5000);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', setDailyBackground);
    </script>
</body>
</html>
