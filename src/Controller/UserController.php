<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class UserController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/harmonyhub/admin/gestionUsuarios', name: 'app_gestionUsuarios')]
    public function gestionUsuarios(): Response
    {
        $datos = $this->em->getRepository(User::class)->findBy([], ['nombre' => 'ASC']);
        return $this->render('admin/gestionUsuarios.html.twig', compact('datos'));
    }
}