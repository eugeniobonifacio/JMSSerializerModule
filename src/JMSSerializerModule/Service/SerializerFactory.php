<?php

namespace JMSSerializerModule\Service;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use InvalidArgumentException;
use JMS\Serializer\Serializer;
use JMS\Serializer\VisitorInterface;
use PhpCollection\Map;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * @author Martin Parsiegla <martin.parsiegla@gmail.com>
 */
class SerializerFactory extends AbstractFactory
{

    /**
     * {@inheritdoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var $options \JMSSerializerModule\Options\Visitors */
        $options = $this->getOptions($serviceLocator, 'visitors');

        return new Serializer(
            $serviceLocator->get('jms_serializer.metadata_factory'),
            $serviceLocator->get('jms_serializer.handler_registry'),
            $serviceLocator->get('jms_serializer.object_constructor'),
            $this->buildMap($serviceLocator, $options->getSerialization()),
            $this->buildMap($serviceLocator, $options->getDeserialization()),
            $serviceLocator->get('jms_serializer.event_dispatcher')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionsClass()
    {
        return 'JMSSerializerModule\Options\Visitors';
    }


    /**
     * @param \Laminas\ServiceManager\ServiceLocatorInterface $sl
     * @param array                                        $array
     *
     * @return \PhpCollection\Map
     * @throws \InvalidArgumentException
     */
    private function buildMap(ServiceLocatorInterface $sl, array $array)
    {
        $map = new Map();
        foreach ($array as $format => $visitorName) {
            $visitor = $visitorName;
            if (is_string($visitorName)) {
                if ($sl->has($visitorName)) {
                    $visitor = $sl->get($visitorName);
                } elseif (class_exists($visitorName)) {
                    $visitor = new $visitorName();
                }
            }

            if ($visitor instanceof VisitorInterface) {
                $map->set($format, $visitor);
                continue;
            }

            throw new InvalidArgumentException(sprintf(
                'Invalid (de-)serialization visitor"%s" given, must be a service name, '
                    . 'class name or an instance implementing JMS\Serializer\VisitorInterface',
                is_object($visitorName)
                    ? get_class($visitorName)
                    : (is_string($visitorName) ? $visitorName : gettype($visitor))
            ));
        }

        return $map;
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
