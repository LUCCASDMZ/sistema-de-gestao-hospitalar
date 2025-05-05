<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('email')->unique();
            $table->string('cpf')->unique();
            $table->string('password');
            $table->string('telefone');
            $table->string('endereco');
            $table->date('data_nascimento');
            $table->string('sexo');
            $table->string('estado_civil');
            $table->string('profissao');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pacientes');
    }
};
