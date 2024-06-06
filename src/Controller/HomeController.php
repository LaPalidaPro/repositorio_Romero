<?php

namespace App\Controller;

use App\Entity\Cancion;
use App\Entity\Favorito;
use App\Entity\User;
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

    public function __construct(EntityManagerInterface $em, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->em = $em;
        $this->csrfTokenManager = $csrfTokenManager;
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
        $query = $this->em->getRepository(Cancion::class)
            ->createQueryBuilder('c')
            ->setFirstResult(($pagina - 1) * $limite)
            ->setMaxResults($limite)
            ->getQuery();

        $paginator = new Paginator($query, $fetchJoinCollection = true);

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

        return $this->render("home/index.html.twig", [
            'datosCanciones' => $paginator,
            'paginaActual' => $pagina,
            'totalPaginas' => $totalPaginas,
            'cancion_actual' => $cancion,
            'tiempo_actual' => $tiempo,
            'volumen_actual' => $volumen,
            'corazon_actual' => $corazon,
            'favoritos' => $favoritos
        ]);
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

    private function getUserFavoritos()
    {
        $user = $this->getUser();
        $favoritos = [];

        if ($user) {
            $favoritos = $this->em->getRepository(Favorito::class)
                ->createQueryBuilder('f')
                ->select('IDENTITY(f.cancion) as cancion_id')
                ->where('f.usuario = :user')
                ->setParameter('user', $user)
                ->getQuery()
                ->getResult();
            
            $favoritos = array_column($favoritos, 'cancion_id');
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
        $user = $this->getUser();

        if (!$cancion) {
            throw $this->createNotFoundException('La canción no existe');
        }

        $favorito = $this->em->getRepository(Favorito::class)->findOneBy([
            'usuario' => $user,
            'cancion' => $cancion,
        ]);

        $esFavorito = $favorito ? 1 : 0;

        return $this->render('home/cancion.html.twig', [
            'cancion' => $cancion,
            'tiempo' => $request->query->get('tiempo', 0),
            'volumen' => $request->query->get('volumen', 1),
            'corazon' => $esFavorito,
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

    #[Route('/sobremi', name: 'app_sobremi')]
    public function sobreMi(): Response
    {
        $cartaPresentacion = "Soy Celia, Recién graduada en Desarrollo de Aplicaciones Web. Estoy buscando una oportunidad para aplicar mis conocimientos y seguir aprendiendo.

        En el ámbito del desarrollo web, las prácticas las he desarrollado en Symfony, y también tengo conocimientos de HTML, CSS, JavaScript, PHP, Bootstrap, Spring Boot, Hibernate, MySQL y MongoDB.
        
        Me encantaría poder contribuir a proyectos innovadores y continuar creciendo en un entorno profesional. Agradezco tu tiempo y espero podamos conversar pronto.";
        return $this->render('home/sobremi.html.twig', [
            'image_path' => '/images/perfil/yo.jpg', // Asegúrate de que esta ruta sea correcta
            'cv_path' => '/ficheros/CV_CeliaRomero.pdf', // Asegúrate de que esta ruta sea correcta
            'carta_presentacion' => $cartaPresentacion,
        ]);
    }
}
