<?php 
namespace App\Controller;

use App\Entity\Artista;
use App\Form\ArtistaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Filesystem\Filesystem;

class ArtistaController extends AbstractController
{
    private $em;
    private $csrfTokenManager;

    public function __construct(EntityManagerInterface $em, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->em = $em;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    #[Route('/admin/crearArtista', name: 'app_crearArtista')]
    public function crearArtista(Request $request): Response
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

            $this->em->persist($artista);
            $this->em->flush();

            $this->addFlash('success', 'Artista creado con éxito.');

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

                    if ($oldFilename) {
                        $oldFilePath = $this->getParameter('images_directory') . '/' . $oldFilename;
                        if ($filesystem->exists($oldFilePath)) {
                            $filesystem->remove($oldFilePath);
                        }
                    }
                } catch (FileException $e) {
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
}
