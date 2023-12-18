<?php

namespace App\EntityListener;

use GraphAware\Neo4j\OGM\EntityManager;
use GraphAware\Neo4j\OGM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

#[AsEventListener(
    event: ResponseEvent::class,
    method: 'onKernelResponse',
    priority: -100
)]
class Neo4jFlushListener
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) { }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $this->entityManager->flush();
    }
}
