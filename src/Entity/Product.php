<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column]
    private ?bool $highlighted = null;

    #[ORM\Column]
    private ?int $quantity = 0;

    /**
     * @var Collection<int, Cart>
     */
    #[ORM\ManyToMany(targetEntity: Cart::class, mappedBy: 'products')]
    private Collection $carts;

    /**
     * @var Collection<int, ProductSize>
     */
    #[ORM\OneToMany(targetEntity: ProductSize::class, mappedBy: 'product', cascade: ['persist', 'remove'])]
    private Collection $productSizes;

    public function __construct()
    {
        $this->carts = new ArrayCollection();
        $this->productSizes = new ArrayCollection();
    }

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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function isHighlighted(): ?bool
    {
        return $this->highlighted;
    }

    public function setHighlighted(bool $highlighted): static
    {
        $this->highlighted = $highlighted;

        return $this;
    }

    /**
     * @return Collection<int, Cart>
     */
    public function getCarts(): Collection
    {
        return $this->carts;
    }

    public function addCart(Cart $cart): static
    {
        if (!$this->carts->contains($cart)) {
            $this->carts->add($cart);
            $cart->addProduct($this);
        }

        return $this;
    }

    public function removeCart(Cart $cart): static
    {
        if ($this->carts->removeElement($cart)) {
            $cart->removeProduct($this);
        }

        return $this;
    }

    public function getImage(): ?string 
    { 
        return $this->image; 
    }
    
    public function setImage(?string $image): static 
    {
        $this->image = $image; return $this; 
    }

    public function getProductSizes(): Collection
    {
        return $this->productSizes;
    }

    public function addProductSize(ProductSize $productSize): self
    {
        if (!$this->productSizes->contains($productSize)) {
            $this->productSizes[] = $productSize;
            $productSize->setProduct($this);
        }

        return $this;
    }

    public function removeProductSize(ProductSize $productSize): self
    {
        if ($this->productSizes->contains($productSize)) {
            $this->productSizes->removeElement($productSize);
            if ($productSize->getProduct() === $this) {
                $productSize->setProduct(null);
            }
        }

        return $this;
    }

    public function getSelectedSize(): ?ProductSize
    {
        return $this->productSizes->first();
    }

        public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

}