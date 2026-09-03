<?php

namespace AgentSIB\CryptoBundle\Tests\Functional;

use AgentSIB\CryptoBundle\Tests\Fixtures\Entity\TestUser;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Full key-rotation scenario: rows are written under cipher v1 (kernel with
 * current_cipher: v1), then the reencrypt command is executed under a kernel
 * with current_cipher: v2 (both ciphers configured), exercising the ORM 3
 * batch iteration path (toIterable + flush + detach).
 */
class ReencryptCommandTest extends KernelTestCase
{
    private const DB_FILE = __DIR__ . '/../../var/test-reencrypt.db3';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::removeDatabase();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        self::removeDatabase();
    }

    public function testVersionOneRotationConfigurationLoadsEncryptedEntityMetadata(): void
    {
        self::bootKernel(['test_case' => 'ReencryptCase', 'root_config' => 'config_v1.yml', 'environment' => 'reenc_v1']);

        $metadata = static::$kernel->getContainer()->get('doctrine')->getManager()->getMetadataFactory()->getAllMetadata();

        self::assertCount(1, $metadata);
        self::assertSame(TestUser::class, $metadata[0]->getName());
    }

    public function testReencryptCommandMigratesStoredValuesToCurrentCipherVersion(): void
    {
        // 1. seed rows encrypted with v1
        self::bootKernel(['test_case' => 'ReencryptCase', 'root_config' => 'config_v1.yml', 'environment' => 'reenc_v1']);
        $em = static::$kernel->getContainer()->get('doctrine')->getManager();
        (new SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());

        $plains = ['alpha-plain-value', 'beta-plain-value', 'gamma-plain-value'];
        foreach ($plains as $plain) {
            $user = new TestUser();
            $user->setNamePlain($plain);
            $em->persist($user);
        }
        $em->flush();
        $em->clear();

        $seedRaw = $em->getConnection()->fetchFirstColumn('SELECT name FROM crypto_test_user');
        self::assertCount(3, $seedRaw);
        foreach ($seedRaw as $raw) {
            self::assertMatchesRegularExpression('/^enc:v1::/', (string) $raw);
        }

        self::ensureKernelShutdown();

        // 2. run the reencrypt command under v2
        self::bootKernel(['test_case' => 'ReencryptCase', 'root_config' => 'config_v2.yml', 'environment' => 'reenc_v2']);
        $kernel = static::$kernel;
        $application = new Application($kernel);

        $tester = new CommandTester($application->find('agentsib_crypto:reencrypt'));
        $tester->execute([]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());

        // 3. every row is now stored with the v2 cipher
        $connection = $kernel->getContainer()->get('doctrine')->getConnection();
        $reencryptedRaw = $connection->fetchFirstColumn('SELECT name FROM crypto_test_user ORDER BY id');
        self::assertCount(3, $reencryptedRaw);
        foreach ($reencryptedRaw as $raw) {
            self::assertMatchesRegularExpression('/^enc:v2::/', (string) $raw);
        }

        // 4. entities still load and decrypt to the original plain values
        $em = $kernel->getContainer()->get('doctrine')->getManager();
        $em->clear();
        foreach ($plains as $index => $plain) {
            $user = $em->find(TestUser::class, $index + 1);
            self::assertNotNull($user);
            self::assertSame($plain, $user->getNamePlain());
        }
    }

    private static function removeDatabase(): void
    {
        if (!is_dir(dirname(self::DB_FILE))) {
            mkdir(dirname(self::DB_FILE), 0777, true);
        }

        foreach ([self::DB_FILE, self::DB_FILE . '-wal', self::DB_FILE . '-journal'] as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
