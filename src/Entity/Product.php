<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $quantity = null;

    #[ORM\Column]
    private bool $realisation = false;

    #[ORM\Column(enumType: ProductUnitEnum::class)]
    private ?ProductUnitEnum $unit = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ShoppingList $shoppingList = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function isRealised(): ?bool
    {
        return $this->realisation;
    }

    public function setRealisation(bool $realisation): static
    {
        $this->realisation = $realisation;

        return $this;
    }

    public function toggleRealisation(): static
    {
        $this->realisation = !$this->realisation;

        return $this;
    }

    public function getUnit(): ?ProductUnitEnum
    {
        return $this->unit;
    }

    public function setUnit(ProductUnitEnum $unit): static
    {
        $this->unit = $unit;

        return $this;
    }

    public function getShoppingList(): ?ShoppingList
    {
        return $this->shoppingList;
    }

    public function setShoppingList(?ShoppingList $shoppingList): static
    {
        $this->shoppingList = $shoppingList;

        return $this;
    }
}
