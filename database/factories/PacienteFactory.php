<?php

namespace Database\Factories;

use App\Models\Paciente;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class PacienteFactory extends Factory
{
    protected $model = Paciente::class;

    public function definition()
    {
        return [
            'nome' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'cpf' => $this->faker->numerify('###########'),
            'password' => Hash::make('senha123'),
            'telefone' => $this->faker->phoneNumber,
            'endereco' => $this->faker->address,
            'data_nascimento' => $this->faker->date(),
            'sexo' => $this->faker->randomElement(['M', 'F']),
            'estado_civil' => $this->faker->randomElement(['solteiro', 'casado', 'divorciado', 'viuvo']),
            'profissao' => $this->faker->jobTitle
        ];
    }
}
