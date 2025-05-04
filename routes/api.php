<?php

use App\Http\Controllers\PacienteController;
use App\Http\Controllers\ProdutoController;
use Illuminate\Support\Facades\Route;

Route::apiResource('produtos', ProdutoController::class);

// Rotas de autenticação do paciente
Route::post('/pacientes/register', [PacienteController::class, 'register']);
Route::post('/pacientes/login', [PacienteController::class, 'login']);
