<?php

namespace JhFlexiTime\Entity;

use Doctrine\ORM\Mapping as ORM;
use ZfcUser\Entity\UserInterface;
use DateTime;
use JsonSerializable;

/**
 * Class UserSettings
 * @package JhFlexiTime\Entity
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @ORM\Entity
 * @ORM\Table(name="user_flexi_settings")
 */
class UserSettings implements JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="JhUser\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="flex_start_date", nullable=false)
     */
    protected $flexStartDate = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="time", name="default_start_time", nullable=false)
     */
    protected $defaultStartTime = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="time", name="default_end_time", nullable=false)
     */
    protected $defaultEndTime = null;

    /**
     * @var float
     *
     * @ORM\Column(type="float", name="start_balance", nullable=false)
     */
    protected $startingBalance = 0;

    /**
     *
     * @param \ZfcUser\Entity\UserInterface $user
     * @return self
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
     * @param DateTime $defaultStartTime
     * @return self
     */
    public function setDefaultStartTime(DateTime $defaultStartTime)
    {
        $this->defaultStartTime = $defaultStartTime;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDefaultStartTime()
    {
        return $this->defaultStartTime;
    }

    /**
     * @param DateTime $defaultEndTime
     * @return self
     */
    public function setDefaultEndTime(DateTime $defaultEndTime)
    {
        $this->defaultEndTime = $defaultEndTime;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDefaultEndTime()
    {
        return $this->defaultEndTime;
    }

    /**
     * @param DateTime $flexStartDate
     * @return self
     */
    public function setFlexStartDate(DateTime $flexStartDate)
    {
        $this->flexStartDate = $flexStartDate;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getFlexStartDate()
    {
        return $this->flexStartDate;
    }

    /**
     * @param float $startingBalance
     * @return self
     */
    public function setStartingBalance($startingBalance)
    {
        $this->startingBalance = $startingBalance;
        return $this;
    }

    /**
     * @return float
     */
    public function getStartingBalance()
    {
        return $this->startingBalance;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [];
    }
}
