<?php

namespace AgentSIB\CryptoBundle\Tests\Functional;

class BundleRegistrationTest extends KernelTestCase
{
    public function testKernelRegistersCryptoServiceInContainer(): void
    {
        $kernel = self::createKernel(['test_case' => 'FirstCase', 'root_config' => 'config.yml']);
        $kernel->boot();
        $container = $kernel->getContainer();

        $this->assertTrue($container->has('agentsib_crypto.crypto_service'));
    }
}
