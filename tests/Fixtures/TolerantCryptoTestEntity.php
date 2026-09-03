<?php

namespace AgentSIB\CryptoBundle\Tests\Fixtures;

use AgentSIB\CryptoBundle\Attribute\Encrypted;

class TolerantCryptoTestEntity
{
    #[Encrypted(decryptedProperty: 'plainSecret', onDecryptFail: 'false')]
    private ?string $secret = null;

    private ?string $plainSecret = null;

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): void
    {
        $this->secret = $secret;
    }

    public function getPlainSecret(): ?string
    {
        return $this->plainSecret;
    }

    public function setPlainSecret(?string $plainSecret): void
    {
        $this->plainSecret = $plainSecret;
    }
}
