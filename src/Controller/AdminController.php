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

    #[Route('/admin/crearArtista', name: 'app_crearArtista')]
    public function crearArtista(Request $request, EntityManagerInterface $entityManager): Response
    {
        $artista = new Artista();
        $form = $this->createForm(ArtistaType::class, $artista);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imgFile = $form->get('imgArtista')->getData();
            if ($imgFile) {
                $newFilename = uniqid() . '.' . $imgFile->guessExtension();
                try {
                    $imgFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $artista->setImgArtista($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error al subir la imagen');
                }
            }

            $entityManager->persist($artista);
            $entityManager->flush();

            return $this->redirectToRoute('app_gestionContenido');
        }

        return $this->render('admin/crearArtista.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/harmonyhub/admin/editarArtista/{id}', name: 'app_editarArtista', methods: ['GET', 'POST'])]
    public function editarArtista(Request $request, int $id): Response
    {
        $artista = $this->em->getRepository(Artista::class)->find($id);

        if (!$artista) {
            throw $this->createNotFoundException('El artista no existe');
        }

        $form = $this->createForm(ArtistaType::class, $artista);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imgFile = $form->get('imgArtista')->getData();
            if ($imgFile) {
                $filesystem = new Filesystem();
                $oldFilename = $artista->getImgArtista();

                $newFilename = uniqid() . '.' . $imgFile->guessExtension();
                try {
                    $imgFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );

                    // Si se sube la nueva imagen correctamente, eliminar la antigua
                    if ($oldFilename) {
                        $oldFilePath = $this->getParameter('images_directory') . '/' . $oldFilename;
                        if ($filesystem->exists($oldFilePath)) {
                            $filesystem->remove($oldFilePath);
                        }
                    }
                } catch (FileException $e) {
                    // Manejar excepción en caso de fallo de carga
                    $this->addFlash('error', 'Error al subir la imagen: ' . $e->getMessage());
                    return $this->redirectToRoute('app_gestionContenido');
                }
                $artista->setImgArtista($newFilename);
            }

            $this->em->flush();

            $this->addFlash('success', 'Artista actualizado correctamente.');

            return $this->redirectToRoute('app_gestionContenido');
        }

        return $this->render('admin/editarArtista.html.twig', [
            'artista' => $artista,
            'form' => $form->createView()
        ]);
    }
    #[Route('/harmonyhub/admin/actualizarImagen/{id}', name: 'app_actualizar_imagen', methods: ['POST'])]
    public function actualizarImagen(Request $request, int $id): JsonResponse
    {
        $artista = $this->em->getRepository(Artista::class)->find($id);

        if (!$artista) {
            return new JsonResponse(['error' => 'Artista no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(ArtistaType::class, $artista);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imgFile = $form->get('imgArtista')->getData();
            if ($imgFile) {
                $newFilename = uniqid() . '.' . $imgFile->guessExtension();
                try {
                    $imgFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $artista->setImgArtista($newFilename);
                    $this->em->flush();

                    return new JsonResponse([
                        'success' => true,
                        'newImageUrl' => '/images/grupos/' . $newFilename
                    ]);
                } catch (FileException $e) {
                    return new JsonResponse(['error' => 'Error al subir la imagen'], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        }

        return new JsonResponse(['error' => 'Formulario no válido'], Response::HTTP_BAD_REQUEST);
    }
    #[Route('/harmonyhub/admin/gestion/borrarArtista{id}', name: 'app_borrarArtista', methods: ['POST'])]
    public function borrarArtista(int $id, Request $request): Response
    {
        $csrfToken = $request->request->get('_token');

        if ($this->csrfTokenManager->isTokenValid(new CsrfToken('delete' . $id, $csrfToken))) {
            $artista = $this->em->getRepository(Artista::class)->find($id);

            if ($artista) {
                $filesystem = new Filesystem();
                $imgFilename = $artista->getImgArtista();
                if ($imgFilename) {
                    $imgFilePath = $this->getParameter('images_directory') . '/' . $imgFilename;
                    if ($filesystem->exists($imgFilePath)) {
                        $filesystem->remove($imgFilePath);
                    }
                }

                $this->em->remove($artista);
                $this->em->flush();
                $this->addFlash('success', 'Artista eliminado correctamente.');
            } else {
                $this->addFlash('error', 'El artista no existe.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF no válido.');
        }

        return $this->redirectToRoute('app_gestionContenido');
    }

    #[Route('/harmonyhub/admin/gestion/Albums{id}', name: 'app_gestionAlbums')]
    public function gestionAlbums(int $id): Response
    {
        $albumes = $this->em->getRepository(Album::class)->findBy(
            ['artista' => $id],
            ['fechaLanzamiento' => 'DESC']
        );
        $artista = $this->em->getRepository(Artista::class)->find($id);
        return $this->render('admin/gestionAlbums.html.twig', [
            'albumes' => $albumes,
            'artista' => $artista
        ]);
    }

    #[Route('/harmonyhub/admin/crearAlbum', name: 'app_crearAlbum')]
    public function crearAlbum(Request $request, SluggerInterface $slugger): Response
    {
        $album = new Album();
        $form = $this->createForm(AlbumType::class, $album);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fotoPortada = $form->get('fotoPortada')->getData();
            if ($fotoPortada) {
                $newFilename = $slugger->slug(pathinfo($fotoPortada->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . uniqid() . '.' . $fotoPortada->guessExtension();
                try {
                    $fotoPortada->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $album->setFotoPortada($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error al subir la imagen');
                }
            }

            // Depurar el contenido del request
            $allRequestData = $request->request->all();


            $totalDuration = 0;
            $numPistas = 0;
            // Manejar la subida de canciones
            if (isset($allRequestData['canciones'])) {
                $cancionesPaths = $allRequestData['canciones'];
                if (is_array($cancionesPaths) && !empty($cancionesPaths)) {
                    foreach ($cancionesPaths as $cancionPath) {
                        $filePath = $this->getParameter('audio_directory') . '/' . $cancionPath;
                        $getID3 = new getID3();
                        $fileInfo = $getID3->analyze($filePath);
                        $duracionSegundos = isset($fileInfo['playtime_seconds']) ? $fileInfo['playtime_seconds'] : 0;

                        // Verificar la duración en segundos
                        $this->logger->info('Duración de ' . basename($cancionPath) . ': ' . $duracionSegundos . ' segundos');

                        // Formatear la duración
                        $duracionFormateada = $this->formatDuration($duracionSegundos);
                        $totalDuration += $duracionSegundos;

                        $cancion = new Cancion();
                        $cancion->setTitulo(basename($cancionPath));
                        $cancion->setAlbum($album);
                        $cancion->setArtista($album->getArtista());
                        $cancion->setDuracion($duracionFormateada); // Guardar la duración formateada
                        $cancion->setFechaLanzamiento(new \DateTime()); // Asigna la fecha de lanzamiento actual
                        $cancion->setGeneroMusical(implode(',', $album->getGenerosMusicales())); // Asigna un valor predeterminado o usa un valor real
                        $cancion->setNumeroReproducciones(0); // Asigna un valor predeterminado
                        $this->em->persist($cancion);
                        $album->addCancion($cancion);
                        $numPistas++;
                    }
                } else {
                    $this->addFlash('error', 'No se han recibido canciones.');
                }
            } else {
                $this->addFlash('error', 'No se han recibido canciones.');
            }
            print_r(' total duracion: ' . $this->formatDuration($totalDuration));
            // Calcular y establecer la duración total del álbum antes de persistir
            $album->setDuracionTotal($this->formatDuration($totalDuration));
            print_r(' duracion del album: ' . $album->getDuracionTotal());
            $album->setNumPistas($numPistas);
            $this->em->persist($album);
            $this->em->flush();
            $this->addFlash('success', 'Album creado con exito.');
            return $this->redirectToRoute('app_gestionContenido');
        }

        // Definir las variables artistaNombre y albumNombre para la vista
        $artistaNombre = $album->getArtista() ? $album->getArtista()->getNombre() : '';
        $albumNombre = $album->getNombre() ?: '';

        return $this->render('admin/crearAlbum.html.twig', [
            'form' => $form->createView(),
            'artistaNombre' => $artistaNombre,
            'albumNombre' => $albumNombre,
        ]);
    }

    #[Route('/harmonyhub/admin/upload', name: 'upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        $archivo = $request->files->get('file');
        if ($archivo) {
            $artista = $request->get('artista');
            $album = $request->get('album');

            $audioDirectory = $this->getParameter('audio_directory') . '/' . $artista . '/' . $album;

            if (!is_dir($audioDirectory)) {
                mkdir($audioDirectory, 0777, true);
            }

            $originalFilename = $archivo->getClientOriginalName();
            try {
                $archivo->move(
                    $audioDirectory,
                    $originalFilename
                );
                return new JsonResponse(['path' => $artista . '/' . $album . '/' . $originalFilename], Response::HTTP_OK);
            } catch (FileException $e) {
                return new JsonResponse(['error' => 'Error al subir el archivo'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return new JsonResponse(['error' => 'No se ha recibido ningún archivo'], Response::HTTP_BAD_REQUEST);
    }
    private function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    #[Route('/harmonyhub/admin/eliminarAlbum/{id}', name: 'app_eliminarAlbum', methods: ['POST'])]
    public function eliminarAlbum(int $id, Request $request): Response
    {
        $csrfToken = $request->request->get('_token');

        if ($this->csrfTokenManager->isTokenValid(new CsrfToken('delete' . $id, $csrfToken))) {
            $album = $this->em->getRepository(Album::class)->find($id);

            if ($album) {
                // Eliminar las canciones asociadas al álbum
                foreach ($album->getCanciones() as $cancion) {
                    $this->em->remove($cancion);
                }

                // Eliminar el álbum
                $this->em->remove($album);
                $this->em->flush();

                $this->addFlash('success', 'Álbum eliminado correctamente.');
            } else {
                $this->addFlash('error', 'El álbum no existe.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF no válido.');
        }

        return $this->redirectToRoute('app_gestionAlbums', ['id' => $album->getArtista()->getId()]);
    }

    #[Route('/harmonyhub/admin/editarAlbum/{id}', name: 'app_editarAlbum', methods: ['GET', 'POST'])]
    public function editarAlbum(Request $request, int $id, SluggerInterface $slugger): Response
    {
        $album = $this->em->getRepository(Album::class)->find($id);

        if (!$album) {
            throw $this->createNotFoundException('El álbum no existe');
        }

        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fotoPortada = $form->get('fotoPortada')->getData();
            if ($fotoPortada) {
                $newFilename = $slugger->slug(pathinfo($fotoPortada->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . uniqid() . '.' . $fotoPortada->guessExtension();
                try {
                    $fotoPortada->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $album->setFotoPortada($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error al subir la imagen');
                }
            }

            // Depurar el contenido del request
            $allRequestData = $request->request->all();
            $this->addFlash('info', 'Datos del request: ' . json_encode($allRequestData));

            $totalDuration = 0;
            $numPistas = 0;
            // Manejar la subida de canciones
            if (isset($allRequestData['canciones'])) {
                $cancionesPaths = $allRequestData['canciones'];
                if (is_array($cancionesPaths) && !empty($cancionesPaths)) {
                    foreach ($cancionesPaths as $cancionPath) {
                        $filePath = $this->getParameter('audio_directory') . '/' . $cancionPath;
                        $getID3 = new getID3();
                        $fileInfo = $getID3->analyze($filePath);
                        $duracionSegundos = isset($fileInfo['playtime_seconds']) ? $fileInfo['playtime_seconds'] : 0;

                        // Verificar la duración en segundos
                        $this->logger->info('Duración de ' . basename($cancionPath) . ': ' . $duracionSegundos . ' segundos');

                        // Formatear la duración
                        $duracionFormateada = $this->formatDuration($duracionSegundos);
                        $totalDuration += $duracionSegundos;

                        $cancion = new Cancion();
                        $cancion->setTitulo(basename($cancionPath));
                        $cancion->setAlbum($album);
                        $cancion->setArtista($album->getArtista());
                        $cancion->setDuracion($duracionFormateada); // Guardar la duración formateada
                        $cancion->setFechaLanzamiento(new \DateTime()); // Asigna la fecha de lanzamiento actual
                        $cancion->setGeneroMusical(implode(',', $album->getGenerosMusicales())); // Asigna un valor predeterminado o usa un valor real
                        $cancion->setNumeroReproducciones(0); // Asigna un valor predeterminado
                        $this->em->persist($cancion);
                        $album->addCancion($cancion);
                        $numPistas++;
                    }
                } else {
                    $this->addFlash('error', 'No se han recibido canciones. 1');
                }
            } else {
                $this->addFlash('error', 'No se han recibido canciones. 2');
            }

            // Calcular y establecer la duración total del álbum antes de persistir
            $album->setDuracionTotal($totalDuration);
            $album->setNumPistas($numPistas);
            $this->em->persist($album);
            $this->em->flush();

            return $this->redirectToRoute('app_gestionContenido');
        }

        // Definir las variables artistaNombre y albumNombre para la vista
        $artistaNombre = $album->getArtista() ? $album->getArtista()->getNombre() : '';
        $albumNombre = $album->getNombre() ?: '';

        return $this->render('admin/editarAlbum.html.twig', [
            'form' => $form->createView(),
            'album' => $album,
            'artistaNombre' => $artistaNombre,
            'albumNombre' => $albumNombre,
        ]);
    }

    #[Route('/harmonyhub/admin/removeCancion', name: 'app_remove_cancion', methods: ['POST'])]
    public function removeCancion(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $cancionId = $data['id'];

        $cancion = $this->em->getRepository(Cancion::class)->find($cancionId);
        if ($cancion) {
            $this->em->remove($cancion);
            $this->em->flush();
            return new JsonResponse(['success' => true]);
        }

        return new JsonResponse(['error' => 'Canción no encontrada'], Response::HTTP_NOT_FOUND);
    }


    #[Route('/harmonyhub/admin/gestionUsuarios', name: 'app_gestionUsuarios')]
    public function gestionUsuarios(): Response
    {
        $datos = $this->em->getRepository(User::class)->findBy([], ['nombre' => 'ASC']);
        return $this->render('admin/gestionUsuarios.html.twig', compact('datos'));
    }

    #[Route('/carrusel', name: 'carousel_images')]
    public function getCarouselImages(PublicidadRepository $publicidadRepository): JsonResponse
    {
        $publicidades = $publicidadRepository->findAll();

        if (!$publicidades) {
            return new JsonResponse(['error' => 'No se encontraron publicidades'], 404);
        }

        $images = array_map(fn($publicidad) => '/images/publicidad/' . $publicidad->getImagen(), $publicidades);

        return new JsonResponse($images);
    }

    #[Route('/harmonyhub/admin/gestionPublicidad', name: 'app_gestionPublicidad')]
    public function gestionPublicidad(PublicidadRepository $publicidadRepository, Request $request, EntityManagerInterface $em): Response
    {
        // Obtener todas las publicidades
        $publicidades = $publicidadRepository->findAll();

        // Crear nuevo formulario de publicidad
        $publicidad = new Publicidad();
        $form = $this->createForm(PublicidadType::class, $publicidad);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imagen')->getData();

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_publi'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Handle the exception if something happens during file upload
                }

                $publicidad->setImagen($newFilename);
            }

            $em->persist($publicidad);
            $em->flush();
            return $this->redirectToRoute('app_gestionPublicidad');
        }

        return $this->render('admin/gestionPublicidad.html.twig', [
            'publicidades' => $publicidades,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/harmonyhub/admin/gestionPublicidad/{id}/delete', name: 'publicidad_delete', methods: ['POST'])]
    public function delete(Publicidad $publicidad, EntityManagerInterface $em, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete'.$publicidad->getId(), $request->request->get('_token'))) {
            $em->remove($publicidad);
            $em->flush();
        }

        return $this->redirectToRoute('app_gestionPublicidad');
    }

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
