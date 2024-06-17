<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\UserType;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Cookie;

class LoginController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/harmonyhub/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        // Verificar si el usuario está autenticado
        /** @var UserInterface $user */
        $user = $this->getUser();
        if ($user) {
            // Usuario autenticado, establecer la cookie con el SID
            $response = $this->redirectToRoute('home');

            // Crear la cookie con el SID del usuario
            $response->headers->setCookie(new Cookie(
                'session_id',          // Nombre de la cookie
                $user->getSid(),       // Valor de la cookie (SID del usuario)
                strtotime('now + 1 hour'),  // Fecha de expiración
                '/',                   // Ruta
                null,                  // Dominio
                true,                  // Sólo HTTPS
                true                   // HttpOnly
            ));

            return $response;
        }

        return $this->render('login/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/harmonyhub/registro', name: 'app_registro')]
    public function registro(Request $request, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $csrfToken = $request->request->get('_token');

        if ($form->isSubmitted()) {
            if ($this->isCsrfTokenValid('miToken', $csrfToken)) {
                $errors = $validator->validate($user);

                $existingUser = $this->em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);

                if ($existingUser) {
                    $this->addFlash('error', 'El email ya está registrado.');

                    return $this->render('login/registro.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }

                if (count($errors) > 0) {
                    foreach ($errors as $error) {
                        $this->addFlash('error', $error->getMessage());
                    }

                    return $this->render('login/registro.html.twig', [
                        'form' => $form->createView(),
                    ]);
                } else {
                    $user->setPassword(
                        $passwordHasher->hashPassword($user, $user->getPassword())
                    );

                    $user->setFoto('defaultImage.jpg');
                    $this->em->persist($user);
                    $this->em->flush();
                    $this->addFlash('success', 'Registro completado con éxito.');

                    return $this->redirectToRoute('app_login');
                }
            } else {
                $this->addFlash('error', 'Token CSRF no válido.');

                return $this->redirectToRoute('app_registro');
            }
        }

        return $this->render('login/registro.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/harmonyhub/logout', name: 'app_logout')]
    public function logout()
    {
        // El controlador de logout puede estar vacío
    }
}
