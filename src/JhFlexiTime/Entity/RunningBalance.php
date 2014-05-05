<?php

namespace JhFlexiTime\Entity;

use Doctrine\ORM\Mapping as ORM;
use ZfcUser\Entity\UserInterface;
use DateTime;
use JsonSerializable;

/**
 * Class RunningBalance
 * @package JhFlexiTime\Entity
 * @author Aydin Hassan <aydin@wearejh.com>
 * @ORM\Entity
 * @ORM\Table(name="running_balance")
 */
class RunningBalance implements JsonSerializable
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id = null;
    
    /**
     * @ORM\ManyToOne(targetEntity="JhUser\Entity\User")
     */
    protected $user = null;
    
     /**
     * @var int
     * 
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $balance = 0;
    
    /**
     * Set Defaults
     */
    public function __construct()
    {
        $this->date = new DateTime();
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @param \ZfcUser\Entity\UserInterface $user
     * @return \JhFlexiTime\Entity\RunningBalance
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return \ZfcUser\Entity\UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return int
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param int $balance
     * @return \JhFlexiTime\Entity\RunningBalance
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;
        return $this;
    }

    /**
     * @param float $balance
     */
    public function addBalance($balance)
    {
        $this->balance += (float) $balance;
    }

    /**
     * @param float $balance
     */
    public function subtractBalance($balance)
    {
        $this->balance -= (float) $balance;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function jsonSerialize()
    {
        if (!$this->user instanceof UserInterface) {
            throw new \Exception('User Must be an instance of \ZfcUser\Entity\UserInterface');
        }

        return array(
            'id'        => $this->id,
            'user'      => $this->user->getId(),
            'balance'   => $this->balance,
        );
    }
}
