<?php

namespace App\Entity;

use App\Repository\CartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'carts')]
    private ?User $user = null;

    #[ORM\Column(type: 'float', options: ['default' => 0])]
    private ?float $totalPrice = 0;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\ManyToMany(targetEntity: Product::class, inversedBy: 'carts')]
    private Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->totalPrice = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$product) {
            throw new \InvalidArgumentException('Le produit fourni est invalide.');
        }
        $this->products->add($product);
        $product->setQuantity($product->getQuantity() ?? 2);

        $this->updateTotalPrice();
        
        return $this;
    }

    public function removeProduct(Product $product): static
    {
        $this->products->removeElement($product);
        $this->updateTotalPrice();

        return $this;
    }

    public function setTotalPrice(?float $totalPrice = null): static
    {
        $this->totalPrice = $totalPrice ?? $this->calculateTotalPrice();
        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }

    /**
     * Calcule le prix total des produits dans le panier.
     */
    private function calculateTotalPrice(): float
    {
        $total = 0;

        foreach ($this->products as $product) {
            $total += $product->getPrice() * $product->getQuantity();
        }

        return $total;
    }

    /**
     * Met à jour le prix total basé sur les produits dans le panier.
     */
    private function updateTotalPrice(): void
    {
        $this->totalPrice = $this->calculateTotalPrice();
    }
}