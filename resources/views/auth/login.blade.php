<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chivo:wght@300;400;600&family=Fraunces:wght@600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f4f6f8;
            --border: #e3e7ee;
            --ink: #1b2333;
            --muted: #6b7280;
            --brand: #2563eb;
        }

        body { font-family: "Chivo", "Trebuchet MS", sans-serif; background: var(--bg); margin: 0; color: var(--ink); }
        header { background: #ffffff; border-bottom: 1px solid var(--border); }
        .nav { max-width: 1100px; margin: 0 auto; padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; }
        .brand { font-family: "Fraunces", serif; font-size: 20px; }
        .nav a { text-decoration: none; color: var(--ink); margin-left: 16px; font-size: 14px; }
        .container { max-width: 520px; margin: 60px auto; background: #ffffff; padding: 32px 40px; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 12px 30px rgba(0,0,0,0.08); }
        h1 { margin: 0 0 18px; font-family: "Fraunces", serif; }
        label { display: block; font-weight: 600; margin: 16px 0 6px; }
        input { width: 100%; padding: 10px 12px; border: 1px solid #d6d6d6; border-radius: 8px; }
        .row { display: flex; align-items: center; justify-content: space-between; margin-top: 14px; font-size: 13px; color: var(--muted); }
        .row label { margin: 0; font-weight: 400; display: flex; align-items: center; gap: 6px; }
        button { margin-top: 18px; padding: 10px 18px; border: none; border-radius: 6px; background: var(--brand); color: #ffffff; font-weight: 600; cursor: pointer; }
        .error { color: #b91c1c; font-size: 14px; margin-top: 8px; }
        .forgot { color: var(--brand); text-decoration: none; }
    </style>
</head>
<body>
    <header>
        <div class="nav">
            <div class="brand">Public Library of Veria</div>
            <div>
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Registration</a>
            </div>
        </div>
    </header>

    <div class="container">
        <h1>Login</h1>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="post" action="{{ route('login.submit') }}">
            @csrf
            <label for="email">E-mail address</label>
            <input id="email" name="email" type="email" required value="{{ old('email') }}">

            <label for="code">Code</label>
            <input id="code" name="code" type="password" required>

            <div class="row">
                <label for="remember">
                    <input id="remember" type="checkbox" name="remember">
                    Stay Connected
                </label>
                <a class="forgot" href="#">I forgot my password</a>
            </div>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
