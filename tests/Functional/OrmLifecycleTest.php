<?php

namespace AgentSIB\CryptoBundle\Tests\Functional;

use AgentSIB\CryptoBundle\Service\CryptoService;
use AgentSIB\CryptoBundle\Tests\Fixtures\Entity\TestUser;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Entity lifecycle against a real (in-memory sqlite) entity manager:
 * preFlush encrypts on insert, postLoad decrypts, raw column keeps ciphertext.
 */
class OrmLifecycleTest extends KernelTestCase
{
    public function testDoctrineLoadsMetadataForEncryptedEntity(): void
    {
        self::bootKernel(['test_case' => 'OrmCase', 'root_config' => 'config.yml']);

        $metadata = static::$kernel->getContainer()->get('doctrine')->getManager()->getMetadataFactory()->getAllMetadata();

        self::assertCount(1, $metadata);
        self::assertSame(TestUser::class, $metadata[0]->getName());
    }

    public function testDoctrineLifecycleStoresCiphertextAndRestoresPlainValue(): void
    {
        self::bootKernel(['test_case' => 'OrmCase', 'root_config' => 'config.yml']);

        $container = static::$kernel->getContainer();
        $em = $container->get('doctrine')->getManager();

        (new SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());

        $user = new TestUser();
        $user->setNamePlain('Test Round-Trip');

        $em->persist($user);
        $em->flush();

        // postFlush decrypted the property back for further in-memory usage
        self::assertSame('Test Round-Trip', $user->getNamePlain());
        $id = $user->getId();

        // the database stores ciphertext, not the plain value
        $rawName = $em->getConnection()->fetchOne('SELECT name FROM crypto_test_user WHERE id = ?', [$id]);
        self::assertMatchesRegularExpression('/^enc:v1::/', (string) $rawName);
        self::assertStringNotContainsString('Test Round-Trip', (string) $rawName);

        /** @var CryptoService $cryptoService */
        $cryptoService = $container->get('agentsib_crypto.crypto_service');
        self::assertSame('Test Round-Trip', $cryptoService->decrypt((string) $rawName));

        // postLoad decrypts into the plain property
        $em->clear();
        $loaded = $em->find(TestUser::class, $id);
        self::assertNotNull($loaded);
        self::assertSame('Test Round-Trip', $loaded->getNamePlain());
        self::assertMatchesRegularExpression('/^enc:v1::/', (string) $loaded->getName());
    }
}
