<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureActiveUser;
use App\Http\Controllers\AuthController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Tests\TestCase;

class EnsureActiveUserTest extends TestCase
{
    public function test_deactivated_authenticated_users_are_logged_out(): void
    {
        $user = new User;
        $user->is_active = false;

        Auth::shouldReceive('user')->once()->andReturn($user);
        Auth::shouldReceive('logout')->once();

        $request = $this->requestWithSession();
        $sessionToken = $request->session()->token();
        $response = app(EnsureActiveUser::class)->handle(
            $request,
            fn () => response('Protected content')
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(route('login'), $response->headers->get('Location'));
        $this->assertNotSame($sessionToken, $request->session()->token());
    }

    public function test_active_authenticated_users_continue_to_the_requested_page(): void
    {
        $user = new User;
        $user->is_active = true;

        Auth::shouldReceive('user')->once()->andReturn($user);

        $response = app(EnsureActiveUser::class)->handle(
            Request::create('/protected'),
            fn () => response('Protected content')
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Protected content', $response->getContent());
    }

    public function test_login_only_attempts_active_accounts(): void
    {
        Auth::shouldReceive('attempt')
            ->once()
            ->with([
                'email' => 'inactive@example.test',
                'password' => 'password',
                'is_active' => true,
            ], false)
            ->andReturn(false);

        $request = $this->requestWithSession();
        $request->merge([
            'email' => 'inactive@example.test',
            'password' => 'password',
        ]);
        $request->setMethod('POST');
        $request->headers->set('referer', '/login');

        $response = app(AuthController::class)->login($request);

        $this->assertSame(302, $response->getStatusCode());
    }

    private function requestWithSession(): Request
    {
        $request = Request::create('/protected');
        $request->setLaravelSession(new Store('test', new ArraySessionHandler(120)));
        $request->session()->start();

        return $request;
    }
}
