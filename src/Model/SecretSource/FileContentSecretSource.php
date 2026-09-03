<?php

namespace AgentSIB\CryptoBundle\Model\SecretSource;

use AgentSIB\CryptoBundle\Model\Exception\SecretSourceExtension;
use AgentSIB\CryptoBundle\Model\SecretSourceInterface;

class FileContentSecretSource implements SecretSourceInterface
{
    public function __construct(
        private readonly string $fileName,
    ) {
    }

    public function getSecret(): string
    {
        if (!file_exists($this->fileName)) {
            throw new SecretSourceExtension(sprintf('File "%s" not exists', $this->fileName));
        }

        if (!is_readable($this->fileName)) {
            throw new SecretSourceExtension(sprintf('File "%s" is not readable', $this->fileName));
        }

        return file_get_contents($this->fileName);
    }
}
