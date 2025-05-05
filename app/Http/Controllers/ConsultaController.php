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
            Log::info('Dados recebidos para agendamento:', $request->all());
            
            $validator = \Validator::make($request->all(), [
                'nome_profissional' => 'required|string',
                'data' => [
                    'required',
                    'date',
                    'after:today',
                    function ($attribute, $value, $fail) {
                        try {
                            $data = \Carbon\Carbon::parse($value);
                            if ($data->isWeekend()) {
                                $fail('Não é possível agendar consultas aos finais de semana.');
                            }
                        } catch (\Exception $e) {
                            $fail('A data fornecida é inválida.');
                        }
                    },
                    function ($attribute, $value, $fail) {
                        try {
                            $dataAgendamento = \Carbon\Carbon::parse($value);
                            $agora = now();
                            $diferencaHoras = $agora->diffInHours($dataAgendamento, false);
                            
                            if ($diferencaHoras < 24) {
                                $fail('O agendamento deve ser feito com pelo menos 24 horas de antecedência.');
                            }
                        } catch (\Exception $e) {
                            $fail('Erro ao validar a data de agendamento.');
                        }
                    },
                ],
                'hora' => [
                    'required',
                    'date_format:H:i',
                    function ($attribute, $value, $fail) {
                        // Verifica se está no horário comercial (8h às 18h)
                        if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $value)) {
                            $fail('O formato do horário é inválido. Use o formato HH:MM.');
                            return;
                        }
                        
                        $hora = (int) explode(':', $value)[0];
                        if ($hora < 8 || $hora >= 18) {
                            $fail('O horário de atendimento é das 8h às 18h.');
                        }
                    },
                ],
                'observacoes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                Log::error('Falha na validação:', $validator->errors()->toArray());
                return response()->json([
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Encontrar o profissional pelo nome (case insensitive)
            $profissional = Profissional::whereRaw('LOWER(nome) = ?', [strtolower($validated['nome_profissional'])])->first();

            if (!$profissional) {
                return response()->json([
                    'message' => 'Profissional não encontrado. Verifique o nome e tente novamente.'
                ], 404);
            }

            // Verificar se já existe consulta agendada para o mesmo horário
            $data_hora = $validated['data'] . ' ' . $validated['hora'];
            
            Log::info('Verificando horário ocupado', [
                'profissional_id' => $profissional->id,
                'data_hora' => $data_hora,
                'data' => $validated['data'],
                'hora' => $validated['hora']
            ]);

            // Verifica se já existe consulta agendada para o mesmo profissional no mesmo horário
            // e que não esteja cancelada
            $dataHoraFormatada = \Carbon\Carbon::parse($data_hora)->format('Y-m-d H:i:00');
            
            $exists = Consulta::where('profissional_id', $profissional->id)
                ->whereRaw("DATE_FORMAT(data_hora, '%Y-%m-%d %H:%i:00') = ?", [$dataHoraFormatada])
                ->where('status', '!=', 'cancelada')
                ->exists();

            Log::info('Resultado da verificação', [
                'exists' => $exists,
                'profissional_id' => $profissional->id,
                'data_hora' => $dataHoraFormatada,
                'data_hora_original' => $data_hora
            ]);

            if ($exists) {
                Log::warning('Tentativa de agendamento em horário ocupado', [
                    'profissional_id' => $profissional->id,
                    'data_hora' => $data_hora,
                    'consultas_existentes' => Consulta::where('profissional_id', $profissional->id)
                        ->where('data_hora', $data_hora)
                        ->get()
                        ->toArray()
                ]);
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
