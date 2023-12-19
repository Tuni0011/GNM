<?php

namespace App\Service;

use DateTimeImmutable;

class JWTService
{
    /**
     * Undocumented function
     *
     * @param array $header
     * @param array $payload
     * @param string $secret
     * @param integer $validity
     * @return string
     */
   
   public function generate(array $header, array $payload, string $secret, int $validity = 10800): string
   {
       if ($validity > 0) {
           $now = new DateTimeImmutable();
           $exp = $now->getTimestamp() + $validity;
           $payload['iat'] = $now->getTimestamp();
           $payload['exp'] = $exp;
       }

       $base64Header = base64_encode(json_encode($header));
       $base64Payload = base64_encode(json_encode($payload));

       $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], $base64Header);
       $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], $base64Payload);

       $secret = base64_encode($secret);
       $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $secret, true);
       $base64Signature = base64_encode($signature);
       $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], $base64Signature);

       $jwt = $base64Header . '.' . $base64Payload . '.' . $base64Signature;
       return $jwt;
   }

    /**
     * Validate if a string is a valid JWT format
     *
     * @param string $token
     *
     * @return bool
     */
    public function isValid(string $token): bool
    {
        return preg_match(
            '/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/',
            $token
        ) === 1;
    }

    /**
     * Decode the payload of a JWT
     *
     * @param string $token
     *
     * @return array
     */
    public function getPayload(string $token): array
    {
        $array = explode('.', $token);
        $payload = json_decode(base64_decode($array[1]), true);

        return $payload;
    }

    /**
     * Decode the header of a JWT
     *
     * @param string $token
     *
     * @return array
     */
    public function getHeader(string $token): array
    {
        $array = explode('.', $token);
        $header = json_decode(base64_decode($array[0]), true);

        return $header;
    }

    /**
     * Check if a JWT is expired
     *
     * @param string $token
     *
     * @return bool
     */
    public function isExpired(string $token): bool
    {
        $payload = $this->getPayload($token);
        $now = new DateTimeImmutable();

        return $payload['exp'] < $now->getTimestamp();
    }

    /**
     * Verify the JWT token
     *
     * @param string $token
     * @param string $secret
     * @return bool
     */
    public function check(string $token, string $secret): bool
    {
        // Get the header and payload from the original token
        $originalHeader = $this->getHeader($token);
        $originalPayload = $this->getPayload($token);

        // Generate the original token using the original header, payload, and the secret
        $originalToken = $this->generate($originalHeader, $originalPayload, $secret, 0);

        // Extract the signatures from both the generated and original tokens
        $generatedSignature = explode('.', $token)[2];
        $originalSignature = explode('.', $originalToken)[2];

        // Dump information for debugging
        

        // Compare the generated token with the original token
        return $generatedSignature === $originalSignature;
    }
}