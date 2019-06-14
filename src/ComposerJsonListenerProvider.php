<?php declare(strict_types=1);

namespace WyriHaximus\Broadcast;

use Composed\AbstractPackage;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use function WyriHaximus\get_in_packages_composer;
use function WyriHaximus\iteratorOrArrayToArray;
use function WyriHaximus\listClassesInFile;

final class ComposerJsonListenerProvider implements ListenerProviderInterface
{
    private $events = [];

    /** @var ContainerInterface */
    private $container;

    public function __construct(string $path, ContainerInterface $container)
    {
        $this->container = $container;
        $this->events = iteratorOrArrayToArray($this->iteratePackages(get_in_packages_composer($path)));
    }

    public function getListenersForEvent(object $event): iterable
    {
        $eventName = \get_class($event);

        if (!isset($this->events[$eventName])) {
            yield from [];

            return;
        }

        foreach ($this->events[$eventName] as $listener) {
            yield $this->container->get($listener);
        }
    }

    private function iteratePackages(iterable $packages): iterable
    {
        foreach ($packages as $package => $events) {
            yield from $this->locateEvents($package, $events);
        }
    }

    private function locateEvents(AbstractPackage $package, iterable $events): iterable
    {
        foreach ($events as $event => $listeners) {
            yield $event => iteratorOrArrayToArray($this->locateListeners($package, $listeners));
        }
    }

    private function locateListeners(AbstractPackage $package, iterable $paths): iterable
    {
        foreach ($paths as $listenerPaths) {
            yield from $this->listListenersInLocation($package, $listenerPaths);
        }
    }

    private function listListenersInLocation(AbstractPackage $package, string $location): iterable
    {
        if (\strpos($location, '*') !== false) {
            yield from $this->locateListeners($package, \glob($location));

            return;
        }

        yield from listClassesInFile($package->getPath($location));
    }
}
