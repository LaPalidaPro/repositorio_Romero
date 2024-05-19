<?php

namespace App\Controller;

use App\Entity\Cancion;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\JsonResponse;

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
    #[Route('/harmonyhub/{pagina}', name: 'app_home', defaults: ['pagina' => 1])]
    public function index(int $pagina): Response
    {
        $limite = 8; // Número de canciones por página

        $query = $this->em->getRepository(Cancion::class)
            ->createQueryBuilder('c')
            ->setFirstResult(($pagina - 1) * $limite) // Calcula el offset
            ->setMaxResults($limite) // Limita la cantidad de resultados
            ->getQuery();

        $paginator = new Paginator($query, $fetchJoinCollection = true);

        $totalCanciones = count($paginator);
        $totalPaginas = ceil($totalCanciones / $limite);

        return $this->render("home/index.html.twig", [
            'datosCanciones' => $paginator,
            'paginaActual' => $pagina,
            'totalPaginas' => $totalPaginas,
        ]);
    }

    #[Route('/obtener-detalles-cancion', name: 'obtener_detalles_cancion')]
    public function obtenerDetallesCancion(Request $request): JsonResponse
    {
        $songId = $request->query->get('songId');

        $cancion = $this->em->getRepository(Cancion::class)->find($songId);

        if (!$cancion) {
            return new JsonResponse(['error' => 'Canción no encontrada'], JsonResponse::HTTP_NOT_FOUND);
        }

        $songDetails = [
            'audioSrc' => '/music/' . $cancion->getNombreArchivo(),
            'titulo' => $cancion->getTitulo(),
            'artista' => $cancion->getArtista()->getNombre(),  // Asumiendo relación con Artista
        ];

        return new JsonResponse($songDetails);
    }

    #[Route('/harmonyhub/cancion', name: 'app_cancion')]
    public function mostrarCancion(): Response
    {
        return $this->render("home/cancion.html.twig");
    }
}
