<?php

namespace Tests\Unit;

use App\Rules\CpfValidation;
use PHPUnit\Framework\TestCase;

class CpfValidationTest extends TestCase
{
    /**
     * @test
     */
    public function valida_cpf_valido()
    {
        $rule = new CpfValidation();
        $this->assertTrue($rule->passes('cpf', '123.456.789-09'));
        $this->assertTrue($rule->passes('cpf', '12345678909'));
    }

    /**
     * @test
     */
    public function rejeita_cpf_invalido()
    {
        $rule = new CpfValidation();
        $this->assertFalse($rule->passes('cpf', '111.111.111-11'));
        $this->assertFalse($rule->passes('cpf', '123'));
        $this->assertFalse($rule->passes('cpf', 'abcdefghijk'));
    }

    /**
     * @test
     */
    public function mensagem_de_erro_correta()
    {
        $rule = new CpfValidation();
        $this->assertEquals('O campo :attribute não é um CPF válido.', $rule->message());
    }
}
