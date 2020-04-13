<?php declare(strict_types=1);

namespace WyriHaximus\Broadcast;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use WyriHaximus\Broadcast\Generated\AbstractListenerProvider;

/**
 * This class is generated by wyrihaximus/broadcast and inspired by bmack/kart-composer-plugin
 */
final class ContainerListenerProvider extends AbstractListenerProvider implements ListenerProviderInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param array<array{class: string, method: string, static: bool}> $listeners
     *
     * @return iterable<callable>
     */
    protected function prepareCallable(array $listeners): iterable
    {
        foreach ($listeners as $args) {
            if ($args['static']) {
                yield $args['class'] . '::' . $args['method'];

                continue;
            }

            yield [$this->container->get($args['class']), $args['method']];
        }
    }
}
