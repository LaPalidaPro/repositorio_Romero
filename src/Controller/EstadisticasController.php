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

class EstadisticasController extends AbstractController
{
    #[Route('/admin/estadisticas', name: 'app_estadisticas')]
    public function getEstadisticas(CancionRepository $cancionRepository, EventoRepository $eventoRepository, UserRepository $userRepository): Response
    {
        $mostPlayedSongs = $cancionRepository->findMostPlayedSongs();
        $songsByGenre = $cancionRepository->findSongsByGenre();
        $mostPopularEvents = $eventoRepository->findMostPopularEvents();
        $mostActiveUsers = $userRepository->findMostActiveUsers();

        return $this->render('admin/estadisticas.html.twig', [
            'mostPlayedSongs' => $mostPlayedSongs,
            'songsByGenre' => $songsByGenre,
            'mostPopularEvents' => $mostPopularEvents,
            'mostActiveUsers' => $mostActiveUsers,
        ]);
    }
}