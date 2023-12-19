<?php

namespace App\Entity;

use App\Repository\SubCategoriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubCategoriesRepository::class)]
class SubCategories
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: Categories::class, inversedBy: 'subcategories')]
    #[ORM\JoinColumn(name: 'categories_id', referencedColumnName: 'id')]
    private $categories;

    #[ORM\OneToMany(mappedBy: 'subcategories', targetEntity: Products::class)]
    private Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
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
     * @return Collection<int, Products>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Products $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setSubcategories($this);
        }

        return $this;
    }

    public function removeProduct(Products $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getSubcategories() === $this) {
                $product->setSubcategories(null);
            }
        }

        return $this;
    }
    /**
 * Get the associated category.
 *
 * @return Categories|null
 */
public function getCategories(): ?Categories
{
    return $this->categories;
}

/**
 * Set the associated category.
 *
 * @param Categories|null $categories
 * @return $this
 */
public function setCategories(?Categories $categories): self
{
    $this->categories = $categories;

    return $this;
}
public function __toString(): string
{
    return $this->name ?? ''; // Adjust based on your entity's properties
}
}
