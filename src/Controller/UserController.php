<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRolesType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/harmonyhub/admin/gestionUsuarios', name: 'app_gestionUsuarios')]
    public function gestionUsuarios(): Response
    {
        $datos = $this->em->getRepository(User::class)->findBy([], ['nombre' => 'ASC']);
        return $this->render('admin/gestionUsuarios.html.twig', compact('datos'));
    }
    #[Route('/admin/editarUsuarioRoles/{id}', name: 'app_editarUsuarioRoles')]
    public function editarUsuarioRoles(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $usuario = $em->getRepository(User::class)->find($id);

        if (!$usuario) {
            throw $this->createNotFoundException('Usuario no encontrado');
        }

        $form = $this->createForm(UserRolesType::class, $usuario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Roles del usuario actualizados correctamente.');
            return $this->redirectToRoute('app_gestionUsuarios');
        }

        return $this->render('admin/editarUsuarioRoles.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
