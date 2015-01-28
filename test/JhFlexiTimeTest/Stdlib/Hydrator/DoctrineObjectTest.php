<?php

namespace JhFlexiTimeTest\Stdlib\Hydrator;

use Doctrine\DBAL\Types\Type;
use JhFlexiTime\Entity\Booking;
use JhFlexiTime\Stdlib\Hydrator\DoctrineObject;
use PHPUnit_Framework_TestCase;

/**
 * Class UserStrategyTest
 * @package JhFlexiTimeTest\Validator
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class DoctrineObjectTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var DoctrineObject
     */
    protected $hydrator;

    /**
     * @var \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    protected $metadata;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    public function setUp()
    {
        $this->objectManager    = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->hydrator         = new DoctrineObject($this->objectManager);
        $this->metadata         = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $this->objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue($this->metadata));
    }

    public function testHandleTypeConversionsCustom()
    {
        Type::overrideType('time', 'JhFlexiTime\DBAL\Types\TimeType');

        $this
            ->metadata
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('JhFlexiTime\Entity\Booking'));
        $this
            ->metadata
            ->expects($this->any())
            ->method('getAssociationNames')
            ->will($this->returnValue(array()));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getTypeOfField')
            ->will($this->returnValue('time'));

        $booking = new Booking;
        $this->hydrator->hydrate(['startTime' => 1422486708], $booking);
        $this->assertInstanceOf('JhFlexiTime\DateTime\DateTime', $booking->getStartTime());
    }

    public function testHandleTypeConversionsWithNoCustomTypes()
    {
        Type::overrideType('time', 'Doctrine\DBAL\Types\TimeType');

        $this
            ->metadata
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('JhFlexiTimeTest\Stdlib\Hydrator\TestEntity'));
        $this
            ->metadata
            ->expects($this->any())
            ->method('getAssociationNames')
            ->will($this->returnValue(array()));

        $this
            ->metadata
            ->expects($this->any())
            ->method('getTypeOfField')
            ->will($this->returnValue('time'));

        $booking = new TestEntity;
        $this->hydrator->hydrate(['startTime' => 1422486708], $booking);
        $this->assertInstanceOf('DateTime', $booking->getStartTime());
    }
}
