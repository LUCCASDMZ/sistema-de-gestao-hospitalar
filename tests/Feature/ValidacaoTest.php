<?php

namespace Tests\Feature;

use App\Models\Paciente;
use App\Models\Profissional;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidacaoTest extends TestCase
{
    use RefreshDatabase;

    protected $profissional;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->profissional = Profissional::factory()->create([
            'email' => 'profissional@exemplo.com',
            'password' => bcrypt('senha123')
        ]);

        $response = $this->postJson('/api/profissionais/login', [
            'email' => 'profissional@exemplo.com',
            'password' => 'senha123'
        ]);

        $this->token = $response->json('token');
    }

    public function test_campos_obrigatorios_agendamento()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/consultas/agendar', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'nome_profissional',
                'data',
                'hora'
            ]);
    }

    public function test_formato_data_hora_invalido()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/consultas/agendar', [
            'nome_profissional' => $this->profissional->nome,
            'data' => '2023-13-32', // Data inválida
            'hora' => '25:61',      // Hora inválida
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'data',
                'hora'
            ]);
    }
}
