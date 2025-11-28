<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class CaptchaService
{
    public function generate(string $context = 'default'): string
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);
        $answer = $a + $b;

        Session::put($this->key($context), $answer);

        return "What is {$a} + {$b}?";
    }

    public function validate(?string $value, string $context = 'default'): bool
    {
        $expected = Session::pull($this->key($context));

        if ($expected === null) {
            return false;
        }

        return (int) $value === (int) $expected;
    }

    protected function key(string $context): string
    {
        return "captcha_answer_{$context}";
    }
}

