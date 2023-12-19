<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;

// Trait to add 'created_at' property and related methods to entities
trait CreatedAtTrait {

    // Define 'created_at' property with default value as the current timestamp
    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $created_at = null;

    // Getter for 'created_at' property
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    // Setter for 'created_at' property
    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}