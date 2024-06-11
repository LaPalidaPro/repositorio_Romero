<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\User;
use App\Entity\Artista;
use App\Entity\Album;


class AdminControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/harmonyhub/admin');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Admin Dashboard'); // Asegúrate de que el contenido esperado está presente en la vista
    }

    public function testGestionContenidos()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/harmonyhub/admin/gestionContenido');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form'); // Asegúrate de que los formularios se generan
    }

}
