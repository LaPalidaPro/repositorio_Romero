<?php

namespace App\Controller;

use App\Entity\Cancion;
use App\Entity\Favorito;
use App\Entity\User;
use App\Repository\CancionRepository;
use App\Repository\FavoritoRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class HomeController extends AbstractController
{
    private $em;
    private $csrfTokenManager;
    private $cancionRepository;

    public function __construct(EntityManagerInterface $em, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->em = $em;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->cancionRepository = $em->getRepository(Cancion::class);
    }

    #[Route('/harmonyhub/cambioIdioma/{locale}', name: 'cambio_idioma')]
    public function cambioIdioma($locale, Request $request, SessionInterface $session): JsonResponse
    {
        $session->set('_locale', $locale);
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
        $limite = 8;

        // Obtener el género seleccionado de la solicitud
        $generoSeleccionado = $request->query->get('genero');

        // Construir la consulta en base al género seleccionado
        $queryBuilder = $this->cancionRepository->createQueryBuilder('c');
        if ($generoSeleccionado) {
            $queryBuilder->where('c.generoMusical = :genero')
                ->setParameter('genero', $generoSeleccionado);
        }
        $queryBuilder->setFirstResult(($pagina - 1) * $limite)
            ->setMaxResults($limite);

        $paginator = new Paginator($queryBuilder->getQuery(), $fetchJoinCollection = true);
        $totalCanciones = count($paginator);
        $totalPaginas = ceil($totalCanciones / $limite);

        $cancionId = $request->query->get('cancion_id', null);
        $cancion = null;
        $favoritos = [];
        $user = $this->getUser();

        if ($user) {
            $favoritosEntities = $this->em->getRepository(Favorito::class)->findBy(['usuario' => $user]);
            foreach ($favoritosEntities as $favorito) {
                $favoritos[] = $favorito->getCancion()->getId();
            }
        }

        if ($cancionId) {
            $cancion = $this->em->getRepository(Cancion::class)->find($cancionId);
        }

        $tiempo = $request->query->get('tiempo', 0);
        $volumen = $request->query->get('volumen', 1);
        $corazon = $request->query->get('corazon', 0);

        // Obtener géneros únicos de la base de datos
        $generos = $this->cancionRepository->createQueryBuilder('c')
            ->select('DISTINCT c.generoMusical')
            ->getQuery()
            ->getArrayResult();

        // Convertir resultados a un array simple
        $generos = array_column($generos, 'generoMusical');

        return $this->render("home/index.html.twig", [
            'datosCanciones' => $paginator,
            'paginaActual' => $pagina,
            'totalPaginas' => $totalPaginas,
            'cancion_actual' => $cancion,
            'tiempo_actual' => $tiempo,
            'volumen_actual' => $volumen,
            'corazon_actual' => $corazon,
            'favoritos' => $favoritos,
            'isSearch' => false,
            'generos' => $generos,
            'genero_seleccionado' => $generoSeleccionado,
        ]);
    }

    #[Route('/harmonyhub/cargar-canciones', name: 'cargar_canciones')]
    public function cargarCanciones(Request $request): JsonResponse
    {
        $pagina = $request->query->get('pagina', 1);
        $limite = 8;
        $query = $this->em->getRepository(Cancion::class)
            ->createQueryBuilder('c')
            ->setFirstResult(($pagina - 1) * $limite)
            ->setMaxResults($limite)
            ->getQuery();

        $paginator = new Paginator($query, $fetchJoinCollection = true);

        $totalCanciones = count($paginator);
        $totalPaginas = ceil($totalCanciones / $limite);

        $favoritos = [];
        $user = $this->getUser();

        if ($user) {
            $favoritosEntities = $this->em->getRepository(Favorito::class)->findBy(['usuario' => $user]);
            foreach ($favoritosEntities as $favorito) {
                $favoritos[] = $favorito->getCancion()->getId();
            }
        }

        $html = $this->renderView('home/_canciones.html.twig', [
            'datosCanciones' => $paginator,
            'paginaActual' => $pagina,
            'totalPaginas' => $totalPaginas,
            'favoritos' => $favoritos,
        ]);

        return new JsonResponse(['html' => $html]);
    }

    #[Route('/favoritos/toggle/{id}', name: 'toggle_favorito', methods: ['POST'])]
    public function toggleFavorito(Cancion $cancion, Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['status' => 'error', 'message' => 'Necesitas estar logueado para realizar esta acción', 'redirect' => $this->generateUrl('app_login')], 403);
        }

        $csrfToken = $request->headers->get('X-CSRF-Token');
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('favorito', $csrfToken))) {
            return new JsonResponse(['status' => 'error', 'message' => 'Token CSRF inválido'], 403);
        }

        $favoritoRepo = $this->em->getRepository(Favorito::class);

        $favorito = $favoritoRepo->findOneBy([
            'usuario' => $user,
            'cancion' => $cancion,
        ]);

        if ($favorito) {
            $this->em->remove($favorito);
            $this->em->flush();
            return new JsonResponse(['status' => 'removed', 'message' => 'Canción eliminada de favoritos', 'favoritos' => $this->getUserFavoritos()]);
        } else {
            $nuevoFavorito = new Favorito();
            $nuevoFavorito->setUsuario($user);
            $nuevoFavorito->setCancion($cancion);

            $this->em->persist($nuevoFavorito);
            $this->em->flush();

            return new JsonResponse(['status' => 'added', 'message' => 'Canción añadida a favoritos', 'favoritos' => $this->getUserFavoritos()]);
        }
    }

    #[Route('/harmonyhub/reproductor', name: 'app_reproductor')]
    public function reproductor(Request $request): Response
    {
        $cancionId = $request->query->get('cancion_id');
        $user = $this->getUser();
        $cancion = null;
        $corazon = 0;

        if ($cancionId) {
            $cancion = $this->em->getRepository(Cancion::class)->find($cancionId);

            if ($cancion && $user) {
                $favorito = $this->em->getRepository(Favorito::class)->findOneBy([
                    'usuario' => $user,
                    'cancion' => $cancion,
                ]);

                $corazon = $favorito ? 1 : 0;
            }
        }

        return $this->render('reproductor.html.twig', [
            'cancion' => $cancion,
            'tiempo' => $request->query->get('tiempo', 0),
            'volumen' => $request->query->get('volumen', 1),
            'corazon' => $corazon,
            'favoritos' => $this->getUserFavoritos(), // Pasar los favoritos del usuario
        ]);
    }

    private function getUserFavoritos(): array
    {
        $user = $this->getUser();
        $favoritos = [];
        if ($user) {
            $favoritosEntities = $this->em->getRepository(Favorito::class)->findBy(['usuario' => $user]);
            foreach ($favoritosEntities as $favorito) {
                $favoritos[] = $favorito->getCancion()->getId();
            }
        }
        return $favoritos;
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
            'id' => $cancion->getId(),
            'audioSrc' => '/music/' . $cancion->getTitulo(),
            'titulo' => $cancion->getTitulo(),
            'artista' => $cancion->getArtista()->getNombre(),
            'imagen' => '/images/grupos/' . $cancion->getAlbum()->getFotoPortada(),
        ];

        return new JsonResponse($songDetails);
    }


    #[Route('/harmonyhub/cancion/{id}', name: 'app_cancion')]
    public function mostrarCancion(int $id, Request $request): Response
    {
        $cancion = $this->em->getRepository(Cancion::class)->find($id);
        $user = $this->getUser();

        if (!$cancion) {
            throw $this->createNotFoundException('La canción no existe');
        }

        $favorito = $this->em->getRepository(Favorito::class)->findOneBy([
            'usuario' => $user,
            'cancion' => $cancion,
        ]);

        $esFavorito = $favorito ? 1 : 0;

        return $this->render('home/detalleCancion.html.twig', [
            'cancion' => $cancion,
            'tiempo' => $request->query->get('tiempo', 0),
            'volumen' => $request->query->get('volumen', 1),
            'corazon' => $esFavorito,
            'audio_path' => '/music/' . $cancion->getTitulo()
        ]);
    }

    #[Route('/buscador', name: 'buscar_canciones', methods: ['GET'])]
    public function buscarCancion(Request $request, CancionRepository $cancionRepository, FavoritoRepository $favoritoRepository): JsonResponse
    {
        $query = $request->query->get('query');

        if (!$query) {
            return new JsonResponse(['error' => 'La consulta de búsqueda está vacía'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $canciones = $cancionRepository->findByNombre($query);

        if (!$canciones) {
            return new JsonResponse(['error' => 'No se encontraron canciones'], JsonResponse::HTTP_NOT_FOUND);
        }

        $usuario = $this->getUser();
        $favoritos = $usuario ? $favoritoRepository->findBy(['usuario' => $usuario]) : [];
        $favoritosIds = array_map(function ($favorito) {
            return $favorito->getCancion()->getId();
        }, $favoritos);

        $html = '<div class="row" id="contenedorCanciones">';
        foreach ($canciones as $cancion) {
            $tituloSinExtension = pathinfo($cancion->getTitulo(), PATHINFO_FILENAME);
            $isFavorito = in_array($cancion->getId(), $favoritosIds) ? 'true' : 'false';
            $html .= '
        <div class="col-md-3 mb-4">
            <div class="cardBtn">
                <div class="card" data-audio-src="/music/' . $cancion->getTitulo() . '" data-cancion="' . $tituloSinExtension . '" data-artista="' . $cancion->getArtista()->getNombre() . '" data-id="' . $cancion->getId() . '" data-favorito="' . $isFavorito . '">
                    <img src="/images/grupos/' . $cancion->getAlbum()->getFotoPortada() . '" class="card-img-top" alt="' . $tituloSinExtension . '">
                    <div class="card-body">
                        <h5 class="card-title">' . $tituloSinExtension . '</h5>
                        <p class="card-text">' . $cancion->getArtista()->getNombre() . '</p>
                    </div>
                </div>
            </div>
        </div>';
        }
        $html .= '</div>';

        return new JsonResponse(['html' => $html]);
    }


    #[Route('/sobremi', name: 'app_sobremi')]
    public function sobreMi(): Response
    {
        $cartaPresentacion = "Soy Celia, Recién graduada en Desarrollo de Aplicaciones Web. Estoy buscando una oportunidad para aplicar mis conocimientos y seguir aprendiendo.

        En el ámbito del desarrollo web, las prácticas las he desarrollado en Symfony, y también tengo conocimientos de HTML, CSS, JavaScript, PHP, Bootstrap, Spring Boot, Hibernate, MySQL y MongoDB.
        
        Me encantaría poder contribuir a proyectos innovadores y continuar creciendo en un entorno profesional. Agradezco tu tiempo y espero podamos conversar pronto.";
        return $this->render('home/sobremi.html.twig', [
            'image_path' => '/images/perfil/yo.jpg',
            'cv_path' => '/ficheros/CV_CeliaRomero.pdf',
            'carta_presentacion' => $cartaPresentacion,
        ]);
    }

    #[Route('/harmonyhub/top-canciones', name: 'top_canciones')]
    public function getTopCanciones(EntityManagerInterface $em): JsonResponse
    {
        $canciones = $em->getRepository(Cancion::class)->findBy([], ['numeroReproducciones' => 'DESC'], 10);

        $data = array_map(function (Cancion $cancion) {
            return [
                'imagen' => '/images/grupos/' . $cancion->getAlbum()->getFotoPortada(),
                'titulo' => $cancion->getTitulo(),
                'artista' => $cancion->getArtista()->getNombre(),
                'audioSrc' => '/music/' . $cancion->getTitulo(),
                'id' => $cancion->getId(),
            ];
        }, $canciones);

        return new JsonResponse($data);
    }
}
