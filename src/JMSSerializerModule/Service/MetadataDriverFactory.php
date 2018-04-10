<?php

namespace JMSSerializerModule\Service;

use Application\Module;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use JMS\Serializer\Handler\DateHandler;
use Metadata\Driver\FileLocator;
use Zend\ModuleManager\ModuleManager;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @author Martin Parsiegla <martin.parsiegla@gmail.com>
 */
class MetadataDriverFactory implements FactoryInterface
{

    /**
     * @var string
     */
    protected $driver;

    /**
     * @param string $driver
     */
    public function __construct($driver)
    {
        $this->driver = $driver;
    }


    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $driver = $this->driver;
        $fileLocator = $serviceLocator->get('jms_serializer.metadata.file_locator');
        $driver = new $driver($fileLocator);

        return $driver;
    }

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return $this->createService($container);
    }
}
