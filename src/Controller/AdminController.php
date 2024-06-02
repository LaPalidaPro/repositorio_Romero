<?php

// src/Controller/AdminController.php

namespace App\Controller;

use App\Entity\Artista;
use App\Entity\Cancion;
use App\Entity\User;
use App\Form\ArtistaType;
use App\Form\AlbumType;
use App\Entity\Album;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Psr\Log\LoggerInterface;

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

    #[Route('/admin/crearAlbum', name: 'app_crearAlbum')]
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
            $this->addFlash('error', 'Datos del request: ' . json_encode($allRequestData));

            // Manejar la subida de canciones
            if (isset($allRequestData['canciones'])) {
                $cancionesPaths = $allRequestData['canciones'];
                if (is_array($cancionesPaths) && !empty($cancionesPaths)) {
                    foreach ($cancionesPaths as $cancionPath) {
                        $cancion = new Cancion();
                        $cancion->setTitulo(basename($cancionPath));
                        $cancion->setAlbum($album);
                        $cancion->setArtista($album->getArtista());
                        $cancion->setDuracion(0); // Asigna un valor predeterminado o calcula la duración
                        $cancion->setFechaLanzamiento(new \DateTime()); // Asigna la fecha de lanzamiento actual
                        $cancion->setGeneroMusical('Desconocido'); // Asigna un valor predeterminado o usa un valor real
                        $cancion->setNumeroReproducciones(0); // Asigna un valor predeterminado
                        $this->em->persist($cancion);
                        $album->addCancion($cancion);
                    }
                } else {
                    $this->addFlash('error', 'No se han recibido canciones.');
                }
            } else {
                $this->addFlash('error', 'No se han recibido canciones.');
            }

            // Calcular la duración total del álbum antes de persistir
            $album->calculateDuracionTotal();
            $album->setNumPistas(0);

            $this->em->persist($album);
            $this->em->flush();

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

    #[Route('/upload', name: 'upload', methods: ['POST'])]
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

    #[Route('/harmonyhub/admin/gestionUsuarios', name: 'app_gestionUsuarios')]
    public function gestionUsuarios(): Response
    {
        $datos = $this->em->getRepository(User::class)->findBy([], ['nombre' => 'ASC']);
        return $this->render('admin/gestionUsuarios.html.twig', compact('datos'));
    }
}
