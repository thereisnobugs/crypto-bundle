<?php

namespace AgentSIB\CryptoBundle\Tests\Unit\Model\Cipher;

use AgentSIB\CryptoBundle\Model\Cipher\AES256OpenSSLCipher;
use AgentSIB\CryptoBundle\Model\Exception\DecryptException;
use AgentSIB\CryptoBundle\Model\SecretSource\SimpleSecretSource;
use PHPUnit\Framework\TestCase;

class AES256OpenSSLCipherTest extends TestCase
{
    public function testEncryptDecryptRoundTrip(): void
    {
        $cipher = new AES256OpenSSLCipher(new SimpleSecretSource('cipher-secret'));

        foreach (['hello', '', 'Привет, мир! 🌍'] as $plain) {
            self::assertSame($plain, $cipher->decrypt($cipher->encrypt($plain)));
        }
    }

    public function testDecryptThrowsWhenCiphertextIsTampered(): void
    {
        $cipher = new AES256OpenSSLCipher(new SimpleSecretSource('cipher-secret'));
        $encrypted = $cipher->encrypt('hello');

        $encrypted[strlen($encrypted) - 1] = $encrypted[strlen($encrypted) - 1] ^ "\x01";

        $this->expectException(DecryptException::class);
        $cipher->decrypt($encrypted);
    }

    public function testDecryptThrowsWhenSecretDoesNotMatch(): void
    {
        $encryptor = new AES256OpenSSLCipher(new SimpleSecretSource('secret-one'));
        $decryptor = new AES256OpenSSLCipher(new SimpleSecretSource('secret-two'));

        $this->expectException(DecryptException::class);
        $decryptor->decrypt($encryptor->encrypt('hello'));
    }
}
