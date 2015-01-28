<?php

namespace JhFlexiTimeTest\Stdlib\Hydrator\Strategy;

use JhFlexiTime\Stdlib\Hydrator\Strategy\UserStrategy;
use JhUser\Entity\User;
use JhUser\Repository\UserRepositoryInterface;
use PHPUnit_Framework_TestCase;

/**
 * Class UserStrategyTest
 * @package JhFlexiTimeTest\Validator
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class UserStrategyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var UserRepositoryInterface
     */
    protected $repository;

    /**
     * @var UserStrategy
     */
    protected $strategy;

    public function setUp()
    {
        $this->repository = $this->getMock('JhUser\Repository\UserRepositoryInterface');
        $this->strategy = new UserStrategy($this->repository);
    }

    public function testExtractReturnsUserIdIfInstanceOfUser()
    {
        $user = new User;
        $user->setId(2);
        $this->assertEquals(2, $this->strategy->extract($user));
    }

    public function testExtractReturnsInputIfNotInstanceOfUser()
    {
        $this->assertEquals(2, $this->strategy->extract(2));
    }

    public function testHydrateLoadsUserFromRepository()
    {
        $user = new User;
        $user->setId(2);
        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with(2)
            ->will($this->returnValue($user));

        $this->assertSame($user, $this->strategy->hydrate(2));
    }
}
