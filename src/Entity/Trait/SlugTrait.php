<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;

// Trait to add 'slug' property and related methods to entities
trait SlugTrait {

    // Define 'slug' property with a length of 255 characters
    #[ORM\Column(length: 255)]
    private ?string $slug;

    // Getter for 'slug' property
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    // Setter for 'slug' property
    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }
}
