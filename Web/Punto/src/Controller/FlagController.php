<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;

class FlagController extends AbstractController
{
    public function __construct(
        private CacheInterface $cache
    ) { }

    #[Route('/flag', name: 'app_flag')]
    public function index(): Response
    {
        if($this->cache->get('printFlag', function () {
            return false;
        })){
            return new Response($this->cache->get('flag', function () {
                return 'Flag isnt present, contact admin';
            }));
        } else {
            return new Response('You cant see the flag, try harder');
        }
    }
}
