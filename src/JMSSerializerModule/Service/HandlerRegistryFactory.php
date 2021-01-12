<?php

namespace JMSSerializerModule\Service;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use InvalidArgumentException;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * @author Martin Parsiegla <martin.parsiegla@gmail.com>
 */
class HandlerRegistryFactory extends AbstractFactory
{

    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var $options \JMSSerializerModule\Options\Handlers */
        $options      = $this->getOptions($serviceLocator, 'handlers');
        $handlerRegistry = new HandlerRegistry();

        foreach ($options->getSubscribers() as $subscriberName) {
            $subscriber = $subscriberName;
            if (is_string($subscriber)) {
                if ($serviceLocator->has($subscriber)) {
                    $subscriber = $serviceLocator->get($subscriber);
                } elseif (class_exists($subscriber)) {
                    $subscriber = new $subscriber();
                }
            }

            if ($subscriber instanceof SubscribingHandlerInterface) {
                $handlerRegistry->registerSubscribingHandler($subscriber);
                continue;
            }
            throw new InvalidArgumentException(sprintf(
                'Invalid subscriber "%s" given, must be a service name, '
                    . 'class name or an instance implementing JMS\Serializer\Handler\SubscribingHandlerInterface;
',
                is_object($subscriberName)
                    ? get_class($subscriberName)
                    : (is_string($subscriberName) ? $subscriberName : gettype($subscriber))
            ));
        }

        return $handlerRegistry;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionsClass()
    {
        return 'JMSSerializerModule\Options\Handlers';
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
