<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Profissional extends Authenticatable
{
    // Profissional model for hospital management system
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'profissionais';

    protected $fillable = [
        'nome',
        'email',
        'cpf',
        'password',
        'especialidade',
        'crm',
        'telefone',
        'endereco',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function consultas()
    {
        return $this->hasMany(Consulta::class);
    }
}
