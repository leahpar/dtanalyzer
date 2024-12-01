<?php

namespace App\Controller;

use App\Entity\Metric;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{

    #[Route('/', name: 'projects')]
    public function dashboard(EntityManagerInterface $em): Response
    {
        $metrics = $em->getRepository(Metric::class)->findAll();

        $projects = [];
        foreach ($metrics as $metric) {
            $projects[$metric->project][$metric->date->format('Ymd')][$metric->key] = $metric->value;
        }

        return $this->render('dashboard/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/{project}', name: 'project')]
    public function project(string $project, EntityManagerInterface $em): Response
    {
        $metrics = $em->getRepository(Metric::class)->findBy(['project' => $project], ['date' => 'ASC']);

        $data = [];
        foreach ($metrics as $metric) {
            $data[$metric->date->format('Y-m-d')][$metric->key] = $metric->value;
        }

        return $this->render('dashboard/project.html.twig', [
            'project' => $project,
            'data' => $data,
        ]);
    }

}
