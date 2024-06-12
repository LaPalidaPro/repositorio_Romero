<?php

// tests/Controller/AdminControllerTest.php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use App\Entity\User;
use Symfony\Component\BrowserKit\Cookie;

class AdminControllerTest extends WebTestCase
{
    private function logIn($client)
    {
        $container = $client->getContainer();
        $session = $container->get('session.factory')->createSession();

        // Obtener el usuario de prueba
        $user = $container->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'usu2@gmail.com']);
        
        if (!$user) {
            throw new \Exception('User not found');
        }
        
        // Crear el token de autenticación
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $session->set('_security_main', serialize($token));
        $session->save();

        // Configurar la cookie de sesión
        $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));
    }

    public function testIndex()
    {
        $client = static::createClient();
        $this->logIn($client);

        $crawler = $client->request('GET', '/harmonyhub/admin');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Menú Administrador'); // Cambiado a 'Menú Administrador'

        // Debugging
        if (!$client->getResponse()->isSuccessful()) {
            echo $client->getResponse()->getContent();
        }
    }

    public function testGestionContenidos()
    {
        $client = static::createClient();
        $this->logIn($client);

        $crawler = $client->request('GET', '/harmonyhub/admin/gestionContenido');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form'); // Asegúrate de que los formularios se generan

        // Debugging
        if (!$client->getResponse()->isSuccessful()) {
            echo $client->getResponse()->getContent();
        }
    }
}
