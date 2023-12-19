<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;

// Trait to add 'slug' property and related methods to entities
trait CustomIdTrait {
    
    #[ORM\Column(length: 4, unique:true)]
    
   private ?string $customId = null;
    /**
     * Set the custom ID.
     *
     * @param string|null $customId
     */
    public function setCustomId(?string $customId): void
    {
        $this->customId = $customId;
    }

    /**
     * @PrePersist
     */
    public function prePersist(): void
    {
        $this->generateCustomId(); // Ensure custom ID is generated on persist
    }

    /**
     * Generate a custom ID for the user.
     */
    public function generateCustomId(): void
    {
        // Logic to generate custom ID
        $parentCustomId = $this->getParent() ? $this->getParent()->getCustomId() : null;

        $this->customId = $parentCustomId
            ? $parentCustomId . '-' . $this->generateRandomCode()
            : $this->generateRandomCode();
    }

    /**
     * Generate a random code (3 numbers and 1 letter).
     */
    private function generateRandomCode(): string
    {
        $numbers = mt_rand(100, 999);
        $letter = chr(mt_rand(65, 90)); // ASCII values for uppercase letters

        return $numbers . $letter;
    }
    public function getCustomId(): ?string
    {
        return $this->customId;
    }
}
