<?php

namespace AgentSIB\CryptoBundle\Model;

interface CipherInterface
{
    public function encrypt(string $plainString): string;

    public function decrypt(string $encryptedString): string;
}
