<?php

namespace Snadnee\ABOGenerator\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Snadnee\ABOGenerator\Services\ABOService;

class Group
{
    private ?string $accountNumber = null;
    private ?string $accountPrefix = null;

    /**
     * @var Payment[]
     */
    private array $payments = [];
    private ?string $dueDate = null;

    public function __toString(): string
    {
        return $this->generate();
    }

    /**
     * Set date of the execution. If no time is set default is today.
     *
     * @param Carbon|string|null $date
     * @return self
     */
    public function setDate(Carbon|string|null $date = null): self
    {
        if (!$date) {
            $date = Carbon::now()->format('dmy');
        } else {
            $date = Carbon::parse($date)->format('dmy');
        }

        $this->dueDate = $date;

        return $this;
    }

    /**
     * Set the sender account for the full group. The sender account will not be rendered in Payment.
     *
     * @param string $number
     * @param string|null $prefix
     * @return self
     */
    public function setSenderAccount(string $number, ?string $prefix = null): self
    {
        $this->accountNumber = $number;
        $this->accountPrefix = $prefix;

        return $this;
    }

    /**
     * Adds Payment to group and returns it for set up.
     *
     * @param string $accountNumber
     * @param float $amount
     * @param string $variableSymbol
     * @return Payment
     */
    public function addPayment(string $accountNumber, float $amount, string $variableSymbol): Payment
    {
        $payment = new Payment($accountNumber, $amount, $variableSymbol);
        $this->payments[] = $payment;

        return $payment;
    }

    /**
     * Get the amount of group.
     *
     * @return int
     */
    public function getAmount(): int
    {
        $amount = 0;

        foreach ($this->payments as $payment) {
            $amount += $payment->getAmount();
        }

        return $amount;
    }

    /**
     * Generate Group as string
     *
     * @return string
     */
    public function generate(): string
    {
        $result = Str::of('2 ');

        if ($this->accountNumber != null) {
            $result = $result->append(ABOService::composeAccountNumber($this->accountNumber, $this->accountPrefix) . ' ');
        }

        if (!$this->dueDate) {
            $this->setDate();
        }
        $result = $result
            ->append(sprintf('%014d %s', $this->getAmount(), $this->dueDate))
            ->append("\r\n");

        foreach ($this->payments as $payment) {
            $result = $result->append($payment);
        }

        $result = $result->append("3 +\r\n");

        return (string) $result;
    }
}
