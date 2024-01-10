<?php

namespace Snadnee\ABOGenerator\Models;

use Illuminate\Support\Str;
use Snadnee\ABOGenerator\Services\ABOService;

class Payment
{
    private int $amount;
    private ?string $variableSymbol = '';
    private string $bankCode;
    private string $accountNumber;
    private ?string $accountPrefix = '';
    private ?string $specificSymbol = null;
    private ?string $constantSymbol = null;
    private string $message = '';
    private ?string $payeeName = null;

    public function __construct(string $fullAccountNumber, float $amount, string $variableSymbol = '')
    {
        $this->setAccount($fullAccountNumber)
            ->setAmount($amount)
            ->setVariableSymbol($variableSymbol);
    }

    public function __toString(): string
    {
        return $this->generate();
    }

    /**
     * Set payment amount.
     *
     * @param float $amount
     * @param bool $inCents specifies if the amount is in cents
     * @return self
     */
    public function setAmount(float|int $amount, bool $inCents = false): self
    {
        $amount = $inCents ? $amount : $amount * 100;
        $this->amount = (int) round($amount);

        return $this;
    }

    /**
     * Get amount of payment.
     *
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * Set account.
     *
     * @param string $account in full format (xxxx-)xxxxxxxx/xxxx
     * @return self
     */
    public function setAccount(string $account): self
    {
        $account = explode('/', $account);
        $this->bankCode = $account[1];

        if (Str::contains($account[0], '-')) {
            $number = explode('-', $account[0]);
            $this->accountPrefix = $number[0];
            $this->accountNumber = $number[1];
        } else {
            $this->accountNumber = $account[0];
        }

        return $this;
    }

    /**
     * Set variable symbol.
     *
     * @param string|null $variableSymbol
     * @return self
     */
    public function setVariableSymbol(?string $variableSymbol): self
    {
        $this->variableSymbol = $variableSymbol;

        return $this;
    }

    /**
     * Set constant symbol.
     *
     * @param string|null $constantSymbol
     * @return self
     */
    public function setConstantSymbol(?string $constantSymbol): self
    {
        $this->constantSymbol = $constantSymbol;

        return $this;
    }

    /**
     * Set specific symbol.
     *
     * @param string|null $specificSymbol
     * @return self
     */
    public function setSpecificSymbol(?string $specificSymbol): self
    {
        $this->specificSymbol = $specificSymbol;

        return $this;
    }

    /**
     * Set payment message.
     *
     * @param string|array|null $message
     * @return self
     */
    public function setMessage(string|array|null $message): self
    {
        if (is_array($message)) {
            $message = implode(' AV|', $message);
        }
        $this->message = $message;

        return $this;
    }

    public function setPayeeName(?string $payeeName): self
    {
        $this->payeeName = $payeeName;

        return $this;
    }

    /**
     * Generate payment as a string.
     *
     * @return string
     */
    public function generate(): string
    {
        $result = Str::of('');

        $result = $result
            ->append(
                sprintf(
                    '%s %.0f %s %s%04d ',
                    ABOService::composeAccountNumber($this->accountNumber, $this->accountPrefix),
                    $this->amount,
                    $this->variableSymbol,
                    $this->bankCode,
                    $this->constantSymbol
                )
            )
            ->append(($this->specificSymbol ?: ' ') . ' ')
            ->append(($this->message ? mb_substr('AV:' . Str::transliterate($this->message), 0, 38) : ' '))
            ->append(($this->payeeName ? ' ' . mb_substr('NP:' . Str::transliterate($this->payeeName), 0, 35) : ''))
            ->append("\r\n");

        return (string) $result;
    }
}
