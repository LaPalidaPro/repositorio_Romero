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
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class HomeController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    #[Route('/harmonyhub/cambioIdioma/{locale}', name: 'cambio_idioma')]
    public function cambioIdioma($locale, Request $request, SessionInterface $session): JsonResponse
    {
        // Almacenar el idioma en la sesión
        $session->set('_locale', $locale);

        // Devolver una respuesta JSON
        return new JsonResponse(['status' => 'success']);
    }
    #[Route('/', name: 'app_index')]
    public function irIndex(): RedirectResponse
    {
        return $this->redirectToRoute('app_home');
    }
    #[Route('/harmonyhub/principal/{pagina}', name: 'app_home', defaults: ['pagina' => 1])]
    public function index(int $pagina, Request $request): Response
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

        $cancionId = $request->query->get('cancion_id', null);
        $cancion = null;
        if ($cancionId) {
            $cancion = $this->em->getRepository(Cancion::class)->find($cancionId);
        }

        $tiempo = $request->query->get('tiempo', 0);
        $volumen = $request->query->get('volumen', 1);
        $corazon = $request->query->get('corazon', 0);

        return $this->render("home/index.html.twig", [
            'datosCanciones' => $paginator,
            'paginaActual' => $pagina,
            'totalPaginas' => $totalPaginas,
            'cancion_actual' => $cancion,
            'tiempo_actual' => $tiempo,
            'volumen_actual' => $volumen,
            'corazon_actual' => $corazon,
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
            'audioSrc' => '/music/' . $cancion->getNombre(),
            'titulo' => $cancion->getTitulo(),
            'artista' => $cancion->getArtista()->getNombre(),  
        ];

        return new JsonResponse($songDetails);
    }

    #[Route('/harmonyhub/cancion/{id}', name: 'app_cancion')]
    public function mostrarCancion(int $id, Request $request): Response
    {
        $cancion = $this->em->getRepository(Cancion::class)->find($id);

        if (!$cancion) {
            throw $this->createNotFoundException('La canción no existe');
        }

        $tiempo = $request->query->get('tiempo', 0);
        $volumen = $request->query->get('volumen', 1);
        $corazon = $request->query->get('corazon', 0);

        return $this->render('home/cancion.html.twig', [
            'cancion' => $cancion,
            'tiempo' => $tiempo,
            'volumen' => $volumen,
            'corazon' => $corazon,
            'audio_path' => '/music/' . $cancion->getTitulo()
        ]);
    }

    #[Route('/buscarCancion', name: 'buscar_cancion')]
    public function buscarCancion(Request $request): JsonResponse
    {
        $consulta = $request->query->get('query');
        $canciones = $this->em->getRepository(Cancion::class)->createQueryBuilder('c')
            ->join('c.artista', 'a')
            ->where('c.titulo LIKE :consulta')
            ->orWhere('a.nombre LIKE :consulta')
            ->setParameter('consulta', '%' . $consulta . '%')
            ->getQuery()
            ->getResult();

        $datos = [];
        foreach ($canciones as $cancion) {
            $datos[] = [
                'id' => $cancion->getId(),
                'titulo' => $cancion->getTitulo(),
                'artista' => $cancion->getArtista()->getNombre(),
                'audio_path' => '/music/' . $cancion->getTitulo()
            ];
        }

        return new JsonResponse($datos);
    }

    #[Route('/harmonyhub/reproductor', name: 'app_reproductor')]
    public function reproductor(Request $request): Response
    {
        $cancionId = $request->query->get('cancion_id');

        // Obtener la canción de la base de datos usando el ID
        $cancion = $this->em->getRepository(Cancion::class)->find($cancionId);

        // Verificar que la canción exista
        if (!$cancion) {
            throw $this->createNotFoundException('La canción no existe');
        }

        return $this->render('reproductor.html.twig', [
            'cancion' => $cancion,
            'tiempo' => $request->query->get('tiempo', 0),
            'volumen' => $request->query->get('volumen', 1),
            'corazon' => $request->query->get('corazon', 0)
        ]);
    }
}
