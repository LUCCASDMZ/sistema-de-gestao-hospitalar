<?php

namespace Tests\Feature;

use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\Profissional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SegurancaTest extends TestCase
{
    use RefreshDatabase;

    protected $profissional1;
    protected $profissional2;
    protected $token1;
    protected $token2;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Cria dois profissionais
        $this->profissional1 = Profissional::factory()->create([
            'email' => 'profissional1@exemplo.com',
            'password' => bcrypt('senha123')
        ]);

        $this->profissional2 = Profissional::factory()->create([
            'email' => 'profissional2@exemplo.com',
            'password' => bcrypt('senha123')
        ]);

        // Faz login com o primeiro profissional
        $response = $this->postJson('/api/profissionais/login', [
            'email' => 'profissional1@exemplo.com',
            'password' => 'senha123'
        ]);
        $this->token1 = $response->json('token');

        // Faz login com o segundo profissional
        $response = $this->postJson('/api/profissionais/login', [
            'email' => 'profissional2@exemplo.com',
            'password' => 'senha123'
        ]);
        $this->token2 = $response->json('token');
    }

    public function test_acesso_rota_protegida_sem_autenticacao()
    {
        $response = $this->getJson('/api/profissional/agenda');
        
        $response->assertStatus(401); // Não autorizado
    }

    public function test_profissional_so_pode_ver_sua_propria_agenda()
    {
        // Cria um paciente
        $paciente = Paciente::factory()->create();

        // Cria uma consulta para o profissional 1
        $consultaProf1 = new Consulta([
            'paciente_id' => $paciente->id,
            'profissional_id' => $this->profissional1->id,
            'data_hora' => now()->addDay(),
            'status' => 'agendada'
        ]);
        $consultaProf1->save();

        // Tenta acessar a agenda com o token do profissional 2
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token2,
            'Accept' => 'application/json'
        ])->getJson('/api/profissional/agenda');

        // Verifica se a consulta do profissional 1 não aparece na agenda do profissional 2
        $response->assertStatus(200);
        
        $responseData = $response->json();
        $this->assertEmpty(array_filter($responseData['data'], function($item) use ($consultaProf1) {
            return $item['id'] == $consultaProf1->id;
        }), 'A consulta do profissional 1 não deveria aparecer na agenda do profissional 2');
    }
}
