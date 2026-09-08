<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('id="email"', false);
        $response->assertSee('id="password"', false);
        $response->assertSee('id="login-as-staff"', false);
        $response->assertSee('id="login-as-student"', false);
        $response->assertSee('id="student_id"', false);
        $response->assertSee('Show Student ID');
        $response->assertDontSee('x-data', false);

        $html = $response->getContent();
        $this->assertLessThan(
            strpos($html, 'id="student_id"'),
            strpos($html, 'id="last_name"'),
            'Last name should appear above Student ID on the student login form.'
        );
        $this->assertMatchesRegularExpression('/id="student_id"[^>]*type="password"|type="password"[^>]*id="student_id"/', $html);
    }

    public function test_login_screen_keeps_student_tab_after_validation_error(): void
    {
        $this->from('/login')
            ->followingRedirects()
            ->post('/login', [
                'login_as' => 'student',
                'student_id' => '',
                'last_name' => '',
            ])
            ->assertSee('id="login-as-student"', false)
            ->assertSee('name="login_as" value="student"', false);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Faculty');

        $response = $this->post('/login', [
            'login_as' => 'staff',
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.login',
            'user_id' => $user->id,
        ]);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Faculty');

        $this->post('/login', [
            'login_as' => 'staff',
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.login_failed',
        ]);
    }

    public function test_students_can_authenticate_with_student_id_and_last_name(): void
    {
        $user = User::factory()->create([
            'employee_id' => 'STU-100',
            'last_name' => 'Santos',
        ]);
        $user->assignRole('Student');

        $response = $this->post('/login', [
            'login_as' => 'student',
            'student_id' => 'STU-100',
            'last_name' => 'Santos',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_students_cannot_authenticate_with_email_password(): void
    {
        $user = User::factory()->create([
            'employee_id' => 'STU-101',
            'last_name' => 'Reyes',
            'password' => 'password',
        ]);
        $user->assignRole('Student');

        $this->post('/login', [
            'login_as' => 'staff',
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
