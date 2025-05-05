<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consulta extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'paciente_id',
        'profissional_id',
        'data_hora',
        'observacoes',
        'status'
    ];

    protected $casts = [
        'data_hora' => 'datetime',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function profissional()
    {
        return $this->belongsTo(Profissional::class);
    }
}
