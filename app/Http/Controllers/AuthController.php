<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\MailingListService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    public function register(
        RegisterRequest $request,
        MailingListService $mailingListService,
        NotificationService $notificationService
    ): RedirectResponse
    {
        $data = $request->validated();
        $requestedNewsletter = !empty($data['subscribe_newsletter']);

        $user = User::query()->create([
            'name' => $data['name'],
            'surname' => $data['surname'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_PARENT,
            'phone' => $data['phone'],
            'card_number' => $data['card_number'],
            'dob' => $data['dob'],
            'newsletter_subscribed' => false,
            'newsletter_subscribed_at' => null,
        ]);

        if ($requestedNewsletter) {
            $subscribed = $mailingListService->subscribeUser($user, 'account_registration');
            if ($subscribed) {
                $user->newsletter_subscribed = true;
                $user->newsletter_subscribed_at = now();
                $user->save();
            }
        }

        $notificationService->queueAccountCreated($user);

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
