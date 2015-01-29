<?php

namespace JhFlexiTimeTest\DBAL\Types;

use Doctrine\DBAL\Platforms\MySQL57Platform;
use JhFlexiTime\DBAL\Types\DateType;
use PHPUnit_Framework_TestCase;
use Doctrine\DBAL\Types\Type;
use Doctrine\Tests\DBAL\Mocks\MockPlatform;

/**
 * Class TimeTypeTest
 * @package JhFlexiTimeTest\DBAL\Types
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class TimeTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Type
     */
    protected $type;

    /**
     * @var MySQL57Platform
     */
    protected $platform;

    public function setUp()
    {
        Type::overrideType('time', 'JhFlexiTime\DBAL\Types\TimeType');
        $this->type       = Type::getType('time');
        $this->platform   = new MySQL57Platform;
    }

    public function testConvertToPHPValue()
    {
        $dateTime = $this->type->convertToPHPValue('10:30:31', $this->platform);
        $this->assertInstanceOf('JhFlexiTime\DateTime\DateTime', $dateTime);
    }

    public function testGetName()
    {
        $this->assertEquals('time', $this->type->getName());
    }

    public function testConvertToHydratorValueReturnsNullIfInputEmpty()
    {
        $this->assertNull($this->type->convertToHydratorValue(''));
    }

    public function testConvertToHydratorValue()
    {
        $this->assertInstanceOf('DateTime', $this->type->convertToHydratorValue(1422486708));
        $this->assertInstanceOf('DateTime', $this->type->convertToHydratorValue('10 November 2014'));
    }
}
