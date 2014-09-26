<?php

/**
 * General class.
 *
 * PHP version 5.4
 *
 * @category General
 * @package  Core\Util\Serializer
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @version  GIT:<>
 * @link     https://github.com/desyncr
 */
namespace Core\Util\Serializer;

use JMS\Serializer\Handler\DateHandler;
use JMS\Serializer\Context;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\XmlDeserializationVisitor;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\VisitorInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\XmlSerializationVisitor;

/**
 * Class DateTimeHandler
 *
 * @category General
 * @package  Core\Util\Serializer
 * @author   Dario Cavuotti <dc@syncr.com.ar>
 * @license  https://www.gnu.org/licenses/gpl.html GPL-3.0+
 * @link     https://github.com/desyncr
 */
class DateTimeHandler extends DateHandler
{
    /**
     * @var null|string
     */
    protected $defaultFormat = null;

    /**
     * @param string $defaultFormat
     * @param string $defaultTimezone
     * @param bool   $xmlCData
     */
    public function __construct($defaultFormat = \DateTime::ISO8601, $defaultTimezone = 'UTC', $xmlCData = true)
    {
        parent::__construct($defaultFormat, $defaultTimezone);
        $this->defaultFormat = $defaultFormat;
    }

    /**
     * serializeDateTime
     *
     * @param VisitorInterface $visitor
     * @param \DateTime        $date
     * @param array            $type
     * @param Context          $context
     *
     * @return mixed
     */
    public function serializeDateTime(VisitorInterface $visitor, \DateTime $date, array $type, Context $context)
    {
        return $visitor->visitArray(
            array(
                'full_date' => $date->format($this->getFormat($type)),
                // Backward compatibility
                'date'      => $date->format('Y-m-d H:i:s'),
                'timezone'  => $date->getTimezone()->getName()
            ),
            $type,
            $context
        );
    }

    /**
     * @return string
     * @param array $type
     */
    protected function getFormat(array $type)
    {
        return isset($type['params'][0]) ? $type['params'][0] : $this->defaultFormat;
    }
}
 