<?php

namespace App\Command;

use App\Entity\Party;
use App\Entity\Player;
use App\Repository\contracts\DatabasePool;
use App\Repository\contracts\DatabaseTypes;
use App\Repository\ogm\PartyRepository;
use GraphAware\Neo4j\OGM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'neo4j:test',
    description: 'Test for neo4j',
)]
class Neo4jTestCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DatabasePool $pool
    )
    {
        parent::__construct("neo4j:test");
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Party $party */
         $party = $this->pool->getPartyRepository(DatabaseTypes::NEO4J)->find(Uuid::fromInteger(2));
        /** @var Player $player */
         $player = $this->pool->getPlayerRepository(DatabaseTypes::NEO4J)->find(Uuid::fromInteger(12));

        // $party = new Party();
        // $party->setCreatedAt(new \DateTimeImmutable());
        // $party->setRoundNumber(3);

        // $this->entityManager->persist($party);

        // $player = new Player();
        // $player->setName("Test");
        // $player->setCreatedAt(new \DateTimeImmutable());

        // $this->entityManager->persist($player);

        $party->addPlayer($player);

        // $this->entityManager->flush();

        $visited = [];
        $this->entityManager->getUnitOfWork()->traverseRelationshipEntities($party, $visited);
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
