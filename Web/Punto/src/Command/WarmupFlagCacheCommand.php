<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Cache\CacheInterface;

#[AsCommand(
    name: 'app:warmup-flag',
    description: 'Add a short description for your command',
)]
class WarmupFlagCacheCommand extends Command
{
    public function __construct(
        private CacheInterface $cache
    )
    {
        parent::__construct("app:warmup-flag");
    }

    protected function configure(): void
    {
        $this
            ->addArgument('flag', InputArgument::REQUIRED, 'The flag to warmup')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $flag = $input->getArgument('flag');

        if ($flag) {
            $this->cache->delete("flag");
            $this->cache->delete("superAdminActivate");
            $this->cache->get("flag", function () use ($flag) {
                return $flag;
            });
            $this->cache->get("superAdminActivate", function () use ($flag) {
                return false;
            });
        }

        $io->success('Cache warmed up!');

        return Command::SUCCESS;
    }
}
