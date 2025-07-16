<?php

namespace Tests\Unit;

use App\Support\Helpers\SecurityMaskHelper;
use Tests\TestCase;

class SecurityMaskHelperTest extends TestCase
{
    public function test_mask_email_with_valid_email()
    {
        $email = 'john.doe@example.com';
        $masked = SecurityMaskHelper::maskEmail($email);

        $this->assertEquals('j***.d**@e******.com', $masked);
    }

    public function test_mask_email_with_short_local_part()
    {
        $email = 'ab@example.com';
        $masked = SecurityMaskHelper::maskEmail($email);

        $this->assertEquals('ab@e******.com', $masked);
    }

    public function test_mask_email_with_invalid_email()
    {
        $email = 'invalid-email';
        $masked = SecurityMaskHelper::maskEmail($email);

        $this->assertEquals('', $masked);
    }

    public function test_mask_phone_with_mobile_format()
    {
        $phone = '(11) 99999-9999';
        $masked = SecurityMaskHelper::maskPhone($phone);

        $this->assertEquals('(11) 9****-9999', $masked);
    }

    public function test_mask_phone_with_landline_format()
    {
        $phone = '(11) 3333-3333';
        $masked = SecurityMaskHelper::maskPhone($phone);

        $this->assertEquals('(11) 3***-3333', $masked);
    }

    public function test_mask_phone_with_numbers_only()
    {
        $phone = '11999999999';
        $masked = SecurityMaskHelper::maskPhone($phone);

        $this->assertEquals('(11) 9****-9999', $masked);
    }

    public function test_mask_document_with_cpf()
    {
        $cpf = '123.456.789-01';
        $masked = SecurityMaskHelper::maskDocument($cpf);

        $this->assertEquals('123.***.***-01', $masked);
    }

    public function test_mask_document_with_cnpj()
    {
        $cnpj = '12.345.678/0001-90';
        $masked = SecurityMaskHelper::maskDocument($cnpj);

        $this->assertEquals('12.***.***/****-90', $masked);
    }

    public function test_mask_document_with_numbers_only()
    {
        $cpf = '12345678901';
        $masked = SecurityMaskHelper::maskDocument($cpf);

        $this->assertEquals('123.***.***-01', $masked);
    }

                    public function test_conditional_mask_always_applies_mask()
    {
        $email = 'test@example.com';
        $masked = SecurityMaskHelper::conditionalMask($email, 'email');

        $this->assertEquals('t***@e******.com', $masked);
    }
}
