<?php

namespace AgentSIB\CryptoBundle\DependencyInjection\Factory\SecretSource;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

interface SecretSourceFactoryInterface
{
    /**
     *
     * @return string service_id
     */
    public function create(ContainerBuilder $container, string $sourceName, array|string $config): string;

    public function getName(): string;

    public function addConfiguration(ArrayNodeDefinition $builder): void;
}
