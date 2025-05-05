<?php

namespace Tests\Feature;

use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\Profissional;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar um profissional de teste
        $this->profissional = Profissional::factory()->create([
            'email' => 'profissional@exemplo.com',
            'password' => bcrypt('senha123')
        ]);

        // Fazer login e obter o token
        $response = $this->postJson('/api/profissionais/login', [
            'email' => 'profissional@exemplo.com',
            'password' => 'senha123'
        ]);

        $this->token = $response->json('token');
    }

    public function test_agendar_consulta()
    {
        // Criar e autenticar um paciente
        $paciente = Paciente::factory()->create([
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
        
        $token = $loginResponse->json('token');
        
        // Garantir que a data seja pelo menos 24 horas no futuro
        $data = Carbon::now()->addDays(2)->format('Y-m-d');
        $hora = '14:00';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->postJson('/api/consultas/agendar', [
            'nome_profissional' => $this->profissional->nome,
            'data' => $data,
            'hora' => $hora,
            'observacoes' => 'Consulta de rotina'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'observacoes',
                'status'
            ]);

        $this->assertDatabaseHas('consultas', [
            'paciente_id' => $paciente->id,
            'profissional_id' => $this->profissional->id,
            'status' => 'agendada'
        ]);
    }

    public function test_visualizar_agenda_do_profissional()
    {
        // Criar e autenticar um paciente
        $paciente = Paciente::factory()->create([
            'nome' => 'Paciente Teste 2',
            'email' => 'paciente2@exemplo.com',
            'cpf' => '987.654.321-00',
            'password' => bcrypt('senha123'),
            'telefone' => '(11) 98888-8888',
            'endereco' => 'Avenida Teste, 456',
            'data_nascimento' => '1985-05-15',
            'sexo' => 'F',
            'estado_civil' => 'casado',
            'profissao' => 'Engenheira'
        ]);
        
        // Criar consulta de teste
        $consulta = new Consulta([
            'paciente_id' => $paciente->id,
            'profissional_id' => $this->profissional->id,
            'data_hora' => now()->addDay(),
            'observacoes' => 'Consulta de teste',
            'status' => 'agendada'
        ]);
        $consulta->save();

        // Fazer login como profissional para ver a agenda
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->getJson('/api/profissional/agenda');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'data',
                        'hora',
                        'paciente',
                        'observacoes',
                        'status'
                    ]
                ]
            ]);
    }
}
