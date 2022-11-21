<?php

namespace Snadnee\ABOGenerator\Models;

use Illuminate\Support\Str;
use Snadnee\ABOGenerator\ABOGenerator;

class File
{
    private string $number = '';
    private int $type;
    private string $bank = '';
    private string $bankDepartment = '';
    private array $groups = [];

    public function __construct(int $type = ABOGenerator::PAYMENT)
    {
        $this->type = $type;
    }

    public function __toString(): string
    {
        return $this->generate();
    }

    /**
     * Set file number.
     *
     * @param string $number
     * @return self
     */
    public function setFileNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Set type (payment, inkaso).
     *
     * @param int $type
     * @return self
     */
    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set bank code.
     *
     * @param string $bankCode
     * @return self
     */
    public function setSenderBank(string $bankCode): self
    {
        $this->bank = $bankCode;

        return $this;
    }

    /**
     * Set bank department.
     *
     * @param string $departmentCode
     * @return self
     */
    public function setBankDepartment(string $departmentCode): self
    {
        $this->bankDepartment = $departmentCode;

        return $this;
    }

    /**
     * Add group to the file and returns it for set up.
     *
     * @return Group
     */
    public function addGroup(): Group
    {
        $group = new Group();
        $this->groups[] = $group;

        return $group;
    }

    /**
     * Generate file as a string.
     *
     * @return string
     */
    public function generate(): string
    {
        $result = Str::of(sprintf("1 %04d %03d%03d %04d\r\n", $this->type, $this->number, $this->bankDepartment, $this->bank));

        foreach ($this->groups as $group) {
            $result = $result->append($group);
        }
        $result = $result->append("5 +\r\n");

        return (string) $result;
    }
}
