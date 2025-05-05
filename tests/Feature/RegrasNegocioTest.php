<?php

namespace Tests\Feature;

use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\Profissional;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;

class RegrasNegocioTest extends TestCase
{
    use RefreshDatabase;

    protected $profissional;
    protected $token;
    protected $paciente;
    protected function setUp(): void
    {
        parent::setUp();
        
        // Cria um profissional para teste
        $this->profissional = Profissional::factory()->create([
            'email' => 'profissional@exemplo.com',
            'password' => bcrypt('senha123')
        ]);

        // Cria um paciente para teste
        $this->paciente = Paciente::factory()->create([
            'nome' => 'Paciente Teste',
            'email' => 'paciente@exemplo.com',
            'password' => bcrypt('senha123'),
            'cpf' => '12345678901',
            'telefone' => '11999999999',
            'endereco' => 'Rua Teste',
            'data_nascimento' => '1990-01-01',
            'sexo' => 'M',
            'estado_civil' => 'Solteiro',
            'profissao' => 'Testador'
        ]);

        // Faz login como profissional
        $response = $this->postJson('/api/profissionais/login', [
            'email' => 'profissional@exemplo.com',
            'password' => 'senha123'
        ]);
        $this->token = $response->json('token');
    }

    public function test_nao_deve_permitir_agendamento_em_horario_ocupado()
    {
        // Cria um paciente para o teste
        $paciente = Paciente::factory()->create([
            'email' => 'paciente2@exemplo.com',
            'password' => bcrypt('senha123')
        ]);
        
        // Faz login como o paciente
        $response = $this->postJson('/api/pacientes/login', [
            'email' => 'paciente2@exemplo.com',
            'password' => 'senha123'
        ]);
        $pacienteToken = $response->json('token');
        
        // Data e hora para o teste
        $dataHora = now()->addDays(2)->setTime(14, 0, 0);
        
        // Cria uma consulta existente
        $consultaExistente = new Consulta([
            'paciente_id' => $paciente->id,
            'profissional_id' => $this->profissional->id,
            'data_hora' => $dataHora->format('Y-m-d H:i:s'),
            'status' => 'agendada',
            'observacoes' => 'Consulta existente'
        ]);
        $consultaExistente->save();

        // Tenta agendar outra consulta no mesmo horário
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $pacienteToken,
            'Accept' => 'application/json'
        ])->postJson('/api/consultas/agendar', [
            'nome_profissional' => $this->profissional->nome,
            'data' => $dataHora->format('Y-m-d'),
            'hora' => $dataHora->format('H:i'),
            'observacoes' => 'Nova consulta em horário ocupado'
        ]);

        // Verifica se a resposta indica que o horário está ocupado
        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Horário já está ocupado'
            ]);
    }

    public function test_apenas_paciente_pode_cancelar_sua_consulta()
    {
        // Cria um novo paciente para o teste
        $paciente = Paciente::factory()->create([
            'email' => 'outropaciente@exemplo.com',
            'password' => bcrypt('senha123')
        ]);

        // Faz login como o paciente
        $response = $this->postJson('/api/pacientes/login', [
            'email' => 'outropaciente@exemplo.com',
            'password' => 'senha123'
        ]);
        $pacienteToken = $response->json('token');

        // Cria uma consulta para o paciente
        $consulta = new Consulta([
            'paciente_id' => $paciente->id,
            'profissional_id' => $this->profissional->id,
            'data_hora' => now()->addDay(),
            'status' => 'agendada',
            'observacoes' => 'Consulta para cancelamento'
        ]);
        $consulta->save();

        // Tenta cancelar sem autenticar
        $response = $this->withHeaders([
            'Accept' => 'application/json'
        ])->postJson("/api/consultas/cancelar/{$consulta->id}");

        $response->assertStatus(401); // Não autorizado

        // Tenta cancelar autenticado como o paciente
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $pacienteToken,
            'Accept' => 'application/json'
        ])->postJson("/api/consultas/cancelar/{$consulta->id}");

        $response->assertStatus(200); // Sucesso
    }
}
