<?php

namespace AgentSIB\CryptoBundle\Model\Cipher;

use AgentSIB\CryptoBundle\Model\CipherInterface;
use AgentSIB\CryptoBundle\Model\SecretSourceInterface;

abstract class AbstractCipher implements CipherInterface
{
    public function __construct(protected SecretSourceInterface $secretSource)
    {
    }
}
