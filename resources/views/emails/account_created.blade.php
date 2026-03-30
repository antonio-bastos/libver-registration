<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.5;">
    <h2 style="margin-bottom: 12px;">Welcome to Public Library of Veria</h2>
    <p>Hello {{ trim($user->name . ' ' . $user->surname) ?: 'there' }},</p>
    <p>Your account was created successfully.</p>
    <p>You can now sign in and register for activities from your dashboard.</p>
    <p style="margin-top: 18px;">Thank you,<br>Public Library of Veria</p>
</body>
</html>

