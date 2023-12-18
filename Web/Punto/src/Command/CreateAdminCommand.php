<?php

namespace App\Command;

use App\Entity\Player;
use App\Repository\contracts\DatabasePool;
use App\Repository\contracts\DatabaseTypes;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
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

        if($this->pool->getPlayerRepository(DatabaseTypes::MYSQL)->getPlayer(Uuid::fromInteger(1)) !== null){
            $io->error('Admin already created!');
            return Command::FAILURE;
        }

        $player = new Player();
        $player->setCreatedAt(new \DateTimeImmutable());
        $player->setName('admin');
        $player->setId(Uuid::fromInteger(1));

        $metadata = $this->pool->getObjectManager(DatabaseTypes::MYSQL)->getClassMetaData(Player::class);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());

        $this->pool->getObjectManager(DatabaseTypes::MYSQL)->persist($player);
        $this->pool->getObjectManager(DatabaseTypes::MYSQL)->flush();

        $io->success('Admin created!');

        return Command::SUCCESS;
    }
}
