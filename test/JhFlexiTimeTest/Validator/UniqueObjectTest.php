<?php

namespace JhFlexiTimeTest\Validator;

use JhFlexiTime\Validator\UniqueObject;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use JhUser\Entity\User;

/**
 * Class UniqueObjectTest
 * @package JhFlexiTimeTest\Validator
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class UniqueObjectTest extends \PHPUnit_Framework_TestCase
{
    protected $validator;
    protected $objectManager;
    protected $repository;

    public function setUp()
    {
        $this->objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->repository    = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
    }

    protected function getValidator($fields)
    {
        return $this->validator = new UniqueObject(array(
            'object_manager'    => $this->objectManager,
            'object_repository' => $this->repository,
            'fields'            => $fields,
            'use_context'       => true,
        ));
    }

    public function testValidatorPassesIfObjectNotExist()
    {
        $validator  = $this->getValidator(['date', 'user']);

        $date = '12-04-88';
        $user = 2;

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['user' => $user, 'date' => $date])
            ->will($this->returnValue(null));

        $context = [
            'user' => $user,
            'date' => $date,
        ];

        $this->assertTrue($validator->isValid('12-04-88', $context));
    }

    public function testValidatorFailsIfObjectExistsAndIsNotTheSameId()
    {
        $validator  = $this->getValidator(['someField', 'someOtherField']);

        $context = [
            'someField'         => 1,
            'someOtherField'    => 2,
            'id'                => 1,
        ];

        $match = [
            'someField'         => 1,
            'someOtherField'    => 2,
            'id'                => 2,
        ];

        $match  = (object) $match;

        $classMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(array('id')));
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierValues')
            ->with($match)
            ->will($this->returnValue(array('id' => 2)));

        $this->objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with('stdClass')
            ->will($this->returnValue($classMetadata));

        $this->repository
            ->expects($this->any())
            ->method('getClassName')
            ->will($this->returnValue('stdClass'));

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['someField' => 1, 'someOtherField' => 2])
            ->will($this->returnValue($match));

        $this->assertFalse($validator->isValid('12-04-88', $context));
    }

    public function testValidatorPassesIfObjectExistsAndHasSameId()
    {
        $validator  = $this->getValidator(['someField', 'someOtherField']);

        $context = [
            'someField'         => 1,
            'someOtherField'    => 2,
            'id'                => 1,
        ];

        $match = [
            'someField'         => 1,
            'someOtherField'    => 2,
            'id'                => 1,
        ];

        $match  = (object) $match;

        $classMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(array('id')));
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierValues')
            ->with($match)
            ->will($this->returnValue(array('id' => 1)));

        $this->objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with('stdClass')
            ->will($this->returnValue($classMetadata));

        $this->repository
            ->expects($this->any())
            ->method('getClassName')
            ->will($this->returnValue('stdClass'));

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['someField' => 1, 'someOtherField' => 2])
            ->will($this->returnValue($match));

        $this->assertTrue($validator->isValid('12-04-88', $context));
    }

    public function testValidatorPassesIfObjectExistsAndHasTheSameIds()
    {
        $validator  = $this->getValidator(['user', 'date']);

        $date = '12-04-88';
        $user = 2;

        $context = [
            'user' => $user,
            'date' => $date,
        ];

        $object = (object) $context;

        $classMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(array('user', 'date')));
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierValues')
            ->with($object)
            ->will($this->returnValue(array('user' => $user, 'date' => $date)));

        $this->objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with('stdClass')
            ->will($this->returnValue($classMetadata));

        $this->repository
            ->expects($this->any())
            ->method('getClassName')
            ->will($this->returnValue('stdClass'));

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['user' => $user, 'date' => $date])
            ->will($this->returnValue($object));

        $this->assertTrue($validator->isValid('12-04-88', $context));
    }

    public function testValidatorPassesIfObjectExistsAndHasTheSameIdsWithObjectAsId()
    {
        $validator  = $this->getValidator(['user', 'date']);

        $date = new \JhFlexiTime\DateTime\DateTime("12/04/88");

        $user = new User;

        $context = [
            'user' => 2,
            'date' => $date,
        ];

        $object = (object) $context;

        $foundIdentifiers = [
            'user' => $user,
            'date' => $date,
        ];

        $bookingClassMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $bookingClassMetadata
            ->expects($this->once())
            ->method('getIdentifierValues')
            ->with($object)
            ->will($this->returnValue($foundIdentifiers));

        $bookingClassMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->will($this->returnValue(['user', 'date']));


        $userIdValues = [0 => 2];
        $userClassMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $userClassMetadata
            ->expects($this->once())
            ->method('getIdentifierValues')
            ->with($user)
            ->will($this->returnValue($userIdValues));


        $metaMap = [
            ['stdClass', $bookingClassMetadata],
            ['JhUser\Entity\User', $userClassMetadata],
        ];

        $this->objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValueMap($metaMap));

        $e = new \Doctrine\Common\Persistence\Mapping\MappingException();
        $this->objectManager->expects($this->at(3))
            ->method('getClassMetadata')
            ->with('JhFlexiTime\DateTime\DateTime')
            ->will($this->throwException($e));

        $this->repository
            ->expects($this->any())
            ->method('getClassName')
            ->will($this->returnValue('stdClass'));

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['user' => 2, 'date' => $date])
            ->will($this->returnValue($object));

        $this->assertTrue($validator->isValid('12-04-88', $context));
    }
}
