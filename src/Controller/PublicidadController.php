<?php

namespace App\Controller;

use App\Entity\Publicidad;
use App\Form\PublicidadType;
use App\Repository\PublicidadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PublicidadController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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
    public function gestionPublicidad(PublicidadRepository $publicidadRepository, Request $request): Response
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

            $this->em->persist($publicidad);
            $this->em->flush();
            return $this->redirectToRoute('app_gestionPublicidad');
        }

        return $this->render('admin/gestionPublicidad.html.twig', [
            'publicidades' => $publicidades,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/harmonyhub/admin/gestionPublicidad/{id}/delete', name: 'publicidad_delete', methods: ['POST'])]
    public function delete(Publicidad $publicidad, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete'.$publicidad->getId(), $request->request->get('_token'))) {
            $this->em->remove($publicidad);
            $this->em->flush();
        }

        return $this->redirectToRoute('app_gestionPublicidad');
    }
}