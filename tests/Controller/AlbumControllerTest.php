<?php
// tests/Controller/AlbumControllerTest.php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use App\Entity\User;
use Symfony\Component\BrowserKit\Cookie;

class AlbumControllerTest extends WebTestCase
{
    private function logIn($client, $email)
    {
        $container = $client->getContainer();
        $session = $container->get('session.factory')->createSession();

        // Obtener el usuario de prueba
        $user = $container->get('doctrine')->getRepository(User::class)->findOneBy(['email' => $email]);

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

    public function testGestionAlbums()
    {
        $client = static::createClient();
        $this->logIn($client, 'usu2@gmail.com');

        // Reemplaza '1' con el ID de un artista válido en tu base de datos de prueba
        $crawler = $client->request('GET', '/harmonyhub/admin/gestion/Albums/1');

        $this->assertResponseIsSuccessful();
        // Asegúrate de que el selector existe
        $this->assertSelectorExists('.album-list');
    }


    public function testCrearAlbum()
    {
        $client = static::createClient();
        $this->logIn($client, 'usu2@gmail.com');

        $crawler = $client->request('GET', '/harmonyhub/admin/crearAlbum');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Guardar')->form();
        $form['album[nombre]'] = 'Test Album';
        $form['album[fechaLanzamiento]'] = '2024-01-01';
        $form['album[fotoPortada]'] = 'test-image.png'; // Asegúrate de que este campo tenga un valor válido
        // Completa otros campos obligatorios si los hay

        $client->submit($form);
        $this->assertResponseRedirects('/harmonyhub/admin/gestionContenido');

        $client->followRedirect();
        $this->assertSelectorTextContains('.flash-success', 'Álbum creado con éxito.');
    }

    public function testEditarAlbum()
    {
        $client = static::createClient();
        $this->logIn($client, 'usu2@gmail.com');

        // Reemplaza '1' con el ID de un álbum válido en tu base de datos de prueba
        $crawler = $client->request('GET', '/harmonyhub/admin/editarAlbum/1');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Guardar')->form();
        $form['album[nombre]'] = 'Updated Album Name';
        $form['album[fechaLanzamiento]'] = '2024-01-01';
        $form['album[fotoPortada]'] = 'test-image.png'; // Asegúrate de que este campo tenga un valor válido
        // Completa otros campos obligatorios si los hay

        $client->submit($form);
        $this->assertResponseRedirects('/harmonyhub/admin/gestionContenido');

        $client->followRedirect();
        $this->assertSelectorTextContains('.flash-success', 'Álbum actualizado con éxito.');
    }

    public function testEliminarAlbum()
    {
        $client = static::createClient();
        $this->logIn($client, 'usu2@gmail.com');

        // Reemplaza '1' con el ID de un álbum válido en tu base de datos de prueba
        $crawler = $client->request('POST', '/harmonyhub/admin/eliminarAlbum/1');

        $this->assertResponseRedirects('/harmonyhub/admin/gestion/Albums/1');
        $client->followRedirect();
        $this->assertSelectorTextContains('.flash-success', 'Álbum eliminado correctamente.');
    }


    public function testRemoveCancion()
    {
        $client = static::createClient();
        $this->logIn($client, 'usu2@gmail.com');

        // Reemplaza '1' con el ID de una canción válida en tu base de datos de prueba
        $crawler = $client->request('POST', '/harmonyhub/admin/removeCancion/1');

        $this->assertResponseRedirects('/harmonyhub/admin/editarAlbum/1');
        $client->followRedirect();
        $this->assertSelectorTextContains('.flash-success', 'Canción eliminada correctamente.');
    }
}
