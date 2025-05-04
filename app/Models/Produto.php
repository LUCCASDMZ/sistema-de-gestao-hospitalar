<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produto extends Model
{
    use HasFactory;

    // Adicione os campos que podem ser preenchidos em massa
    protected $fillable = ['nome', 'preco'];
}
