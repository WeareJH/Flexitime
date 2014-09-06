<?php

namespace JhFlexiTime\Entity;

use Doctrine\ORM\Mapping as ORM;
use ZfcUser\Entity\UserInterface;
use JhFlexiTime\DateTime\DateTime;
use JsonSerializable;

/**
 * Class Booking
 * @package JhFlexiTime\Entity
 * @author Ben Lill <ben@wearejh.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="booking")
 */
class Booking implements JsonSerializable
{

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="JhUser\Entity\User")
     */
    protected $user = null;

    /**
     * @var DateTime
     *
     * @ORM\Id
     * @ORM\Column(type="date", name="date", nullable=false)
     */
    protected $date;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="time", name="start_time", nullable=false)
     */
    protected $startTime;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="time", name="end_time", nullable=false)
     */
    protected $endTime;

    /**
     * @var int
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $total = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $balance = 0;

    /** @var string
     *
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    protected $notes = null;

    /**
     * Set Defaults
     */
    public function __construct()
    {
        $this->date         = new DateTime("today");
        $this->startTime    = new DateTime('09:00:00');
        $this->endTime      = new DateTime('17:30:00');
    }

    /**
     * @return string
     */
    public function getId()
    {
        if (!$this->user) {
            throw new \RuntimeException("No User is set. Needed to generate ID");
        }

        return $this->date->format('U') . "-" . $this->user->getId();
    }

    /**
     *
     * @param \ZfcUser\Entity\UserInterface $user
     * @return \JhFlexiTime\Entity\Booking
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
     * @param DateTime $date
     * @return \JhFlexiTime\Entity\Booking
     */
    public function setDate(DateTime $date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param DateTime $startTime
     * @return \JhFlexiTime\Entity\Booking
     */
    public function setStartTime(DateTime $startTime)
    {
        $this->startTime = $startTime;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @param DateTime $endTime
     * @return \JhFlexiTime\Entity\Booking
     */
    public function setEndTime(DateTime $endTime)
    {
        $this->endTime = $endTime;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @param string $total
     * @return \JhFlexiTime\Entity\Booking
     */
    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

    /**
     * @return string
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param int $balance
     * @return \JhFlexiTime\Entity\Booking
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;
        return $this;
    }

    /**
     * @return int
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param string $notes
     * @return \JhFlexiTime\Entity\Booking
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array(
            'id'        => $this->getId(),
            'user'      => $this->user->getId(),
            'date'      => $this->date->format('d-m-Y'),
            'startTime' => $this->startTime->format('H:i'),
            'endTime'   => $this->endTime->format('H:i'),
            'total'     => $this->total,
            'balance'   => $this->balance,
            'notes'     => $this->notes,
        );
    }
}
