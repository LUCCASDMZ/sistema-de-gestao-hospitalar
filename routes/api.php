<?php

use App\Http\Controllers\PacienteController;
use App\Http\Controllers\ProfissionalController;
use App\Http\Controllers\ConsultaController;
use Illuminate\Support\Facades\Route;

// Rotas de autenticação do paciente
Route::post('/pacientes/register', [PacienteController::class, 'register']);
Route::post('/pacientes/login', [PacienteController::class, 'login']);

// Rotas de autenticação do profissional
Route::post('/profissionais/register', [ProfissionalController::class, 'store']);
Route::post('/profissionais/login', [ProfissionalController::class, 'login']);

// Rotas de consulta (protegidas)
Route::middleware('auth:sanctum')->group(function () {
    // Rotas de consulta
    Route::post('/consultas/agendar', [ConsultaController::class, 'agendar']);
    Route::post('/consultas/cancelar/{id}', [ConsultaController::class, 'cancelar']);
    Route::get('/consultas', [ConsultaController::class, 'listar']);

    // Rota para histórico do paciente
    Route::get('/paciente/historico', [PacienteController::class, 'historico']);
});
