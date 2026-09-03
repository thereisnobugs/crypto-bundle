<?php

namespace AgentSIB\CryptoBundle\Utils;

use Doctrine\Persistence\Proxy;

class ClassUtils
{
    /**
     *
     * @throws \ReflectionException|\LogicException
     */
    public static function getPropertyValue(object $object, \ReflectionProperty $property): mixed
    {
        $refClass = $property->getDeclaringClass();
        $refProperty = $property;

        if (!$refClass->isInstance($object)) {
            throw new \LogicException(sprintf(
                'Expected class is "%s", actual "%s"',
                $refClass->getName(),
                $object::class
            ));
        }

        return $refProperty->getValue($object);
    }

    /**
     * @throws \ReflectionException
     */
    public static function setPropertyValue(object $object, \ReflectionProperty $property, mixed $value): void
    {
        $refClass = $property->getDeclaringClass();
        $refProperty = $property;

        if (!$refClass->isInstance($object)) {
            throw new \LogicException(sprintf(
                'Expected class is "%s", actual "%s"',
                $refClass->getName(),
                $object::class
            ));
        }

        $refProperty->setValue($object, $value);
    }

    public static function getEntityClass(object $entity): string
    {
        if ($entity instanceof Proxy && $parent = get_parent_class($entity)) {
            return $parent;
        }

        return $entity::class;
    }
}
