<?php

namespace App\Services\Payment\Models;

class Customer
{
    protected ?string $firstName;
    protected ?string $lastName;
    protected ?string $email;
    protected ?string $phone;

    public function __construct($firstName, $lastName, $email, $phone = null)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getFullName(): ?string
    {
        return trim($this->firstName.' '.$this->lastName);
    }
}
