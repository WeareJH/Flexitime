<?php

namespace JhFlexiTime\Install;


use JhHub\Install\InstallerInterface;
use Zend\Console\Adapter\AdapterInterface;

class Installer implements InstallerInterface
{
    /**
     * @param AdapterInterface $console
     * @return void
     */
    public function install(AdapterInterface $console)
    {
        $console->writeLine(sprintf("Hellow from %s", __CLASS__));
    }


} 