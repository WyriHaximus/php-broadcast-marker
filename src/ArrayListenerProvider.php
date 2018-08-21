<?php declare(strict_types=1);

namespace WyriHaximus\Broadcast;

use Psr\EventDispatcher\EventInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

final class ArrayListenerProvider implements ListenerProviderInterface
{
    private $events = [];

    public function __construct(array $events)
    {
        $this->events = $events;
    }

    public function getListenersForEvent(EventInterface $event): iterable
    {
        $eventName = get_class($event);

        if (isset($this->events[$eventName])) {
            yield from $this->events[$eventName];
        }

        return [];
    }
}
