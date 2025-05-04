<?php

use App\Http\Controllers\PacientesController;
use App\Http\Controllers\ProdutoController;
use Illuminate\Support\Facades\Route;

Route::apiResource('produtos', ProdutoController::class);

Route::apiResource('pacientes', PacientesController::class);


// Route::get('/teste', function () {
//     return response()->json(['mensagem' => 'API funcionando!']);
// });
