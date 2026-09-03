<?php

namespace AgentSIB\CryptoBundle\Model\SecretSource;

use AgentSIB\CryptoBundle\Model\Exception\SecretSourceExtension;
use AgentSIB\CryptoBundle\Model\SecretSourceInterface;

class EnvironmentSecretSource implements SecretSourceInterface
{
    public function __construct(
        private readonly ?string $environmentName,
    ) {
    }

    public function getSecret(): string
    {
        $value = getenv($this->environmentName);
        if ($value === false) {
            throw new SecretSourceExtension(sprintf('Environment %s not exists!', $this->environmentName));
        }

        return $value;
    }
}
