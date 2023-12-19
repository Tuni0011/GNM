<?php

namespace App\Service;

class UserCustomIdGenerator
{
    public function generateCustomId($user): string
    {
        // Logic to generate custom ID
        $parentUser = $user->getParent();
        $parentCustomId = $parentUser ? $parentUser->getCustomId() : null;

        $customId = $parentCustomId . '-' . $this->generateRandomCode();

        // Assuming that you have a public method to set the customId in your Users entity
        $user->setCustomId($customId);

        // Return the generated custom ID
        return $customId;
    }

    private function generateRandomCode(): string
    {
        // Generate a random code (numbers and letters)
        $numbers = mt_rand(100, 999);
        $letter = chr(mt_rand(65, 90));

        return $numbers . $letter;
    }
}