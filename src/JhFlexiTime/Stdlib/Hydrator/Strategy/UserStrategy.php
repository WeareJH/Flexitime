<?php

namespace JhFlexiTime\Stdlib\Hydrator\Strategy;

use JhUser\Repository\UserRepositoryInterface;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;
use ZfcUser\Entity\UserInterface;

/**
 * Class UserStrategy
 * @package JhFlexiTime\Stdlib\Hydrator\Strategy
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class UserStrategy implements StrategyInterface
{
    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Convert the User object to it's ID
     *
     * @param UserInterface $value
     * @return int User ID
     */
    public function extract($value)
    {
        if ($value instanceof UserInterface) {
            return $value->getId();
        } else {
            return $value;
        }
    }

    /**
     * Convert the User Id to a User object
     *
     * @param mixed $value The original value.
     * @return UserInterface
     */
    public function hydrate($value)
    {
        return $this->userRepository->find($value);
    }
}
