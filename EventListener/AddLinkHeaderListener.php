<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\WebLink\EventListener;

use Psr\Link\LinkProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\WebLink\HttpHeaderSerializer;

// Help opcache.preload discover always-needed symbols
class_exists(HttpHeaderSerializer::class);

/**
 * Adds the Link HTTP header to the response.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 *
 * @final
 */
class AddLinkHeaderListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly HttpHeaderSerializer $serializer = new HttpHeaderSerializer(),
    ) {
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $linkProvider = $event->getRequest()->attributes->get('_links');
        if (!$linkProvider instanceof LinkProviderInterface || !$links = $linkProvider->getLinks()) {
            return;
        }

        $event->getResponse()->headers->set('Link', $this->serializer->serialize($links), false);
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => 'onKernelResponse'];
    }
}
