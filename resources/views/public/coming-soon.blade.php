<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Source Code Pro -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Source+Code+Pro:wght@300;400;500;600&display=swap" rel="stylesheet">

<title>QUAF Season 09</title>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        width: 100%;
        height: 100vh;
        overflow: hidden;

        background: #BE1E2D;
        color: #ffffff;

        display: flex;
        align-items: center;
        justify-content: center;

        font-family: "Source Code Pro", monospace;
        position: relative;
    }

    .content {
        text-align: center;
        font-family: "Source Code Pro", monospace;
    }

    .line {
        white-space: nowrap;
        overflow: hidden;
        width: 0;
        margin: 0 auto;
        border-right: 2px solid transparent;
    }

    /* QUAF */
    .quaf {
        font-size: clamp(52px, 11vw, 100px);
        font-weight: 400;
        line-height: 1;

        animation:
            typing-quaf 1.2s steps(5) forwards,
            cursor-quaf 0.6s step-end 2;
    }

    /* Season 09 */
    .season {
        margin-top: 14px;

        font-size: clamp(27px, 6vw, 52px);
        font-weight: 300;
        line-height: 1.1;

        animation:
            typing-season 1.8s steps(9) 1.3s forwards,
            cursor-season 0.6s step-end 1.3s 3;
    }

    /* Stay Tuned */
    .stay {
        margin-top: 25px;

        font-size: clamp(25px, 5.5vw, 48px);
        font-weight: 300;
        line-height: 1.1;

        animation:
            typing-stay 2.3s steps(10) 3.2s forwards,
            cursor-stay 0.55s step-end 3.2s infinite;
    }

    /* Portal Login Shortcut (Discreet) */
    .admin-shortcut {
        position: absolute;
        bottom: 24px;
        right: 24px;
        opacity: 0.15;
        transition: opacity 0.3s ease;
        text-decoration: none;
        color: #ffffff;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .admin-shortcut:hover {
        opacity: 0.9;
    }

    /* =========================
       TYPEWRITER ANIMATIONS
       ========================= */

    @keyframes typing-quaf {
        from {
            width: 0;
        }

        to {
            width: 5ch;
        }
    }

    @keyframes typing-season {
        from {
            width: 0;
        }

        to {
            width: 9ch;
        }
    }

    @keyframes typing-stay {
        from {
            width: 0;
        }

        to {
            width: 10ch;
        }
    }

    /* Cursor animations */

    @keyframes cursor-quaf {
        0%, 100% {
            border-right-color: white;
        }

        50% {
            border-right-color: transparent;
        }
    }

    @keyframes cursor-season {
        0%, 100% {
            border-right-color: white;
        }

        50% {
            border-right-color: transparent;
        }
    }

    @keyframes cursor-stay {
        0%, 100% {
            border-right-color: white;
        }

        50% {
            border-right-color: transparent;
        }
    }
</style>
</head>

<body>

<div class="content">
    <div class="line quaf">#QUAF</div>
    <div class="line season">Season 09</div>
    <div class="line stay">Stay Tuned</div>
</div>

<a href="{{ route('login') }}" class="admin-shortcut" title="Portal Login">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
    </svg>
    <span>Login</span>
</a>

</body>
</html>
