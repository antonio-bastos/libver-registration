<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration</title>
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
        .container { max-width: 760px; margin: 50px auto; background: #ffffff; border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 12px 30px rgba(0,0,0,0.08); }
        .header { padding: 18px 28px; border-bottom: 1px solid var(--border); font-family: "Fraunces", serif; }
        form { padding: 24px 28px 32px; }
        h2 { font-size: 16px; margin: 0 0 16px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.6px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px 24px; }
        label { font-weight: 600; font-size: 14px; }
        label span { color: #dc2626; }
        input { width: 100%; padding: 10px 12px; border: 1px solid #d6d6d6; border-radius: 8px; }
        .full { grid-column: 1 / -1; }
        .actions { margin-top: 22px; display: flex; justify-content: center; }
        button { padding: 10px 22px; border: none; border-radius: 6px; background: var(--brand); color: #ffffff; font-weight: 600; cursor: pointer; }
        .error { color: #b91c1c; font-size: 14px; margin-bottom: 14px; }
        @media (max-width: 720px) { .grid { grid-template-columns: 1fr; } }
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
        <div class="header">Registration</div>
        <form method="post" action="{{ route('register.submit') }}">
            @csrf

            @if ($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <h2>Input details</h2>
            <div class="grid">
                <div class="full">
                    <label for="email">E-mail address <span>*</span></label>
                    <input id="email" name="email" type="email" required value="{{ old('email') }}">
                </div>
                <div>
                    <label for="password">Password<span>*</span></label>
                    <input id="password" name="password" type="password" required>
                </div>
                <div>
                    <label for="password_confirmation">Password Confirmation <span>*</span></label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required>
                </div>
            </div>

            <h2 style="margin-top:22px;">Personal information</h2>
            <div class="grid">
                <div>
                    <label for="name">Name <span>*</span></label>
                    <input id="name" name="name" type="text" required value="{{ old('name') }}">
                </div>
                <div>
                    <label for="surname">Surname <span>*</span></label>
                    <input id="surname" name="surname" type="text" required value="{{ old('surname') }}">
                </div>
                <div>
                    <label for="phone">Mobile phone <span>*</span></label>
                    <input id="phone" name="phone" type="text" required value="{{ old('phone') }}">
                </div>
                <div>
                    <label for="card_number">No. card <span>*</span></label>
                    <input id="card_number" name="card_number" type="text" required value="{{ old('card_number') }}">
                </div>
                <div class="full">
                    <label for="dob">Date of birth <span>*</span></label>
                    <input id="dob" name="dob" type="date" required value="{{ old('dob') }}">
                </div>
            </div>

            <div class="actions">
                <button type="submit">Registration</button>
            </div>
        </form>
    </div>
</body>
</html>
