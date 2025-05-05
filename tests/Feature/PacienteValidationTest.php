<?php

namespace Tests\Feature;

use App\Models\Paciente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PacienteValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function nao_deve_aceitar_cpf_invalido()
    {
        $response = $this->postJson('/api/pacientes/register', [
            'nome' => 'Paciente Teste',
            'email' => 'paciente@exemplo.com',
            'cpf' => '123', // CPF inválido
            'password' => 'senha123',
            'telefone' => '(11) 99999-9999',
            'endereco' => 'Rua Teste, 123',
            'data_nascimento' => '1990-01-01',
            'sexo' => 'M',
            'estado_civil' => 'solteiro',
            'profissao' => 'Analista'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['cpf']);
    }

    /** @test */
    public function nao_deve_aceitar_email_invalido()
    {
        $response = $this->postJson('/api/pacientes/register', [
            'nome' => 'Paciente Teste',
            'email' => 'email-invalido', // Email inválido
            'cpf' => '123.456.789-09',
            'password' => 'senha123',
            'telefone' => '(11) 99999-9999',
            'endereco' => 'Rua Teste, 123',
            'data_nascimento' => '1990-01-01',
            'sexo' => 'M',
            'estado_civil' => 'solteiro',
            'profissao' => 'Analista'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function nao_deve_aceitar_data_nascimento_futura()
    {
        $dataFutura = now()->addYear()->format('Y-m-d');
        
        $response = $this->postJson('/api/pacientes/register', [
            'nome' => 'Paciente Teste',
            'email' => 'paciente@exemplo.com',
            'cpf' => '123.456.789-09',
            'password' => 'senha123',
            'telefone' => '(11) 99999-9999',
            'endereco' => 'Rua Teste, 123',
            'data_nascimento' => $dataFutura, // Data futura inválida
            'sexo' => 'M',
            'estado_civil' => 'solteiro',
            'profissao' => 'Analista'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['data_nascimento']);
    }
}
