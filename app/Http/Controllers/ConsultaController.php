<?php

namespace App\Http\Controllers;

use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\Profissional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConsultaController extends Controller
{
    /**
     * Agendar uma nova consulta
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function agendar(Request $request)
    {
        try {
            $validated = $request->validate([
                'nome_profissional' => 'required|string',
                'data' => 'required|date|after:today',
                'hora' => 'required|string|date_format:H:i',
                'observacoes' => 'nullable|string',
            ]);

            // Encontrar o profissional pelo nome (case insensitive)
            $profissional = Profissional::whereRaw('LOWER(nome) = ?', [strtolower($validated['nome_profissional'])])->first();

            if (!$profissional) {
                return response()->json([
                    'message' => 'Profissional não encontrado. Verifique o nome e tente novamente.'
                ], 404);
            }

            // Verificar se já existe consulta agendada para o mesmo horário
            $data_hora = $validated['data'] . ' ' . $validated['hora'];
            $exists = Consulta::where('profissional_id', $profissional->id)
                ->where('data_hora', $data_hora)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Horário já está ocupado'
                ], 400);
            }

            $paciente = auth()->user();

            $consulta = Consulta::create([
                'paciente_id' => $paciente->id,
                'profissional_id' => $profissional->id,
                'data_hora' => $data_hora,
                'observacoes' => $validated['observacoes'] ?? null,
                'status' => 'agendada'
            ]);

            Log::info('Consulta agendada com sucesso', [
                'consulta_id' => $consulta->id,
                'paciente_id' => $paciente->id,
                'profissional_id' => $profissional->id
            ]);

            return response()->json([
                'message' => 'Consulta agendada com sucesso',
                'observacoes' => $consulta->observacoes,
                'status' => $consulta->status
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erro ao agendar consulta', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Erro ao agendar consulta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar uma consulta
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function cancelar($id)
    {
        try {
            $paciente = auth()->user();
            
            $consulta = Consulta::where('id', $id)
                ->where('paciente_id', $paciente->id)
                ->first();

            if (!$consulta) {
                return response()->json([
                    'message' => 'Consulta não encontrada'
                ], 404);
            }

            if ($consulta->status === 'cancelada') {
                return response()->json([
                    'message' => 'Consulta já está cancelada'
                ], 400);
            }

            $consulta->update([
                'status' => 'cancelada',
                'observacoes' => $consulta->observacoes . '\nCancelada pelo paciente em ' . now()
            ]);

            Log::info('Consulta cancelada com sucesso', [
                'consulta_id' => $consulta->id,
                'paciente_id' => $paciente->id
            ]);

            return response()->json([
                'message' => 'Consulta cancelada com sucesso',
                'consulta' => $consulta
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao cancelar consulta', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Erro ao cancelar consulta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar consultas do paciente
     *
     * @return \Illuminate\Http\Response
     */
    public function listar()
    {
        try {
            $paciente = auth()->user();
            
            $consultas = Consulta::where('paciente_id', $paciente->id)
                ->with(['profissional'])
                ->get();

            Log::info('Consultas listadas com sucesso', [
                'paciente_id' => $paciente->id,
                'total_consultas' => $consultas->count()
            ]);

            return response()->json([
                'message' => 'Consultas listadas com sucesso',
                'consultas' => $consultas
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar consultas', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Erro ao listar consultas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
