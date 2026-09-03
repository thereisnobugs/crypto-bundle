<?php

namespace AgentSIB\CryptoBundle\Command;

use AgentSIB\CryptoBundle\Service\CryptoService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'agentsib_crypto:decrypt', description: 'Decrypt string')]
class StringDecryptCommand extends Command
{
    public function __construct(
        protected CryptoService $cryptoService,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addArgument('encryptedString', InputArgument::REQUIRED, 'Encrypted string');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(
            $this->cryptoService->decrypt(
                $input->getArgument('encryptedString')
            )
        );

        return self::SUCCESS;
    }
}
