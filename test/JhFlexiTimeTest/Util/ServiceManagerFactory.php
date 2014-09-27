<?php

namespace JhFlexiTimeTest\Util;

use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceLocatorInterface;

use Doctrine\Common\DataFixtures\Purger\ORMPurger as FixturePurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor as FixtureExecutor;

use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\DBAL\Types\Type;

/**
 * Base test case to be used when a new service manager instance is required
 *
 * @license MIT
 * @link    https://github.com/zf-fr/ZfrRest
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
abstract class ServiceManagerFactory
{
    /**
     * @var array
     */
    private static $config = [];

    /**
     * @static
     * @param array $config
     */
    public static function setApplicationConfig(array $config)
    {
        static::$config = $config;
    }

    /**
     * @static
     * @return array
     */
    public static function getApplicationConfig()
    {
        return static::$config;
    }

    /**
     * @param array|null $config
     * @return ServiceManager
     */
    public static function getServiceManager(array $config = null)
    {
        $config = $config ?: static::getApplicationConfig();

        $serviceManager = new ServiceManager(
            new ServiceManagerConfig(
                isset($config['service_manager']) ? $config['service_manager'] : []
            )
        );
        $serviceManager->setService('ApplicationConfig', $config);

        /* @var $moduleManager \Zend\ModuleManager\ModuleManagerInterface */
        $moduleManager = $serviceManager->get('ModuleManager');

        $moduleManager->loadModules();

        // @todo move to own factory class/add to merged configuration? Create a test module?
        $serviceManager->setFactory(
            'Doctrine\Common\DataFixtures\Executor\AbstractExecutor',
            function (ServiceLocatorInterface $sl) {
                /* @var $em \Doctrine\ORM\EntityManager */
                $em = $sl->get('Doctrine\ORM\EntityManager');
                $schemaTool = new SchemaTool($em);

                Type::overrideType('date', 'JhFlexiTime\DBAL\Types\DateType');
                Type::overrideType('time', 'JhFlexiTime\DBAL\Types\TimeType');

                $schemaTool->dropDatabase();
                $schemaTool->createSchema($em->getMetadataFactory()->getAllMetadata());
                return new FixtureExecutor($em, new FixturePurger($em));
            }
        );

        return $serviceManager;
    }
}
