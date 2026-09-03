<?php

namespace AgentSIB\CryptoBundle\Model\SecretSource;

use AgentSIB\CryptoBundle\Model\SecretSourceInterface;

class SimpleSecretSource implements SecretSourceInterface
{
    public function __construct(
        private readonly string $secret,
    ) {
    }

    public function getSecret(): string
    {
        return $this->secret;
    }
}
