<?php

namespace AgentSIB\CryptoBundle\Command;

use AgentSIB\CryptoBundle\Service\CryptoService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'agentsib_crypto:benchmark', description: 'Benchmark cipher')]
class BenchmarkCommand extends Command
{
    public function __construct(
        protected CryptoService $cryptoService,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addOption('count', null, InputOption::VALUE_REQUIRED, 'Count operation', 1000);
        $this->addOption('length', null, InputOption::VALUE_REQUIRED, 'Data length (bytes)', 100);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = (int)$input->getOption('count');
        $length = (int)$input->getOption('length');

        if ($count <= 0 || $length <= 0) {
            throw new \RuntimeException('Invalid input parameters');
        }

        $progress = new ProgressBar($output, $count);

        $encryptTime = 0;
        $decryptTime = 0;

        for ($i = 0; $i < $count; $i++) {
            $bytes = openssl_random_pseudo_bytes($length);

            $startTime = microtime(true);
            $data = $this->cryptoService->encrypt($bytes);
            $encryptTime += microtime(true) - $startTime;

            $startTime = microtime(true);
            $this->cryptoService->decrypt($data);
            $decryptTime += microtime(true) - $startTime;

            if ($i % (int)($count / 100) === 0) {
                $progress->setProgress($i);
            }
        }

        $progress->finish();

        $output->writeln('');
        $output->writeln('');

        $output->writeln(sprintf('Encrypt: %s seconds, avg: %s per second', $encryptTime, round($count/$encryptTime, 3)));
        $output->writeln(sprintf('Decrypt: %s seconds, avg: %s per second', $decryptTime, round($count/$decryptTime, 3)));

        return self::SUCCESS;
    }
}
