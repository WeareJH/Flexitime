<?php

namespace JhFlexiTimeTest\Entity;

use JhFlexiTime\Entity\BookedCreditType;
use PHPUnit_Framework_TestCase;

/**
 * Class BookedCreditTypeTest
 * @package JhFlexiTimeTest\Entity
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class BookedCreditTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var BookedCreditType
     */
    protected $entity;

    public function setUp()
    {
        $this->entity = new BookedCreditType;
    }

    public function testGetterSetters()
    {
        $this->assertNull($this->entity->getId());
        $this->assertNull($this->entity->getShortName());
        $this->assertNull($this->entity->getLabel());

        $this->entity->setId(2);
        $this->entity->setShortName('ot');
        $this->entity->setLabel('Overtime');

        $this->assertSame(2, $this->entity->getId());
        $this->assertSame('ot', $this->entity->getShortName());
        $this->assertSame('Overtime', $this->entity->getLabel());
    }
}
