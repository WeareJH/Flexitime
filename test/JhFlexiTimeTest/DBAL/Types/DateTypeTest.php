<?php

namespace JhFlexiTimeTest\DBAL\Types;

use Doctrine\DBAL\Platforms\MySQL57Platform;
use JhFlexiTime\DBAL\Types\DateType;
use PHPUnit_Framework_TestCase;
use Doctrine\DBAL\Types\Type;
use Doctrine\Tests\DBAL\Mocks\MockPlatform;

/**
 * Class DateTypeTest
 * @package JhFlexiTimeTest\DBAL\Types
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class DateTypeTest extends PHPUnit_Framework_TestCase
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
        Type::overrideType('date', 'JhFlexiTime\DBAL\Types\DateType');
        $this->type       = Type::getType('date');
        $this->platform   = new MySQL57Platform;
    }

    public function testConvertToPHPValue()
    {
        $dateTime = $this->type->convertToPHPValue('2014-01-02', $this->platform);
        $this->assertInstanceOf('JhFlexiTime\DateTime\DateTime', $dateTime);
    }

    public function testGetName()
    {
        $this->assertEquals('date', $this->type->getName());
    }
}
