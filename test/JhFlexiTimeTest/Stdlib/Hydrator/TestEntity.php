<?php

namespace JhFlexiTimeTest\Stdlib\Hydrator;

class TestEntity
{
    protected $start;

    public function setStartTime(\DateTime $start)
    {
        $this->start = $start;
    }

    public function getStartTime()
    {
        return $this->start;
    }
}
