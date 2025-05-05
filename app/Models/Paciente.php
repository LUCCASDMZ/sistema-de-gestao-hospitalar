<?php

namespace App\Models;

use App\Rules\CpfValidation;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\HasApiTokens;

class Paciente extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nome',
        'email',
        'cpf',
        'password',
        'telefone',
        'endereco',
        'data_nascimento',
        'sexo',
        'estado_civil',
        'profissao'
    ];

    /**
     * Valida os dados do paciente.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public static function validate(array $data)
    {
        return Validator::make($data, [
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:pacientes,email'],
            'cpf' => ['required', 'string', 'unique:pacientes,cpf', new CpfValidation],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'telefone' => ['required', 'string', 'max:20'],
            'endereco' => ['required', 'string', 'max:255'],
            'data_nascimento' => ['required', 'date', 'before:today'],
            'sexo' => ['required', 'string', 'in:M,F,O'],
            'estado_civil' => ['required', 'string', 'in:Solteiro,Casado,Divorciado,Viúvo,Separado'],
            'profissao' => ['required', 'string', 'max:100']
        ]);
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
