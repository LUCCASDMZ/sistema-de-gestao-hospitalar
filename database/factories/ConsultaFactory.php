<?php

namespace Database\Factories;

use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\Profissional;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConsultaFactory extends Factory
{
    protected $model = Consulta::class;

    public function definition()
    {
        return [
            'paciente_id' => Paciente::factory(),
            'profissional_id' => Profissional::factory(),
            'data_hora' => Carbon::now()->addDays(rand(1, 30))->format('Y-m-d H:i:s'),
            'observacoes' => $this->faker->sentence,
            'status' => 'agendada'
        ];
    }
}
