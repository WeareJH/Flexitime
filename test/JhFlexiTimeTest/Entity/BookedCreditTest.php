<?php

namespace JhFlexiTimeTest\Entity;

use JhFlexiTime\DateTime\DateTime;
use JhFlexiTime\Entity\BookedCredit;
use JhFlexiTime\Entity\BookedCreditType;
use JhUser\Entity\User;
use PHPUnit_Framework_TestCase;

/**
 * Class BookedCreditTest
 * @package JhFlexiTimeTest\Entity
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class BookedCreditTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var BookedCredit
     */
    protected $entity;

    public function setUp()
    {
        $this->entity = new BookedCredit;
    }

    public function testGetterSetters()
    {
        $this->assertNull($this->entity->getId());
        $this->assertNull($this->entity->getUser());
        $this->assertNull($this->entity->getDate());
        $this->assertNull($this->entity->getAmount());
        $this->assertNull($this->entity->getType());
        $this->assertNull($this->entity->getNotes());

        $user = new User;
        $date = new DateTime;
        $type = new BookedCreditType;

        $this->entity->setId(2);
        $this->entity->setUser($user);
        $this->entity->setDate($date);
        $this->entity->setAmount(7.5);
        $this->entity->setType($type);
        $this->entity->setNotes('notes');

        $this->assertSame(2, $this->entity->getId());
        $this->assertSame($user, $this->entity->getUser());
        $this->assertSame($date, $this->entity->getDate());
        $this->assertSame(7.5, $this->entity->getAmount());
        $this->assertSame($type, $this->entity->getType());
        $this->assertSame('notes', $this->entity->getNotes());
    }
}
