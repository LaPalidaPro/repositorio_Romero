<?php

namespace App\Controller;

use App\Entity\Cancion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CancionesController extends AbstractController
{
    #[Route('/incrementar-reproducciones/{id}', name: 'incrementar_reproducciones', methods: ['POST'])]
    public function incrementarReproducciones(Cancion $cancion, EntityManagerInterface $em): JsonResponse
    {
        $cancion->incrementarNumeroReproducciones();
        $em->persist($cancion);
        $em->flush();

        return new JsonResponse(['status' => 'success', 'numeroReproducciones' => $cancion->getNumeroReproducciones()]);
    }
}
