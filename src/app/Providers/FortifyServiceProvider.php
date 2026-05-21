<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use Laravel\Fortify\Http\Requests\RegisterRequest as FortifyRegisterRequest;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Contracts\LoginResponse;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;




class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FortifyLoginRequest::class, LoginRequest::class);
        $this->app->bind(FortifyRegisterRequest::class, RegisterRequest::class);

        $this->app->singleton(RegisterResponse::class, function() {
            return new class implements RegisterResponse {
                public function toResponse($request)
                {
                    return redirect()->route('verification.notice');
                }
            };
        });

        $this->app->singleton(LoginResponse::class, function () {
            return new class implements LoginResponse {
                public function toResponse($request)
                {
                    if ($request->guard === 'admin') {
                        return redirect('/admin/attendance/list');
                    }
                    return redirect('/attendance');
                }
            };
        });

        $this->app->singleton(LogoutResponse::class, function() {
            return new class implements LogoutResponse {
                public function toResponse($request)
                {
                    if ($request->guard === 'admin') {
                        return redirect('/admin/login');
                    }
                    return redirect('/login');
                }
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(function () {
            return view('auth.register');
        });

        Fortify::loginView(function () {
            if (request()->is('admin/login')) {
            return view('admin.login');
            }
            return view('auth.login');
        });

        Fortify::authenticateUsing(function (Request $request) {
            if ($request->guard === 'admin') {
                $user = User::where('email', $request->email)
                    ->where('role', 'admin')
                    ->first();

                if ($user && Hash::check($request->password, $user->password)) {
                    Auth::guard('admin')->login($user);

                    return $user;
                }
                return null;
            }

            $user = User::where('email', $request->email)
                ->where('role', 'user')
                ->first;

            if ($user && Hash::check($request->password, $user->password)) {
                return $user;
            }
            return null;
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });
    }
}
