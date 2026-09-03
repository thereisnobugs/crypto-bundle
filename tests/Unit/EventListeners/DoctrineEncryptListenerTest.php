<?php

namespace AgentSIB\CryptoBundle\Tests\Unit\EventListeners;

use AgentSIB\CryptoBundle\EventListeners\DoctrineEncryptListener;
use AgentSIB\CryptoBundle\Model\Cipher\AES256OpenSSLCipher;
use AgentSIB\CryptoBundle\Model\Exception\DecryptException;
use AgentSIB\CryptoBundle\Model\SecretSource\SimpleSecretSource;
use AgentSIB\CryptoBundle\Service\CryptoService;
use AgentSIB\CryptoBundle\Tests\Fixtures\CryptoTestEntity;
use AgentSIB\CryptoBundle\Tests\Fixtures\NullableCryptoTestEntity;
use AgentSIB\CryptoBundle\Tests\Fixtures\TolerantCryptoTestEntity;
use PHPUnit\Framework\TestCase;

class DoctrineEncryptListenerTest extends TestCase
{
    private CryptoService $cryptoService;

    private DoctrineEncryptListener $listener;

    protected function setUp(): void
    {
        $this->cryptoService = new CryptoService('v1');
        $this->cryptoService->addCipherForVersion('v1', new AES256OpenSSLCipher(new SimpleSecretSource('listener-secret')));

        $this->listener = new DoctrineEncryptListener($this->cryptoService);
    }

    public function testEncryptOperationWritesCiphertextToEncryptedProperty(): void
    {
        $entity = new CryptoTestEntity();
        $entity->setPlainSecret('hello');

        $this->listener->processFields($entity, DoctrineEncryptListener::OPERATION_ENCRYPT);

        self::assertMatchesRegularExpression('/^enc:v1::/', (string)$entity->getSecret());
        self::assertSame('hello', $entity->getPlainSecret());
    }

    public function testDecryptOperationRestoresPlainPropertyValue(): void
    {
        $entity = new CryptoTestEntity();
        $entity->setPlainSecret('hello');

        $this->listener->processFields($entity, DoctrineEncryptListener::OPERATION_ENCRYPT);
        $this->listener->processFields($entity, DoctrineEncryptListener::OPERATION_DECRYPT);

        self::assertSame('hello', $entity->getPlainSecret());
    }

    public function testEncryptOperationReencryptsChangedPlainValue(): void
    {
        $entity = new CryptoTestEntity();
        $entity->setPlainSecret('hello');

        $this->listener->processFields($entity, DoctrineEncryptListener::OPERATION_ENCRYPT);

        $entity->setPlainSecret('hello-again');
        $this->listener->processFields($entity, DoctrineEncryptListener::OPERATION_ENCRYPT);

        self::assertSame('hello-again', $this->cryptoService->decrypt((string)$entity->getSecret()));
    }

    public function testDecryptOperationAcceptsPlainTextWhenAllowed(): void
    {
        $entity = new CryptoTestEntity();
        $entity->setSecret('legacy-plain-text');

        $this->listener->processFields($entity, DoctrineEncryptListener::OPERATION_DECRYPT);

        self::assertSame('legacy-plain-text', $entity->getPlainSecret());
    }

    public function testDecryptOperationThrowsForTamperedCiphertextByDefault(): void
    {
        $entity = new CryptoTestEntity();
        $entity->setSecret($this->tamperedEncryptedString());

        $this->expectException(DecryptException::class);

        $this->listener->processFields($entity, DoctrineEncryptListener::OPERATION_DECRYPT);
    }

    public function testDecryptOperationReturnsEmptyStringForTamperedCiphertextInTolerantMode(): void
    {
        $entity = new TolerantCryptoTestEntity();
        $entity->setSecret($this->tamperedEncryptedString());

        $this->listener->processFields($entity, DoctrineEncryptListener::OPERATION_DECRYPT);

        // the listener's internal `false` marker is written via reflection into a
        // ?string property, where it coerces to an empty string (no strict_types)
        self::assertSame('', $entity->getPlainSecret());
    }

    public function testEncryptFalsyPlainValueBecomesEmptyStringByDefault(): void
    {
        $entity = new CryptoTestEntity();
        $entity->setPlainSecret('0');

        $this->listener->processFields($entity, DoctrineEncryptListener::OPERATION_ENCRYPT);

        self::assertSame('', $entity->getSecret());
    }

    public function testEncryptFalsyPlainValueBecomesNullWhenNullable(): void
    {
        $entity = new NullableCryptoTestEntity();
        $entity->setPlainSecret('0');

        $this->listener->processFields($entity, DoctrineEncryptListener::OPERATION_ENCRYPT);

        self::assertNull($entity->getSecret());
    }

    private function tamperedEncryptedString(): string
    {
        $raw = base64_decode(substr($this->cryptoService->encrypt('hello'), strlen('enc:v1::')));
        $raw[strlen($raw) - 1] = $raw[strlen($raw) - 1] ^ "\x01";

        return 'enc:v1::' . base64_encode($raw);
    }
}
