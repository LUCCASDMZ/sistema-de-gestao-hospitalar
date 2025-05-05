<?php

namespace Tests\Feature;

use App\Models\Profissional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_profissional_pode_fazer_login()
    {
        // Cria um profissional para teste
        $profissional = Profissional::factory()->create([
            'email' => 'teste@exemplo.com',
            'senha' => bcrypt('senha123')
        ]);

        // Tenta fazer login
        $response = $this->postJson('/api/profissionais/login', [
            'email' => 'teste@exemplo.com',
            'senha' => 'senha123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'profissional' => [
                    'id',
                    'nome',
                    'email'
                ]
            ]);
    }

    public function test_login_com_credenciais_invalidas()
    {
        $response = $this->postJson('/api/profissionais/login', [
            'email' => 'inexistente@exemplo.com',
            'senha' => 'senhaincorreta'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Credenciais inválidas'
            ]);
    }
}
