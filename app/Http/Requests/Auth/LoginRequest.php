<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\LoginHistory;
use App\Models\ActiveSession;
use App\Models\User;
use App\Services\BackendMetricsService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $email = $this->string('email')->toString();
        $user = User::query()->where('email', $email)->first();
        [$browser, $platform, $device] = $this->parseUserAgent();

        if (! Auth::attempt(
            $this->only('email', 'password'),
            $this->boolean('remember')
        )) {
            LoginHistory::create([
                'user_id' => $user?->id,
                'login_at' => now(),
                'ip_address' => $this->ip(),
                'browser' => $browser,
                'platform' => $platform,
                'device' => $device,
                'successful_login' => false,
                'failure_reason' => 'Invalid credentials',
            ]);
            RateLimiter::hit($this->throttleKey());
            app(BackendMetricsService::class)->forgetDashboardMetrics();

            throw ValidationException::withMessages([
                'email' => 'Invalid email or password.',
            ]);
        }

        if (Auth::user()->is_disabled || Auth::user()->is_draft) {
            LoginHistory::create([
                'user_id' => Auth::id(),
                'login_at' => now(),
                'ip_address' => $this->ip(),
                'browser' => $browser,
                'platform' => $platform,
                'device' => $device,
                'successful_login' => false,
                'failure_reason' => Auth::user()->is_disabled ? 'Disabled user' : 'Draft user',
            ]);

            Auth::logout();
            app(BackendMetricsService::class)->forgetDashboardMetrics();

            throw ValidationException::withMessages([
                'email' => Auth::user()?->is_draft
                    ? 'Your account is still in draft status and cannot log in yet.'
                    : 'Your account has been disabled. Please contact the administrator.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        LoginHistory::create([
            'user_id' => Auth::id(),
            'login_at' => now(),
            'ip_address' => $this->ip(),
            'browser' => $browser,
            'platform' => $platform,
            'device' => $device,
            'successful_login' => true,
        ]);

        app(BackendMetricsService::class)->forgetDashboardMetrics();

        ActiveSession::updateOrCreate(
            ['session_id' => $this->session()->getId()],
            [
                'user_id' => Auth::id(),
                'ip_address' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'last_active_at' => now(),
            'logout_at' => null,
        ]
        );
    }

    private function parseUserAgent(): array
    {
        $ua = strtolower($this->userAgent() ?? '');
        $browser = str_contains($ua, 'firefox') ? 'Firefox'
            : (str_contains($ua, 'edg') ? 'Edge'
            : (str_contains($ua, 'chrome') ? 'Chrome'
            : (str_contains($ua, 'safari') ? 'Safari' : 'Unknown')));

        $platform = str_contains($ua, 'windows') ? 'Windows'
            : (str_contains($ua, 'mac os') || str_contains($ua, 'macintosh') ? 'macOS'
            : (str_contains($ua, 'linux') ? 'Linux'
            : (str_contains($ua, 'android') ? 'Android'
            : (str_contains($ua, 'iphone') || str_contains($ua, 'ipad') ? 'iOS' : 'Unknown'))));

        $device = str_contains($ua, 'mobile') ? 'Mobile' : 'Desktop';

        return [$browser, $platform, $device];
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
