<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Laravel\Socialite\Two\InvalidStateException;
use Socialite;

class LoginController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('guest')->except('logout');
    }


    /**
     * @return mixed
     */
    public function redirect() {
        return Socialite::driver(config('oauth.driver'))->redirect();
    }


    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback() {
        try {
            /** @var \Laravel\Socialite\Contracts\User $socialite */
            $socialite = Socialite::driver(config('oauth.driver'))->user();
        } catch (InvalidStateException $exception) {
            return redirect(route('auth.redirect'));
        }

        $user = User::where('email', $socialite->getEmail())->first();

        if ($user && $user->disabled) {
            return redirect(route('login'))->with([
                'warning' => 'Account disabled.',
            ]);
        }

        if (!$user) {
            User::create([
                'name' => $socialite->getName(),
                'email' => $socialite->getEmail(),
                'avatar' => $socialite->getAvatar(),
                'password' => str_random(32),
            ]);

            return redirect(route('login'))->with([
                'status' => 'Account created.',
            ]);
        }

        return value(function () use ($user, $socialite) {
            if (!$user->avatar) {
                $user->avatar = $socialite->getAvatar();
                $user->save();
            }

            Auth::guard()->login($user);

            return redirect()->intended(route('repository.index'));
        });
    }


    /**
     * @return string
     */
    public function redirectTo() {
        return route('repository.index');
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function loggedOut(Request $request) {
        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}
