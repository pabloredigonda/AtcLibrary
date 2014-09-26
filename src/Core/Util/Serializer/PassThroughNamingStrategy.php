<?php
/**
 * Core\Util
 *
 * PHP version 5.4
 *
 * @category General
 * @package  Core\Util
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @version  GIT:<>
 * @link     https://github.com/desyncr
 */
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
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @link     https://github.com/desyncr
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
 