<?php

namespace JhFlexiTimeTest\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use JhUser\Entity\User;

/**
 * Class SingleUser
 * @package JhUserTest\Fixture
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class SingleUser extends AbstractFixture
{
    /**
     * @var User
     */
    protected $user;

    /**
     * {inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->user = new User();

        $this->user
            ->setEmail('aydin@hotmail.co.uk')
            ->setPassword('password');

        $manager->persist($this->user);
        $manager->flush();
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
