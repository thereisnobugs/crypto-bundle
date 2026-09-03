<?php

namespace AgentSIB\CryptoBundle\Tests\Unit\Attribute;

use AgentSIB\CryptoBundle\Attribute\Encrypted;
use PHPUnit\Framework\TestCase;

class EncryptedTest extends TestCase
{
    public function testDefaults(): void
    {
        $attribute = new Encrypted(decryptedProperty: 'plainField');

        self::assertSame('plainField', $attribute->decryptedProperty);
        self::assertTrue($attribute->allowDecrypted);
        self::assertSame('exception', $attribute->onDecryptFail);
        self::assertFalse($attribute->nullable);
    }

    public function testExplicitValues(): void
    {
        $attribute = new Encrypted(
            decryptedProperty: 'plainField',
            allowDecrypted: false,
            onDecryptFail: 'false',
            nullable: true,
        );

        self::assertFalse($attribute->allowDecrypted);
        self::assertSame('false', $attribute->onDecryptFail);
        self::assertTrue($attribute->nullable);
    }

    public function testConstructorKeepsDefaultsWhenOptionalArgumentsAreNull(): void
    {
        $attribute = new Encrypted('plainField', null, null, null);

        self::assertTrue($attribute->allowDecrypted);
        self::assertSame('exception', $attribute->onDecryptFail);
        self::assertFalse($attribute->nullable);
    }
}
