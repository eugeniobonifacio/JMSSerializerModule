<?php

namespace JMSSerializerModule\Service;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Metadata\Cache\FileCache;
use RuntimeException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * @author Martin Parsiegla <martin.parsiegla@gmail.com>
 */
class MetadataCacheFactory extends AbstractFactory
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var $options \JMSSerializerModule\Options\Metadata */
        $options = $this->getOptions($serviceLocator, 'metadata');
        if ($options->getCache() == 'none') {
            return null;
        } elseif ($options->getCache() == 'file') {
            $fileCache = $options->getFileCache();
            $dir = $fileCache['dir'];
            if (!file_exists($dir)) {
                if (!$rs = @mkdir($dir, 0777, true)) {
                    throw new RuntimeException(sprintf('Could not create cache directory "%s".', $dir));
                }
            }
            return new FileCache($dir);
        }

        return $serviceLocator->get($options->getCache());
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionsClass()
    {
        return 'JMSSerializerModule\Options\Metadata';
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
