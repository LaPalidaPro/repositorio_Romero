<?php

namespace App\Controller;

use App\Entity\Artista;
use App\Entity\Album;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

class AdminController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    #[Route('/harmonyhub/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }
    #[Route('/harmonyhub/admin/gestion', name: 'app_gestion')]
    public function gestionContenidos(): Response
    {
        $datos = $this->em->getRepository(Artista::class)->findBy([], ['nombre' => 'ASC']);
        return $this->render('admin/gestion.html.twig', compact('datos'));
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
    #[Route('/harmonyhub/admin/gestion/editarArtista{id}', name: 'app_editarArtista')]
    public function editarArtista(int $id): Response
    {
        $albumes = $this->em->getRepository(Album::class)->findBy(['artista' => $id]);
        $artista = $this->em->getRepository(Artista::class)->find($id);
        return $this->render('admin/editarArtista.html.twig', [
            'albumes' => $albumes,
            'artista' => $artista
        ]);
    }
    #[Route('/harmonyhub/admin/gestion/borrarArtista{id}', name: 'app_borrarArtista', methods: ['POST'])]
    public function borrarArtista(int $id, Request $request, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $csrfToken = $request->request->get('_token');

        if ($csrfTokenManager->isTokenValid(new CsrfToken('delete' . $id, $csrfToken))) {
            $artista = $this->em->getRepository(Artista::class)->find($id);

            if ($artista) {
                $this->em->remove($artista);
                $this->em->flush();
                $this->addFlash('success', 'Artista eliminado correctamente.');
            } else {
                $this->addFlash('error', 'El artista no existe.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF no válido.');
        }

        return $this->redirectToRoute('app_gestion');
    }
}