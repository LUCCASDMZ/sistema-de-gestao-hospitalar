<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
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
}
