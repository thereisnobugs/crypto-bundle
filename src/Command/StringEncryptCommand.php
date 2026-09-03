<?php

namespace AgentSIB\CryptoBundle\Command;

use AgentSIB\CryptoBundle\Service\CryptoService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StringEncryptCommand extends Command
{
    public function __construct(
        protected CryptoService $cryptoService,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName('agentsib_crypto:encrypt')
            ->setDescription('Decrypt string');

        $this->addArgument('plainString', InputArgument::REQUIRED, 'Plain string');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(
            $this->cryptoService->encrypt(
                $input->getArgument('plainString')
            )
        );

        return self::SUCCESS;
    }
}
