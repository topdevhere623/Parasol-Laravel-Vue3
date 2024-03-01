<?php

namespace App\Services\Payment\Models;

class Product
{
    public function __construct(
        protected ?string $title,
        protected ?string $reference_id,
        protected ?string $product_id,
        protected ?string $description,
        protected float $price,
        protected float $discount,
        protected float $vat,
        protected int $quantity = 1
    ) {
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getReferenceId(): ?string
    {
        return $this->reference_id;
    }

    public function setReferenceId(?string $reference_id): void
    {
        $this->reference_id = $reference_id;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getDiscount(): float
    {
        return $this->discount;
    }

    public function setDiscount(float $discount): void
    {
        $this->discount = $discount;
    }

    public function getVat(): float
    {
        return $this->vat;
    }

    public function setVat(float $vat): void
    {
        $this->vat = $vat;
    }

    public function getTotalPrice(): float
    {
        return $this->price * $this->quantity;
    }

    public function getProductId(): ?string
    {
        return $this->product_id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }
}
