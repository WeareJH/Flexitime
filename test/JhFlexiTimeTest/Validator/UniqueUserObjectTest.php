<?php

namespace JhFlexiTimeTest\Validator;

use JhFlexiTime\Entity\Booking;
use JhFlexiTime\Validator\UniqueUserObject;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use JhUser\Entity\User;

/**
 * Class UniqueUserObjectTest
 * @package JhFlexiTimeTest\Validator
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class UniqueUserObjectTest extends \PHPUnit_Framework_TestCase
{
    protected $validator;
    protected $objectManager;
    protected $repository;
    protected $user;

    public function setUp()
    {
        $this->objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->repository    = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $this->user          = $this->getMock('ZfcUser\Entity\UserInterface');


    }

    protected function getValidator($fields)
    {
        return $this->validator = new UniqueUserObject(array(
            'object_manager'    => $this->objectManager,
            'object_repository' => $this->repository,
            'user'              => $this->user,
            'fields'            => $fields,
        ));
    }

    public function testValidatorThrowsExceptionIsInvalidUsetSet()
    {
        $this->user = new \StdClass;
        $this->setExpectedException(
            'InvalidArgumentException',
            'user must be provided and be an instance of \ZfcUser\Entity\UserInterface'
        );
        $validator  = $this->getValidator(['date', 'user']);
    }

    public function testValidatorPassesIfObjectNotExist()
    {
        $validator  = $this->getValidator(['date', 'user']);

        $value = new \StdClass;

        $this->repository
             ->expects($this->once())
             ->method('findOneBy')
             ->with(['user' => $this->user, 'date' => $value])
             ->will($this->returnValue(null));

        $this->assertTrue($validator->isValid($value));
    }

    public function testValidatorFailsIfObjectDoesNotHaveFieldGetter()
    {
        $validator  = $this->getValidator(['date', 'user']);

        $value = new \StdClass;

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['user' => $this->user, 'date' => $value])
            ->will($this->returnValue(new \StdClass));

        $this->assertFalse($validator->isValid($value));
    }

    public function testValidatorPassesIfObjectHasSameUserAndFieldValue()
    {
        $validator  = $this->getValidator(['date', 'user']);

        $value      = new \DateTime;
        $booking    = new Booking();
        $booking->setDate($value);
        $booking->setUser($this->user);

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['user' => $this->user, 'date' => $value])
            ->will($this->returnValue($booking));

        $this->assertTrue($validator->isValid($value));

    }

    public function testValidatorFailsIfUserDoesNotMatch()
    {
        $validator  = $this->getValidator(['date', 'user']);

        $value      = new \DateTime("10 June 2014");
        $booking    = new Booking();
        $booking->setDate($value);

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['user' => $this->user, 'date' => $value])
            ->will($this->returnValue($booking));

        $this->assertFalse($validator->isValid($value));

        $expectedMessages = [
            'objectNotUnique' => "There is already another object matching '10-06-2014'"
        ];
        $this->assertEquals($expectedMessages, $validator->getMessages());
    }
}
