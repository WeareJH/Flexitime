<?php

namespace JhFlexiTimeTest\Util;

use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

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

        return $serviceManager;
    }
}
