<?php
namespace App\Controller;

use App\Entity\Album;
use App\Entity\Cancion;
use App\Entity\Artista;
use App\Form\AlbumType;

use Doctrine\ORM\EntityManagerInterface;
use getID3;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Psr\Log\LoggerInterface;

class AlbumController extends AbstractController
{
    private $em;
    private $logger;
    private $csrfTokenManager;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->csrfTokenManager = $csrfTokenManager;
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

            $allRequestData = $request->request->all();

            $totalDuration = 0;
            $numPistas = $album->getNumPistas();
            if (isset($allRequestData['canciones'])) {
                $cancionesPaths = $allRequestData['canciones'];
                if (is_array($cancionesPaths) && !empty($cancionesPaths)) {
                    foreach ($cancionesPaths as $cancionPath) {
                        $filePath = $this->getParameter('audio_directory') . '/' . $cancionPath;
                        $getID3 = new getID3();
                        $fileInfo = $getID3->analyze($filePath);
                        $duracionSegundos = isset($fileInfo['playtime_seconds']) ? $fileInfo['playtime_seconds'] : 0;

                        $this->logger->info('Duración de ' . basename($cancionPath) . ': ' . $duracionSegundos . ' segundos');

                        $duracionFormateada = $this->formatDuration($duracionSegundos);
                        $totalDuration += $duracionSegundos;

                        $cancion = new Cancion();
                        $cancion->setTitulo(basename($cancionPath));
                        $cancion->setAlbum($album);
                        $cancion->setArtista($album->getArtista());
                        $cancion->setDuracion($duracionFormateada);
                        $cancion->setFechaLanzamiento(new \DateTime());
                        $cancion->setGeneroMusical(implode(',', $album->getGenerosMusicales()));
                        $cancion->setNumeroReproducciones(0);
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

            $album->calculateDuracionTotal();
            $album->setNumPistas($numPistas);
            $this->em->persist($album);
            $this->em->flush();
            $this->addFlash('success', 'Álbum actualizado con éxito.');
            return $this->redirectToRoute('app_gestionContenido');
        }

        $canciones = $album->getCanciones();

        return $this->render('admin/editarAlbum.html.twig', [
            'form' => $form->createView(),
            'album' => $album,
            'canciones' => $canciones,
        ]);
    }

    #[Route('/harmonyhub/admin/removeCancion/{id}', name: 'app_remove_cancion', methods: ['POST'])]
    public function removeCancion(Request $request, int $id): Response
    {
        $cancion = $this->em->getRepository(Cancion::class)->find($id);
        if (!$cancion) {
            $this->addFlash('error', 'Canción no encontrada.');
            return $this->redirectToRoute('app_gestionContenido');
        }
    
        $albumId = $cancion->getAlbum()->getId();
        $album = $cancion->getAlbum();
        $album->calculateDuracionTotal();
        $csrfToken = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete' . $cancion->getId(), $csrfToken)) {
            $album->setNumPistas($album->getNumPistas()-1);
            $this->em->remove($cancion);
            $this->em->flush();
            $this->addFlash('success', 'Canción eliminada correctamente.');
        } else {
            $this->addFlash('error', 'Token CSRF no válido.');
        }
    
        return $this->redirectToRoute('app_editarAlbum', ['id' => $albumId]);
    }
    

    private function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}