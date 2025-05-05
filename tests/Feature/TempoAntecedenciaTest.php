<?php

namespace Tests\Feature;

use App\Models\Paciente;
use App\Models\Profissional;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TempoAntecedenciaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar profissional para os testes
        $this->profissional = Profissional::factory()->create([
            'email' => 'profissional@exemplo.com',
            'password' => bcrypt('senha123')
        ]);

        // Criar e autenticar paciente
        $this->paciente = Paciente::factory()->create([
            'nome' => 'Paciente Teste',
            'email' => 'paciente@exemplo.com',
            'cpf' => '123.456.789-00',
            'password' => bcrypt('senha123'),
            'telefone' => '(11) 99999-9999',
            'endereco' => 'Rua Teste, 123',
            'data_nascimento' => '1990-01-01',
            'sexo' => 'M',
            'estado_civil' => 'solteiro',
            'profissao' => 'Analista'
        ]);

        // Fazer login como paciente
        $loginResponse = $this->postJson('/api/pacientes/login', [
            'email' => 'paciente@exemplo.com',
            'password' => 'senha123'
        ]);
        
        $this->token = $loginResponse->json('token');
    }

    /** @test */
    public function nao_deve_aceitar_agendamento_com_menos_de_24h_de_antecedencia()
    {
        // Menos de 24 horas de antecedência
        $data = now()->addHours(23)->format('Y-m-d');
        $hora = now()->addHours(23)->format('H:00');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/consultas/agendar', [
            'nome_profissional' => $this->profissional->nome,
            'data' => $data,
            'hora' => $hora,
            'observacoes' => 'Consulta teste'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['data']);
    }

    /** @test */
    public function deve_aceitar_agendamento_com_mais_de_24h_de_antecedencia()
    {
        // Mais de 24 horas de antecedência
        $data = now()->addDays(2)->format('Y-m-d');
        $hora = '10:00';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('/api/consultas/agendar', [
            'nome_profissional' => $this->profissional->nome,
            'data' => $data,
            'hora' => $hora,
            'observacoes' => 'Consulta teste'
        ]);

        $response->assertStatus(201);
    }
}
