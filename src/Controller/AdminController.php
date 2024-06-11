<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Cancion;
use App\Entity\Artista;
use App\Entity\User;
use App\Entity\Publicidad;
use App\Form\AlbumType;
use App\Form\ArtistaType;
use App\Form\PublicidadType;
use App\Repository\PublicidadRepository;
use App\Repository\CancionRepository;
use App\Repository\EventoRepository;
use App\Repository\UserRepository;

use Doctrine\ORM\EntityManagerInterface;
use getID3;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class AdminController extends AbstractController
{
    private $em;
    private $csrfTokenManager;
    private $logger;

    public function __construct(EntityManagerInterface $em, CsrfTokenManagerInterface $csrfTokenManager, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->logger = $logger;
    }

    #[Route('/harmonyhub/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/harmonyhub/admin/gestionContenido', name: 'app_gestionContenido')]
    public function gestionContenidos(): Response
    {
        $artistas = $this->em->getRepository(Artista::class)->findBy([], ['nombre' => 'ASC']);
        $forms = [];

        foreach ($artistas as $artista) {
            $form = $this->createForm(ArtistaType::class, $artista);
            $forms[$artista->getId()] = $form->createView();
        }

        return $this->render('admin/gestionContenido.html.twig', [
            'datos' => $artistas,
            'forms' => $forms,
        ]);
    }
}