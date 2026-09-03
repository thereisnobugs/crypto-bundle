<?php

namespace AgentSIB\CryptoBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class ConsoleCommandsTest extends KernelTestCase
{
    public function testDecryptCommandRestoresValueProducedByEncryptCommand(): void
    {
        self::bootKernel(['test_case' => 'FirstCase', 'root_config' => 'config.yml']);
        $application = new Application(static::$kernel);

        $encryptTester = new CommandTester($application->find('agentsib_crypto:encrypt'));
        $encryptTester->execute(['plainString' => 'console round trip']);

        self::assertSame(Command::SUCCESS, $encryptTester->getStatusCode());
        $encrypted = trim($encryptTester->getDisplay());
        self::assertMatchesRegularExpression('/^enc:v1::/', $encrypted);

        $decryptTester = new CommandTester($application->find('agentsib_crypto:decrypt'));
        $decryptTester->execute(['encryptedString' => $encrypted]);

        self::assertSame(Command::SUCCESS, $decryptTester->getStatusCode());
        self::assertSame('console round trip', trim($decryptTester->getDisplay()));
    }

    public function testBenchmarkCommandCompletesSuccessfully(): void
    {
        self::bootKernel(['test_case' => 'FirstCase', 'root_config' => 'config.yml']);
        $application = new Application(static::$kernel);

        $tester = new CommandTester($application->find('agentsib_crypto:benchmark'));
        // NB: keep --count >= 100: the command divides $count by 100 for progress
        // updates and crashes with "Modulo by zero" for smaller values (pre-existing bug)
        $tester->execute(['--count' => 100, '--length' => 64]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
    }
}
