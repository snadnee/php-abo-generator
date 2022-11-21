<?php

namespace Snadnee\ABOGenerator\Services;

use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;

class ABOService
{
    /**
     * Compose account number.
     *
     * @param string $number
     * @param string|null $prefix
     * @param string|null $bankCode
     * @return string
     */
    public static function composeAccountNumber(string $number, string $prefix = null, string $bankCode = null): string
    {
        $result = Str::of('');

        if ($prefix) {
            $result = $result->append(sprintf('%d-', $prefix));
        }
        $result = $result->append(sprintf('%d', $number));

        if ($bankCode) {
            $result = $result->append(sprintf('/%d', $bankCode));
        }

        return (string) $result;
    }

    /**
     * Parse account number into parts.
     *
     * @param string $accountNumber
     * @return array{prefix: null|string, number: null|string, bankCode: null|string}
     */
    #[ArrayShape(['prefix' => 'null|string', 'number' => 'null|string', 'bankCode' => 'null|string'])]
    public static function parseAccountNumber(string $accountNumber): array
    {
        $parsed = [
            'prefix' => null,
            'number' => null,
            'bankCode' => null,
        ];

        $accountNumber = explode('/', $accountNumber);
        $parsed['bankCode'] = $accountNumber[1];

        if (Str::contains($accountNumber[0], '-')) {
            $number = explode('-', $accountNumber[0]);
            $parsed['prefix'] = $number[0];
            $parsed['number'] = $number[1];
        } else {
            $parsed['number'] = $accountNumber[0];
        }

        return $parsed;
    }
}
