<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\UserType;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LoginController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    #[Route('/harmonyhub/login', name: 'app_login')]
    public function index(): Response
    {
        
        return $this->render('login/login.html.twig');
    }
    #[Route('/harmonyhub/registro', name: 'app_registro')]
    public function registro(Request $request, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $submittedToken = $request->request->get('token');

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->isCsrfTokenValid('miToken', $submittedToken)) {
                // Validar los datos del usuario
                $errors = $validator->validate($user);

                if (count($errors) > 0) {
                    return $this->render('login/registro.html.twig', [
                        'form' => $form->createView(),
                        'errors' => $errors,
                    ]);
                } else {
                    // Hashear la contraseña
                    $user->setPassword(
                        $passwordHasher->hashPassword($user, $user->getPassword())
                    );

                    // Guardar el usuario en la base de datos
                    $this->em->persist($user);
                    $this->em->flush();

                    // Redirigir o mostrar un mensaje de éxito
                    return $this->redirectToRoute('app_login');
                }
            } else {
                $this->addFlash('warning', 'Ocurrió un error inesperado');
                return $this->redirectToRoute('app_registro');
            }
        }

        return $this->render('login/registro.html.twig', [
            'form' => $form->createView(),
            'errors' => [],
        ]);
    }

    #[Route('/harmonyhub/logout', name: 'app_logout')]
    public function logout()
    {
        
    }
}
