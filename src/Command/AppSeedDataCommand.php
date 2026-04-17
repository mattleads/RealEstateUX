<?php

namespace App\Command;

use App\Entity\Amenity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-data',
    description: 'Seeds the database with sample data',
)]
class AppSeedDataCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $amenities = ['Pool', 'Garage', 'Balcony', 'Garden', 'Gym', 'Air Conditioning', 'Elevator', 'Security System'];

        foreach ($amenities as $name) {
            $amenity = new Amenity();
            $amenity->setName($name);
            $this->entityManager->persist($amenity);
        }

        $this->entityManager->flush();

        $io->success('Sample data seeded!');

        return Command::SUCCESS;
    }
}
