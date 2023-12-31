<?php

namespace App\Service;

use DateTimeImmutable;

class JWTService
{
    /**
     * @param array $header
     * @param array $payload
     * @param string $secret
     * @param int $validity
     * @return string
     */
    public function generate(array $header, array $payload, string $secret, int $validity = 10800): string
    {
        if ($validity > 0) {
            //get the actual datetime
            $now = new DateTimeImmutable();
            //we add the validity to the actual datetime, to know when it expires
            $exp = $now->getTimestamp() + $validity;
            //issued at and expired at
            $payload['iat'] = $now->getTimestamp();
            $payload['exp'] = $exp;
        }

        //we encode it in base64
        $base64Header = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($payload));
        //we clear the encoded value(+,/,=)
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], $base64Header);
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], $base64Payload);
        // we have to generate the signature, with a secret
        $secret = base64_encode($secret);
        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secret, true);
        $base64Signature = base64_encode($signature);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], $base64Signature);
        //we create the token
        $jwt = $base64Header . '.' . $base64Payload . '.' . $base64Signature;
        return $jwt;
    }
    //we verify the structure of the token
    public function isValid(string $token): bool
    {
        return preg_match(
            '/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/',
            $token
        ) === 1;
    }

    //we get the header
    public function getHeader(string $token): array
    {
        //we explode he token
        $array = explode('.', $token);
        //we decode the payload
        $header = json_decode(base64_decode($array[0]), true);
        return $header;
    }

    //we get the payload
    public function getPayload(string $token): array
    {
        //we explode he token
        $array = explode('.', $token);
        //we decode the payload
        $payload = json_decode(base64_decode($array[1]), true);
        return $payload;
    }

    //we verify if the token has expired
    public function isExpired(string $token): bool
    {
        $payload = $this->getPayload($token);
        $now = new DateTimeImmutable();
        return $payload['exp'] < $now->getTimestamp();
    }

    //we verify the token's signature
    public function check(string $token, string $secret)
    {
        //we get the header and the payload
        $header = $this->getHeader($token);
        $payload = $this->getPayload($token);
        //we generate a new token with these values, to see if this signature is good
        //we set validity at 0 to skip the part where we initialize iat and exp, because we already have it with getPayload
        $verifToken = $this->generate($header, $payload, $secret, 0);
        return $token === $verifToken;
    }
}
