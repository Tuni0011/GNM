<?php

namespace App\Entity;

use App\Entity\Trait\CreatedAtTrait;
use App\Entity\Trait\SlugTrait;
use App\Repository\ProductsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductsRepository::class)]
class Products
{   
        use CreatedAtTrait;
        use SlugTrait;



    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Name cannot be blank.')]
    #[Assert\Length(
        min: 3,
        max: 30,
        minMessage: 'Name must be at least {{ limit }} characters long.',
        maxMessage: 'Name cannot be longer than {{ limit }} characters.'
    )]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Description cannot be blank.')]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Price cannot be blank.')]
    #[Assert\Type(type: 'numeric', message: 'Price must be a number.')]
    #[Assert\GreaterThan(value: 0, message: 'Price must be greater than 0.')]
    
    private ?int $price = null;

   
    #[ORM\Column]
    #[Assert\NotBlank(message: 'Stock cannot be blank.')]
    #[Assert\Type(type: 'integer', message: 'Stock must be an integer.')]
    #[Assert\GreaterThan(value: 0, message: 'Stock must be greater than 0.')]
    private ?int $stock = null;

    #[ORM\OneToMany(mappedBy: 'products', targetEntity: Images::class, orphanRemoval: true , cascade:['persist'])]
    private Collection $images;

    #[ORM\OneToMany(mappedBy: 'products', targetEntity: OrdersDetails::class)]
    private Collection $ordersDetails;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SubCategories $subcategories = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Shop $shop = null;

  


    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->ordersDetails = new ArrayCollection();
        $this->created_at =new \DateTimeImmutable();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

  
    /**
     * @return Collection<int, Images>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Images $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setProducts($this);
        }

        return $this;
    }

    public function removeImage(Images $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getProducts() === $this) {
                $image->setProducts(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, OrdersDetails>
     */
    public function getOrdersDetails(): Collection
    {
        return $this->ordersDetails;
    }

    public function addOrdersDetail(OrdersDetails $ordersDetail): static
    {
        if (!$this->ordersDetails->contains($ordersDetail)) {
            $this->ordersDetails->add($ordersDetail);
            $ordersDetail->setProducts($this);
        }

        return $this;
    }

    public function removeOrdersDetail(OrdersDetails $ordersDetail): static
    {
        if ($this->ordersDetails->removeElement($ordersDetail)) {
            // set the owning side to null (unless already changed)
            if ($ordersDetail->getProducts() === $this) {
                $ordersDetail->setProducts(null);
            }
        }

        return $this;
    }

    public function getSubcategories(): ?SubCategories
    {
        return $this->subcategories;
    }

    public function setSubcategories(?SubCategories $subcategories): self
    {
        $this->subcategories = $subcategories;

        return $this;
    }
    public function isProductNew(): bool
    {
        // Define the threshold for considering a product as new (e.g., 7 days)
        $newProductThreshold = new \DateTime('-7 days');

        return $this->created_at >= $newProductThreshold;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): static
    {
        $this->shop = $shop;

        return $this;
    }

    

}