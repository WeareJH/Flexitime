<?php

namespace JhFlexiTime\Validator;

use DoctrineModule\Validator\UniqueObject;
use ZfcUser\Entity\UserInterface;

/**
 * Class UniqueUserObject
 * @package JhFlexiTime\Validator
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class UniqueUserObject extends UniqueObject
{
    /**
     * @var \ZfcUser\Entity\UserInterface
     */
    protected $user;

    /**
     * @param array $options
     * @throws \InvalidArgumentException
     */
    public function __construct(array $options)
    {
        parent::__construct($options);

        if (!isset($options['user']) || !$options['user'] instanceof UserInterface) {
            throw new \InvalidArgumentException(
                'user must be provided and be an instance of \ZfcUser\Entity\UserInterface'
            );
        }

        $this->user = $options['user'];
    }

    /**
     * Nasty method - but can't figure out
     * how to do what I need, there are various limitations
     * with Doctrine2:
     * 1.) Composite primary cannot be objects, this parent class uses array_diff_assoc which expects
     * values to not be objects
     * 2.) DateTime not supported as primary key: http://www.doctrine-project.org/jira/browse/DDC-1780
     * 3.) Limitations with ZF2 validator error messages
     *
     * @param mixed $value
     * @param null|array $context
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $values = $this->cleanSearchValue(array($value, $this->user));
        $match = $this->objectRepository->findOneBy($values);

        if (!is_object($match)) {
            return true;
        }

        //check if the field value we are looking for
        //has a getter on the match object
        $field  = $this->fields[0];
        $method = 'get' . ucfirst($field);
        if (!method_exists($match, $method)) {
            return false;
        }

        if ($this->user === $match->getUser() && $value == $match->$method()) {
            return true;
        }

        //hack to create correct messages
        //this validator should be agnostic to what object is passed in
        if ($value instanceof \DateTime) {
            $value = $value->format("d-m-Y");
        }

        $this->error(self::ERROR_OBJECT_NOT_UNIQUE, $value);
        return false;
    }
}
