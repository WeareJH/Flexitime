<?php

namespace JhFlexiTime\Controller;

use Zend\Validator\Date as DateValidator;


/**
 * Class GetSetDateTrait
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
trait GetSetDateTrait
{
    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @param string $month
     * @param string $year
     * @return \DateTime
     */
    public function getDate($month = null, $year = null) {

        if(!$this->date) {
            $validator  = new DateValidator(array('format' => 'M Y'));
            if ($validator->isValid(sprintf("%s %s", $month, $year))) {
                $period = new \DateTime(sprintf('last day of %s %s 23:59:59', $month, $year));
            } else {
                $period = new \DateTime;
            }
            $this->date = $period;
        }

        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
    }
}