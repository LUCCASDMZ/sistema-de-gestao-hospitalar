<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class PacienteController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:pacientes',
            'cpf' => 'required|string|max:11|unique:pacientes',
            'password' => 'required|string|min:8',
        ]);

        $paciente = Paciente::create([
            'nome' => $validated['nome'],
            'email' => $validated['email'],
            'cpf' => $validated['cpf'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $paciente->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Paciente registrado com sucesso',
            'token' => $token,
            'paciente' => $paciente->only(['id', 'nome', 'email', 'cpf'])
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $paciente = Paciente::where('email', $validated['email'])->first();

        if (!$paciente || !Hash::check($validated['password'], $paciente->password)) {
            return response()->json([
                'message' => 'Credenciais inválidas'
            ], 401);
        }

        $token = $paciente->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login realizado com sucesso',
            'token' => $token,
            'paciente' => $paciente->only(['id', 'nome', 'email', 'cpf'])
        ]);
    }
}
