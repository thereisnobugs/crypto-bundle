<?php

namespace AgentSIB\CryptoBundle\Tests\Fixtures\Entity;

use AgentSIB\CryptoBundle\Attribute\Encrypted;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'crypto_test_user')]
class TestUser
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Encrypted(decryptedProperty: 'namePlain')]
    private ?string $name = null;

    private ?string $namePlain = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getNamePlain(): ?string
    {
        return $this->namePlain;
    }

    public function setNamePlain(?string $namePlain): void
    {
        $this->namePlain = $namePlain;
    }
}
