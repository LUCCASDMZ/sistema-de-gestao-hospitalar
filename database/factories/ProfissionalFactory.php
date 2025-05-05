<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class ProfissionalFactory extends Factory
{
    protected $model = \App\Models\Profissional::class;

    public function definition()
    {
        return [
            'nome' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'cpf' => $this->faker->numerify('###########'),
            'password' => Hash::make('senha123'),
            'especialidade' => $this->faker->jobTitle,
            'crm' => 'CRM/' . $this->faker->stateAbbr . ' ' . $this->faker->randomNumber(6),
            'telefone' => $this->faker->phoneNumber,
            'endereco' => $this->faker->address
        ];
    }
}
