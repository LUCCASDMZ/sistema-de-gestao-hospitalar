<?php

namespace App\Http\Controllers;

use App\Models\Profissional;
use App\Models\Consulta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ProfissionalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $profissionais = Profissional::all();
            Log::info('Lista de profissionais retornada');
            return response()->json($profissionais);
        } catch (\Exception $e) {
            Log::error('Erro ao listar profissionais', [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'message' => 'Erro ao listar profissionais'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'email' => 'required|string|email|unique:profissionais',
                'cpf' => 'required|string|unique:profissionais',
                'password' => 'required|string|min:8',
                'especialidade' => 'required|string',
                'crm' => 'required|string',
                'telefone' => 'required|string',
                'endereco' => 'required|string'
            ]);

            $profissional = Profissional::create([
                'nome' => $validated['nome'],
                'email' => $validated['email'],
                'cpf' => $validated['cpf'],
                'password' => Hash::make($validated['password']),
                'especialidade' => $validated['especialidade'],
                'crm' => $validated['crm'],
                'telefone' => $validated['telefone'],
                'endereco' => $validated['endereco']
            ]);

            Log::info('Profissional registrado com sucesso', [
                'profissional_id' => $profissional->id
            ]);

            return response()->json([
                'message' => 'Profissional registrado com sucesso',
                'profissional' => $profissional
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erro ao registrar profissional', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Erro ao registrar profissional',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Authenticate a professional
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

            $profissional = Profissional::where('email', $validated['email'])->first();

            if (!$profissional || !Hash::check($validated['password'], $profissional->password)) {
                Log::warning('Tentativa de login inválida', [
                    'email' => $validated['email']
                ]);
                return response()->json([
                    'message' => 'Credenciais inválidas'
                ], 401);
            }

            $token = $profissional->createToken('auth-token')->plainTextToken;

            Log::info('Profissional logado com sucesso', [
                'profissional_id' => $profissional->id
            ]);

            return response()->json([
                'message' => 'Login realizado com sucesso',
                'token' => $token,
                'profissional' => $profissional->only([
                    'id',
                    'nome',
                    'email',
                    'cpf',
                    'especialidade',
                    'crm',
                    'telefone',
                    'endereco'
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
     * Display the specified resource.
     */
    public function show(Profissional $profissional)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Profissional $profissional)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Profissional $profissional)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Profissional $profissional)
    {
        //
    }

    /**
     * Retorna a agenda do profissional autenticado
     *
     * @return \Illuminate\Http\Response
     */
    public function minhaAgenda()
    {
        try {
            $profissional = Auth::user();
            
            $consultas = Consulta::with('paciente')
                ->where('profissional_id', $profissional->id)
                ->orderBy('data_hora', 'asc')
                ->get();

            $agenda = $consultas->map(function($consulta) {
                return [
                    'id' => $consulta->id,
                    'data' => $consulta->data_hora->format('d/m/Y'),
                    'hora' => $consulta->data_hora->format('H:i'),
                    'paciente' => $consulta->paciente->nome,
                    'observacoes' => $consulta->observacoes,
                    'status' => $consulta->status
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $agenda
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar agenda do profissional', [
                'profissional_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao buscar agenda',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
