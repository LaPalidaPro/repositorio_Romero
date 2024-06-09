<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Cancion;
use App\Entity\Favorito;
use App\Form\EditarUsuarioType;
use App\Repository\FavoritoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Doctrine\ORM\Tools\Pagination\Paginator;

class UsuarioController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/perfil/editar', name: 'app_perfil')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function editarPerfil(Request $request, EntityManagerInterface $entityManager): Response
    {
        $usuario = $this->getUser();

        if (!$usuario instanceof User) {
            throw $this->createAccessDeniedException('No estás autenticado.');
        }

        $form = $this->createForm(EditarUsuarioType::class, $usuario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fotoFile = $form->get('foto')->getData();
            if ($fotoFile) {
                $newFilename = uniqid().'.'.$fotoFile->guessExtension();

                try {
                    $fotoFile->move(
                        $this->getParameter('images_perfil'),
                        $newFilename
                    );
                    $usuario->setFoto($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'No se pudo subir la foto de perfil.');
                }
            }

            $entityManager->persist($usuario);
            $entityManager->flush();

            $this->addFlash('success', 'Perfil actualizado correctamente.');

            return $this->redirectToRoute('app_perfil');
        }

        return $this->render('usuario/perfil.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/listaCanciones/{pagina}', name: 'app_misCanciones', defaults: ['pagina' => 1])]
    public function listaCanciones(int $pagina, Request $request, FavoritoRepository $favoritoRepository): Response
    {
        $limite = 8;
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $query = $favoritoRepository->createQueryBuilder('f')
            ->where('f.usuario = :usuario')
            ->setParameter('usuario', $user)
            ->join('f.cancion', 'c')
            ->setFirstResult(($pagina - 1) * $limite)
            ->setMaxResults($limite)
            ->getQuery();

        $paginator = new Paginator($query, $fetchJoinCollection = true);

        $totalFavoritos = count($paginator);
        $totalPaginas = ceil($totalFavoritos / $limite);

        $cancionId = $request->query->get('cancion_id', null);
        $cancion = null;
        $favoritos = [];

        if ($cancionId) {
            $cancion = $this->em->getRepository(Cancion::class)->find($cancionId);
        }

        $tiempo = $request->query->get('tiempo', 0);
        $volumen = $request->query->get('volumen', 1);
        $corazon = $request->query->get('corazon', 0);

        foreach ($paginator as $favorito) {
            $favoritos[] = $favorito->getCancion()->getId();
        }

        return $this->render('usuario/misCanciones.html.twig', [
            'datosCanciones' => $paginator,
            'paginaActual' => $pagina,
            'totalPaginas' => $totalPaginas,
            'cancion_actual' => $cancion,
            'tiempo_actual' => $tiempo,
            'volumen_actual' => $volumen,
            'corazon_actual' => $corazon,
            'favoritos' => $favoritos,
        ]);
    }
}
