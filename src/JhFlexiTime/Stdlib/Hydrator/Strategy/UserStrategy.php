<?php

namespace JhFlexiTime\Stdlib\Hydrator\Strategy;

use JhUser\Repository\UserRepositoryInterface;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

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
     * Converts the given value so that it can be extracted by the hydrator.
     *
     * @param mixed $value The original value.
     * @param object $object (optional) The original object for context.
     * @return mixed Returns the value that should be extracted.
     */
    public function extract($value)
    {
        if ($value instanceof \JhUser\Entity\User) {
            return $value->getId();
        } else {
            return $value;
        }
    }

    /**
     * Converts the given value so that it can be hydrated by the hydrator.
     *
     * @param mixed $value The original value.
     * @param array $data (optional) The original data for context.
     * @return mixed Returns the value that should be hydrated.
     */
    public function hydrate($value)
    {
        return $this->userRepository->find($value);
    }
}
