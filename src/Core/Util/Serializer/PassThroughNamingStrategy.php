<?php
namespace Core\Util\Serializer;

use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;

/**
 * Class SerializerStrategy
 *
 * Look at https://github.com/schmittjoh/serializer/issues/33
 *
 * @category General
 * @package  Core\Util
 * @author   Pablo Redigonda <pablo.redigonda@globant.com>
 */
class PassThroughNamingStrategy implements PropertyNamingStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function translateName(PropertyMetadata $property)
    {
        return $property->name;
    }
}
 