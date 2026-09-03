<?php

namespace AgentSIB\CryptoBundle\Tests\Unit\Service;

use AgentSIB\CryptoBundle\Model\Cipher\AES256OpenSSLCipher;
use AgentSIB\CryptoBundle\Model\Exception\CryptoException;
use AgentSIB\CryptoBundle\Model\SecretSource\SimpleSecretSource;
use AgentSIB\CryptoBundle\Service\CryptoService;
use PHPUnit\Framework\TestCase;

class CryptoServiceTest extends TestCase
{
    private const SECRET = 'unit-test-secret';

    private function createService(string $currentVersion = 'v1', array $versions = ['v1']): CryptoService
    {
        $service = new CryptoService($currentVersion);

        foreach ($versions as $version) {
            $service->addCipherForVersion($version, new AES256OpenSSLCipher(new SimpleSecretSource(self::SECRET)));
        }

        return $service;
    }

    public function testEncryptReturnsVersionedBase64Payload(): void
    {
        $encrypted = $this->createService()->encrypt('hello');

        self::assertMatchesRegularExpression('/^enc:v1::[A-Za-z0-9+\/=]+$/', $encrypted);
    }

    public function testIsEncryptedString(): void
    {
        $service = $this->createService();
        $encrypted = $service->encrypt('hello');

        self::assertTrue($service->isEncryptedString($encrypted));
        self::assertTrue($service->isEncryptedString('enc:v9::whatever', false));
        self::assertFalse($service->isEncryptedString('enc:v9::whatever'));
        self::assertFalse($service->isEncryptedString('plain text'));
    }

    public function testEncryptThrowsWhenCurrentCipherVersionIsNotRegistered(): void
    {
        $this->expectException(CryptoException::class);
        $this->expectExceptionMessage('Cipher version "v2" not found');

        $this->createService('v2', ['v1'])->encrypt('hello');
    }

    public function testDecryptThrowsWhenCipherVersionIsNotRegistered(): void
    {
        $this->expectException(CryptoException::class);
        $this->expectExceptionMessage('Cipher version "v9" not found');

        $this->createService()->decrypt('enc:v9::aGVsbG8=');
    }
}
