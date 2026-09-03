<?php

namespace AgentSIB\CryptoBundle\Tests\Functional;

use AgentSIB\CryptoBundle\Service\CryptoService;

/**
 * Boots the kernel with all five secret source types chained into one cipher,
 * so every secret source factory and the chain wiring are exercised via DI.
 */
class SecretSourcesTest extends KernelTestCase
{
    private const ENV_NAME = 'CRYPTO_TEST_ENV_SECRET';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        putenv(self::ENV_NAME . '=env-secret-value');
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        putenv(self::ENV_NAME);
    }

    public function testChainedSecretSourcesRoundTrip(): void
    {
        self::bootKernel(['test_case' => 'SourcesCase', 'root_config' => 'config.yml']);

        $container = static::$kernel->getContainer();
        $service = $container->get('agentsib_crypto.crypto_service');

        self::assertInstanceOf(CryptoService::class, $service);
        self::assertSame('chained secret sources', $service->decrypt($service->encrypt('chained secret sources')));
        self::assertMatchesRegularExpression('/^enc:v1::/', $service->encrypt('anything'));
    }
}
