<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;

class Module implements ConsoleBannerProviderInterface
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * This method is defined in ConsoleBannerProviderInterface
     */
    public function getConsoleBanner(Console $console)
    {
        return 'e-mobility search engine';
    }

    /**
     * This method is defined in ConsoleUsageProviderInterface
     */
    public function getConsoleUsage(Console $console)
    {
        return array(
            'search-index build'     => '(Re-) build the search index',
            'search-index update'    => 'Update the search index'
        );
    }
}
