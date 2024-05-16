<?php

namespace App\Controller;

use App\Entity\Cancion;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class HomeController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/', name: 'app_index')]
    public function irIndex(): RedirectResponse
    {
        return $this->redirectToRoute('app_home');
    }
    #[Route('/harmonyhub', name: 'app_home')]
    public function index(): Response
    {
        $datosCanciones = $this->em->getRepository(Cancion::class)->findAll();
        return $this->render("home/index.html.twig", compact('datosCanciones'));
    }
    #[Route('/harmonyhub/cancion', name: 'app_cancion')]
    public function mostrarCancion(): Response
    {
        return $this->render("home/cancion.html.twig");
    }

}
