<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Profissional extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'profissionais';

    protected $fillable = [
        'nome',
        'email',
        'cpf',
        'senha',
        'especialidade',
        'crm',
        'telefone',
        'endereco'
    ];

    protected $hidden = [
        'senha',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function consultas()
    {
        return $this->hasMany(Consulta::class);
    }
}
