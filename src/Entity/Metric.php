<?php

namespace App\Entity;

use App\Repository\MetricRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MetricRepository::class)]
#[ORM\Index(fields: ['project'])]
#[ORM\UniqueConstraint(columns: ['date', 'project', 'key'])]
class Metric
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    public function __construct(
        #[ORM\Column(Types::DATE_MUTABLE)]
        public \DateTime $date,

        #[ORM\Column(length: 255)]
        public string $project,

        #[ORM\Column(length: 255)]
        public string $key,

        #[ORM\Column]
        public float $value,
    ) {}

}
