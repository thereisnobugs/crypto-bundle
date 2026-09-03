<?php

namespace AgentSIB\CryptoBundle\Tests\Unit\Model\SecretSource;

use AgentSIB\CryptoBundle\Model\Exception\SecretSourceExtension;
use AgentSIB\CryptoBundle\Model\SecretSource\ChainXORSecretSource;
use AgentSIB\CryptoBundle\Model\SecretSource\EnvironmentSecretSource;
use AgentSIB\CryptoBundle\Model\SecretSource\FileContentSecretSource;
use AgentSIB\CryptoBundle\Model\SecretSource\PhpConstantSecretSource;
use AgentSIB\CryptoBundle\Model\SecretSource\SimpleSecretSource;
use PHPUnit\Framework\TestCase;

class SecretSourceTest extends TestCase
{
    public function testEnvironmentSourceReturnsValueFromEnvironmentVariable(): void
    {
        putenv('CRYPTO_UNIT_TEST_ENV_SECRET=env-secret-value');

        try {
            self::assertSame('env-secret-value', (new EnvironmentSecretSource('CRYPTO_UNIT_TEST_ENV_SECRET'))->getSecret());
        } finally {
            putenv('CRYPTO_UNIT_TEST_ENV_SECRET');
        }
    }

    public function testEnvironmentSourceThrowsWhenVariableIsMissing(): void
    {
        $this->expectException(SecretSourceExtension::class);
        $this->expectExceptionMessage('Environment CRYPTO_UNIT_TEST_UNDEFINED_ENV not exists!');

        (new EnvironmentSecretSource('CRYPTO_UNIT_TEST_UNDEFINED_ENV'))->getSecret();
    }

    public function testFileContentSourceReturnsSecretFromFile(): void
    {
        $fileName = tempnam(sys_get_temp_dir(), 'crypto_secret_');
        file_put_contents($fileName, 'file-secret-content');

        try {
            self::assertSame('file-secret-content', (new FileContentSecretSource($fileName))->getSecret());
        } finally {
            unlink($fileName);
        }
    }

    public function testFileContentSourceThrowsWhenFileIsMissing(): void
    {
        $this->expectException(SecretSourceExtension::class);
        $this->expectExceptionMessage('not exists');

        (new FileContentSecretSource('/nonexistent/path/to/secret'))->getSecret();
    }

    public function testPhpConstantSourceReturnsDefinedConstantValue(): void
    {
        self::assertSame('constant-secret-value', (new PhpConstantSecretSource('CRYPTO_TEST_PHP_CONSTANT'))->getSecret());
    }

    public function testPhpConstantSourceThrowsWhenConstantIsUndefined(): void
    {
        $this->expectException(SecretSourceExtension::class);
        $this->expectExceptionMessage('PHP constant CRYPTO_UNIT_TEST_UNDEFINED_CONSTANT not exists!');

        (new PhpConstantSecretSource('CRYPTO_UNIT_TEST_UNDEFINED_CONSTANT'))->getSecret();
    }

    public function testChainWithSingleSourceReturnsSha256HashOfSecret(): void
    {
        $chain = new ChainXORSecretSource();
        $chain->addSecretSource(new SimpleSecretSource('abc'));

        self::assertSame(hash('sha256', 'abc', true), $chain->getSecret());
    }

    public function testChainDeterministicallyMixesMultipleSources(): void
    {
        $buildChain = static function (): ChainXORSecretSource {
            $chain = new ChainXORSecretSource();
            $chain->addSecretSource(new SimpleSecretSource('first-secret'));
            $chain->addSecretSource(new SimpleSecretSource('second-secret'));

            return $chain;
        };

        $secret = $buildChain()->getSecret();

        self::assertSame($secret, $buildChain()->getSecret());
        self::assertSame(32, strlen($secret));
        self::assertNotSame(hash('sha256', 'first-secret', true), $secret);
        self::assertNotSame(hash('sha256', 'second-secret', true), $secret);
    }
}
