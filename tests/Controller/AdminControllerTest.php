<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\User;
use App\Entity\Artista;
use App\Entity\Album;


class AdminControllerTest extends WebTestCase
{
    public function testCrearArtista()
    {
        $client = static::createClient();

        // Autenticar el usuario de prueba
        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneByEmail('usu2@gmail.com'); // Usar uno de los usuarios creados en las fixtures
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/admin/crearArtista');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        $form = $crawler->selectButton('Guardar')->form();
        $form['artista[nombre]'] = 'Nuevo Artista';
        $form['artista[anoDebut]'] = '2020-01-01'; // Proporcionar una fecha válida
        $form['artista[paisOrigen]'] = 'España';
        $form['artista[biografia]'] = 'Biografía de prueba';

        // Ruta correcta para el archivo de prueba
        $filePath = __DIR__ . '/../../public/images/test-image.jpg';
        $file = new UploadedFile(
            $filePath,
            'test-image.jpg',
            'image/jpeg',
            null,
            true
        );
        $form['artista[imgArtista]'] = $file;

        $client->submit($form);

        $this->assertResponseRedirects('/harmonyhub/admin/gestionContenido');
        $crawler = $client->followRedirect();

        // Imprimir el contenido de la página redirigida para depuración
        file_put_contents('debug_output.html', $client->getResponse()->getContent());

        // Ajustar el mensaje de éxito según tu implementación
        $this->assertSelectorTextContains('.alert-success', 'Artista creado con éxito.');
    }

    public function testEditarArtista()
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneByEmail('usu2@gmail.com');
        $client->loginUser($testUser);

        $artistaRepository = static::getContainer()->get('doctrine')->getRepository(Artista::class);
        $artista = $artistaRepository->findOneBy(['nombre' => 'Nuevo Artista']);

        $crawler = $client->request('GET', '/harmonyhub/admin/editarArtista/' . $artista->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        $form = $crawler->selectButton('Guardar')->form();
        $form['artista[nombre]'] = 'Artista Editado';

        $client->submit($form);

        $this->assertResponseRedirects('/harmonyhub/admin/gestionContenido');
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('.alert-success', 'Artista actualizado correctamente.');
    }
    public function testBorrarArtista()
    {
        $client = static::createClient();
    
        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneByEmail('usu2@gmail.com');
        $client->loginUser($testUser);
    
        $artistaRepository = static::getContainer()->get('doctrine')->getRepository(Artista::class);
        $artista = $artistaRepository->findOneBy(['nombre' => 'Artista Editado']);
    
        // Realizar una solicitud GET para asegurarse de que la sesión está iniciada
        $crawler = $client->request('GET', '/harmonyhub/admin/gestionContenido');
        $this->assertResponseIsSuccessful();
    
        // Obtener el token CSRF después de que la sesión esté iniciada
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('delete' . $artista->getId())->getValue();
    
        $client->request('POST', '/harmonyhub/admin/gestion/borrarArtista' . $artista->getId(), [
            '_token' => $csrfToken,
        ]);
    
        $this->assertResponseRedirects('/harmonyhub/admin/gestionContenido');
        $client->followRedirect();
    
        $this->assertSelectorTextContains('.alert-success', 'Artista eliminado correctamente.');
    }
    
    public function testEliminarAlbum()
    {
        $client = static::createClient();
    
        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneByEmail('usu2@gmail.com');
        $client->loginUser($testUser);
    
        $albumRepository = static::getContainer()->get('doctrine')->getRepository(\App\Entity\Album::class);
        $album = $albumRepository->findOneBy(['nombre' => 'Nuevo Album']);
    
        // Realizar una solicitud GET para asegurarse de que la sesión está iniciada
        $crawler = $client->request('GET', '/harmonyhub/admin/gestionContenido');
        $this->assertResponseIsSuccessful();
    
        // Obtener el token CSRF después de que la sesión esté iniciada
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('delete' . $album->getId())->getValue();
    
        $client->request('POST', '/harmonyhub/admin/eliminarAlbum/' . $album->getId(), [
            '_token' => $csrfToken,
        ]);
    
        $this->assertResponseRedirects('/harmonyhub/admin/gestionAlbums' . $album->getArtista()->getId());
        $client->followRedirect();
    
        $this->assertSelectorTextContains('.alert-success', 'Álbum eliminado correctamente.');
    }
    

    
  
}
