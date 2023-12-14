<?php

namespace App\Command;

use App\Entity\Player;
use App\Repository\contracts\DatabasePool;
use App\Repository\contracts\DatabaseTypes;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-admin',
    description: '',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private DatabasePool $pool
    )
    {
        parent::__construct("app:create-admin");
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $player = new Player();
        $player->setCreatedAt(new \DateTimeImmutable());
        $player->setName('admin');
        $player->setId(Uuid::fromInteger(1));
        $this->pool->getObjectManager(DatabaseTypes::MYSQL)->persist($player);
        $this->pool->getObjectManager(DatabaseTypes::MYSQL)->flush();

        $io->success('Admin created!');

        return Command::SUCCESS;
    }
}
