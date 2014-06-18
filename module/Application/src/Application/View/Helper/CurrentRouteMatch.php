<?php

namespace Application\View\Helper;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\View\Helper\AbstractHelper;

class CurrentRouteMatch extends AbstractHelper implements ServiceLocatorAwareInterface
{
    /**
     * @var Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var string
     */
    protected $matchedRouteName;

    /**
     * @param string|null $route
     * @return boolean|string
     */
    public function __invoke($route = null)
    {
        if ($this->matchedRouteName == null) {
            $routeMatch = $this->serviceLocator->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch();
            $this->matchedRouteName = $routeMatch->getMatchedRouteName();
        }

        if ($route !== null) {
            return $route == $this->matchedRouteName;
        }

        return $this->matchedRouteName;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}