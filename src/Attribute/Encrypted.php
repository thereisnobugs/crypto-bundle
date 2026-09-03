<?php

namespace AgentSIB\CryptoBundle\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Encrypted
{
    public bool $allowDecrypted = true;

    /**
     * @Enum({"exception", "false"})
     */
    public string $onDecryptFail = 'exception';

    public bool $nullable = false;

    public function __construct(
        public string $decryptedProperty,
        ?bool $allowDecrypted = null,
        ?string $onDecryptFail = null,
        ?bool $nullable = null
    ) {
        $this->allowDecrypted = $allowDecrypted ?? $this->allowDecrypted;
        $this->onDecryptFail = $onDecryptFail ?? $this->onDecryptFail;
        $this->nullable = $nullable ?? $this->nullable;
    }
}
