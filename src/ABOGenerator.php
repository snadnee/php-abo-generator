<?php

namespace Snadnee\ABOGenerator;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Snadnee\ABOGenerator\Models\File;
use Snadnee\ABOGenerator\Services\ABOService;

class ABOGenerator
{
    const HEADER = 'UHL1';
    const PAYMENT = 1501;
    const INKASO = 1502;

    /**
     * @var File[]
     */
    private array $files = [];
    private string $organization;
    private string $date;
    private int $senderNumber = 0;
    private ?string $fixedKeyPart = null;
    private ?string $securityCode = null;

    public function __construct($organization = '')
    {
        $this->setOrganizationName($organization);
        $this->setDate();
    }

    /**
     *
     * Set the organization name. Less then 20 chars.
     * @param string|null $organization
     * @return ABOGenerator
     */
    public function setOrganizationName(?string $organization): self
    {
        $organization = $organization ?: '';

        $this->organization = Str::limit($organization, 20, '');

        return $this;
    }

    /**
     * Set the Fixed key part and security code. These are optional and mostly unnecessary parts.
     *
     * @param string $fixed - 6 numbers
     * @param string $securityCode - 6 numbers
     * @return ABOGenerator
     */
    public function setSecurityKey(string $fixed, string $securityCode): self
    {
        $this->fixedKeyPart = $fixed;
        $this->securityCode = $securityCode;

        return $this;
    }

    /**
     * Set date of file.
     *
     * @param string|null $date format DDMMYY
     * @return ABOGenerator
     */
    public function setDate(?string $date = null): self
    {
        if ($date == null) {
            $date = Carbon::now()->format('dmy');
        }

        $this->date = $date;

        return $this;
    }

    /**
     * Set sender number. Optional.
     *
     * @param string $number
     * @return $this
     */
    public function setSenderNumber(string $number): self
    {
        $this->senderNumber = $number;

        return $this;
    }

    public function addFile($type = ABOGenerator::PAYMENT): File
    {
        $file = new File($type);
        $this->files[] = $file;
        $file->setFileNumber(count($this->files));

        return $file;
    }

    /**
     * Get the account files.
     *
     * @return File[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Generate ABO string.
     *
     * @return string
     */
    public function generate(): string
    {
        $result = Str::of(
            sprintf(
                '%s%s% -20s%010d%03d%03d',
                self::HEADER,
                $this->date,
                $this->organization,
                $this->senderNumber,
                1,
                1 + count($this->files)
            )
        );

        if ($this->securityCode) {
            $result = $result->append(sprintf('%06d%06d', $this->fixedKeyPart, $this->securityCode));
        }
        $result = $result->append("\r\n");

        foreach ($this->files as $file) {
            $result = $result->append($file->generate());
        }

        return (string) $result;
    }

    /**
     * @param array{bankAccount: string, organizationName?: string} $senderParams
     * @param array{dueDate: string|Carbon, payments: array{amount: int|float, bankAccount: string, variableSymbol?: string, message?: string, constantSymbol?: string, specificSymbol?: string}}[] $paymentGroups
     * @return string
     */
    public function simpleGenerating(array $senderParams, array $paymentGroups): string
    {
        ['prefix' => $senderAccountPrefix, 'number' => $senderAccountNumber, 'bankCode' => $senderAccountBankCode] =
            ABOService::parseAccountNumber($senderParams['bankAccount']);

        $ABOGenerator = new ABOGenerator($senderParams['organizationName'] ?? null);

        $file = $ABOGenerator->addFile();
        $file->setSenderBank($senderAccountBankCode);

        foreach ($paymentGroups as $paymentGroup) {
            $group = $file->addGroup();
            $group->setSenderAccount($senderAccountNumber, $senderAccountPrefix)
                ->setDate($paymentGroup['dueDate']);

            foreach ($paymentGroup['payments'] as $payment) {
                $group->addPayment($payment['bankAccount'], $payment['amount'], $payment['variableSymbol'] ?? null)
                    ->setConstantSymbol($payment['constantSymbol'] ?? null)
                    ->setSpecificSymbol($payment['specificSymbol'] ?? null)
                    ->setPayeeName($payment['payeeName'] ?? null)
                    ->setMessage($payment['message'] ?? null);
            }
        }

        return $ABOGenerator->generate();
    }
}
