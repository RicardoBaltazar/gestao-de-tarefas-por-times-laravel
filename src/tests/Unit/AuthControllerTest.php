<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_register_with_valid_data()
    {
        $userData = [
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
            'password' => 'senha123',
            'password_confirmation' => 'senha123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Usuário criado com sucesso',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['name', 'email'],
                    'token',
                    'token_type',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'João Silva',
            'email' => 'joao@exemplo.com',
        ]);
    }

    /** @test */
    public function user_cannot_register_with_invalid_data()
    {
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => 'email-invalido',
            'password' => '123',
            'password_confirmation' => '456',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Dados inválidos',
            ])
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /** @test */
    public function user_cannot_register_with_existing_email()
    {
        User::factory()->create(['email' => 'existente@exemplo.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'Novo Usuario',
            'email' => 'existente@exemplo.com',
            'password' => 'senha123',
            'password_confirmation' => 'senha123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'usuario@exemplo.com',
            'password' => Hash::make('senha123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'usuario@exemplo.com',
            'password' => 'senha123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login realizado com sucesso',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['name', 'email'],
                    'token',
                    'token_type',
                ]
            ]);
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'usuario@exemplo.com',
            'password' => Hash::make('senha123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'usuario@exemplo.com',
            'password' => 'senha-errada',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Credenciais inválidas',
            ]);
    }

    /** @test */
    public function user_cannot_login_with_invalid_data()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'email-invalido',
            'password' => '',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Dados inválidos',
            ])
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /** @test */
    public function authenticated_user_can_logout()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout realizado com sucesso',
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_logout()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_user_can_get_profile()
    {
        $user = User::factory()->create([
            'name' => 'Usuario Teste',
            'email' => 'teste@exemplo.com',
        ]);
        
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Usuario Teste',
                    'email' => 'teste@exemplo.com',
                ]
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_get_profile()
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }

    /** @test */
    public function login_returns_bearer_token()
    {
        $user = User::factory()->create([
            'password' => Hash::make('senha123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'senha123',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['token_type' => 'Bearer']);

        $token = $response->json('data.token');
        $this->assertNotEmpty($token);
        $this->assertIsString($token);
    }
}