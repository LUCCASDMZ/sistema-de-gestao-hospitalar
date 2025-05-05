<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Consulta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PacienteController extends Controller
{
    /**
     * Register a new patient
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:pacientes',
                'cpf' => 'required|string|max:11|unique:pacientes',
                'password' => 'required|string|min:8',
                'telefone' => 'required|string',
                'endereco' => 'required|string',
                'data_nascimento' => 'required|date',
                'sexo' => 'required|string',
                'estado_civil' => 'required|string',
                'profissao' => 'required|string'
            ]);

            $paciente = Paciente::create([
                'nome' => $validated['nome'],
                'email' => $validated['email'],
                'cpf' => $validated['cpf'],
                'password' => Hash::make($validated['password']),
                'telefone' => $validated['telefone'],
                'endereco' => $validated['endereco'],
                'data_nascimento' => $validated['data_nascimento'],
                'sexo' => $validated['sexo'],
                'estado_civil' => $validated['estado_civil'],
                'profissao' => $validated['profissao']
            ]);

            $token = $paciente->createToken('auth-token')->plainTextToken;

            Log::info('Paciente registrado com sucesso', [
                'paciente_id' => $paciente->id,
                'email' => $paciente->email
            ]);

            return response()->json([
                'message' => 'Paciente registrado com sucesso',
                'token' => $token,
                'paciente' => $paciente->only([
                    'id',
                    'nome',
                    'email',
                    'cpf',
                    'telefone',
                    'endereco',
                    'data_nascimento',
                    'sexo',
                    'estado_civil',
                    'profissao'
                ])
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erro ao registrar paciente', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Erro ao registrar paciente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Authenticate a patient
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            $paciente = Paciente::where('email', $validated['email'])->first();

            if (!$paciente || !Hash::check($validated['password'], $paciente->password)) {
                Log::warning('Tentativa de login inválida', [
                    'email' => $validated['email']
                ]);
                return response()->json([
                    'message' => 'Credenciais inválidas'
                ], 401);
            }

            $token = $paciente->createToken('auth-token')->plainTextToken;

            Log::info('Paciente logado com sucesso', [
                'paciente_id' => $paciente->id
            ]);

            return response()->json([
                'message' => 'Login realizado com sucesso',
                'token' => $token,
                'paciente' => $paciente->only([
                    'id',
                    'nome',
                    'email',
                    'cpf',
                    'telefone',
                    'endereco',
                    'data_nascimento',
                    'sexo',
                    'estado_civil',
                    'profissao'
                ])
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao realizar login', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Erro ao realizar login',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Retorna o histórico de consultas do paciente autenticado
     *
     * @return \Illuminate\Http\Response
     */
    public function historico()
    {
        try {
            $paciente = Auth::user();
            
            $consultas = Consulta::where('paciente_id', $paciente->id)
                ->with('profissional') // Carrega o relacionamento com o profissional
                ->orderBy('data_hora', 'desc')
                ->get(['id', 'profissional_id', 'data_hora', 'observacoes', 'status']);

            // Formata os dados para a resposta
            $historico = $consultas->map(function($consulta) {
                return [
                    'id' => $consulta->id,
                    'data' => $consulta->data_hora->format('d/m/Y'),
                    'hora' => $consulta->data_hora->format('H:i'),
                    'profissional' => $consulta->profissional->nome,
                    'observacoes' => $consulta->observacoes,
                    'status' => $consulta->status
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $historico
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar histórico de consultas', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao buscar histórico de consultas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
