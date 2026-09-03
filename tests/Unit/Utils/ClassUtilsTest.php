<?php

namespace AgentSIB\CryptoBundle\Tests\Unit\Utils;

use AgentSIB\CryptoBundle\Tests\Fixtures\CryptoTestEntity;
use AgentSIB\CryptoBundle\Utils\ClassUtils;
use Doctrine\Persistence\Proxy;
use PHPUnit\Framework\TestCase;

class ClassUtilsTest extends TestCase
{
    public function testPropertyAccessorsReadAndWritePublicProperty(): void
    {
        $entity = new ClassUtilsPublicFixture();
        $property = new \ReflectionProperty($entity, 'publicField');

        ClassUtils::setPropertyValue($entity, $property, 'value');
        self::assertSame('value', ClassUtils::getPropertyValue($entity, $property));
        self::assertSame('value', $entity->publicField);
    }

    public function testPropertyAccessorsReadAndWritePrivateProperty(): void
    {
        $entity = new CryptoTestEntity();
        $property = new \ReflectionProperty($entity, 'plainSecret');

        ClassUtils::setPropertyValue($entity, $property, 'hidden');
        self::assertSame('hidden', ClassUtils::getPropertyValue($entity, $property));
        self::assertSame('hidden', $entity->getPlainSecret());
    }

    public function testGetPropertyValueThrowsWhenPropertyBelongsToDifferentClass(): void
    {
        $entity = new CryptoTestEntity();
        $foreignProperty = new \ReflectionProperty(ClassUtilsPublicFixture::class, 'publicField');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Expected class is');

        ClassUtils::getPropertyValue($entity, $foreignProperty);
    }

    public function testGetEntityClassReturnsConcreteEntityClass(): void
    {
        self::assertSame(CryptoTestEntity::class, ClassUtils::getEntityClass(new CryptoTestEntity()));
    }

    public function testGetEntityClassReturnsParentClassForDoctrineProxy(): void
    {
        $proxy = new ClassUtilsProxyFixture();

        self::assertSame(ClassUtilsPublicFixture::class, ClassUtils::getEntityClass($proxy));
    }
}

class ClassUtilsPublicFixture
{
    public string $publicField = '';
}

class ClassUtilsProxyFixture extends ClassUtilsPublicFixture implements Proxy
{
    public function __load(): void
    {
    }

    public function __isInitialized(): bool
    {
        return true;
    }
}
