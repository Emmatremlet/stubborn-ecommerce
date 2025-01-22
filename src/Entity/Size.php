<?php

namespace App\Entity;

use App\Repository\SizeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SizeRepository::class)]
class Size
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    /**
     * @var Collection<int, ProductSize>
     */
    #[ORM\OneToMany(targetEntity: ProductSize::class, mappedBy: 'size', orphanRemoval: true)]
    private Collection $productSizes;

    public function __construct()
    {
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

    /**
     * @return Collection<int, ProductSize>
     */
    public function getProductSizes(): Collection
    {
        return $this->productSizes;
    }

    public function addProductSizes(ProductSize $productSizes): static
    {
        if (!$this->productSizes->contains($stoproductSizes)) {
            $this->productSizes->add($productSizes);
            $sto->setSize($this);
        }

        return $this;
    }

    public function removeProductSizes(ProductSize $productSizes): static
    {
        if ($this->productSizes->removeElement($productSizes)) {
            // set the owning side to null (unless already changed)
            if ($productSizes->getSize() === $this) {
                $productSizes->setSize(null);
            }
        }

        return $this;
    }
}