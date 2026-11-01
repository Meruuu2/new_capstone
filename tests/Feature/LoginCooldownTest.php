<?php

namespace Tests\Feature;

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginCooldownTest extends TestCase
{
    public function test_five_failed_attempts_block_the_ip_for_thirty_minutes(): void
    {
        Auth::shouldReceive('attempt')->times(5)->andReturn(false);

        foreach (range(1, 5) as $_) {
            $response = app(AuthController::class)->login($this->loginRequest('192.0.2.10'));
            $this->assertSame(302, $response->getStatusCode());
        }

        $lockoutKey = 'login-cooldown:192.0.2.10:lockout';
        $this->assertTrue(RateLimiter::tooManyAttempts($lockoutKey, 1));
        $this->assertGreaterThan(0, RateLimiter::availableIn($lockoutKey));
        $this->assertLessThanOrEqual(1800, RateLimiter::availableIn($lockoutKey));

        $response = app(AuthController::class)->login($this->loginRequest('192.0.2.10'));
        $this->assertSame(302, $response->getStatusCode());
    }

    public function test_lockout_applies_to_the_failing_ip_not_other_ips(): void
    {
        Auth::shouldReceive('attempt')->times(6)->andReturn(false);

        foreach (range(1, 5) as $_) {
            app(AuthController::class)->login($this->loginRequest('192.0.2.10'));
        }

        $response = app(AuthController::class)->login($this->loginRequest('192.0.2.11'));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertTrue(RateLimiter::tooManyAttempts('login-cooldown:192.0.2.10:lockout', 1));
        $this->assertFalse(RateLimiter::tooManyAttempts('login-cooldown:192.0.2.11:lockout', 1));
    }

    public function test_an_ip_can_attempt_login_again_after_the_lockout_expires(): void
    {
        Auth::shouldReceive('attempt')->times(6)->andReturn(false);

        foreach (range(1, 5) as $_) {
            app(AuthController::class)->login($this->loginRequest('192.0.2.10'));
        }

        $this->travel(31)->minutes();

        $response = app(AuthController::class)->login($this->loginRequest('192.0.2.10'));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertFalse(RateLimiter::tooManyAttempts('login-cooldown:192.0.2.10:lockout', 1));
    }

    private function loginRequest(string $ip): Request
    {
        $request = Request::create(
            '/login',
            'POST',
            ['email' => 'user@example.test', 'password' => 'incorrect'],
            [],
            [],
            ['REMOTE_ADDR' => $ip],
        );
        $request->setLaravelSession(new Store('test', new ArraySessionHandler(120)));
        $request->session()->start();
        $request->headers->set('referer', '/login');

        return $request;
    }
}
