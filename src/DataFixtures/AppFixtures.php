<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Artista;
use App\Entity\Cancion;
use App\Entity\Album;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Crear algunos artistas
        $artista1 = new Artista();
        $artista1->setNombre('Adelfas')
                 ->setAnoDebut(new \DateTime('2010-01-01'))
                 ->setPaisOrigen('España')
                 ->setBiografia('Biografía de Artista 1')
                 ->setImgArtista('img/artista1.jpg');
        $manager->persist($artista1);

        $artista2 = new Artista();
        $artista2->setNombre('Artista2')
                 ->setAnoDebut(new \DateTime('2015-01-01'))
                 ->setPaisOrigen('México')
                 ->setBiografia('Biografía de Artista 2')
                 ->setImgArtista('img/artista2.jpg');
        $manager->persist($artista2);

        // Crear algunos álbumes
        $album1 = new Album();
        $album1->setNombre('Album 1')
               ->setFechaLanzamiento(new \DateTime('2020-01-01'))
               ->setGenerosMusicales(['Rock', 'Indie'])
               ->setNumPistas(10)
               ->setDuracionTotal('45:30')
               ->setFotoPortada('img/album1.jpg')
               ->setArtista($artista1);
        $manager->persist($album1);

        $album2 = new Album();
        $album2->setNombre('Album 2')
               ->setFechaLanzamiento(new \DateTime('2021-01-01'))
               ->setGenerosMusicales(['Pop', 'Dance'])
               ->setNumPistas(12)
               ->setDuracionTotal('50:45')
               ->setFotoPortada('img/album2.jpg')
               ->setArtista($artista2);
        $manager->persist($album2);

        // Crear algunas canciones
        $cancion1 = new Cancion();
        $cancion1->setTitulo('GATOS_PARDOS.wav')
                 ->setDuracion('2:45')
                 ->setGeneroMusical('Rock')
                 ->setFechaLanzamiento(new \DateTime('2023-05-01'))
                 ->setNumeroReproducciones(100)
                 ->setArtista($artista1)
                 ->setAlbum($album1);
        $manager->persist($cancion1);

        $cancion2 = new Cancion();
        $cancion2->setTitulo('GRÍTALO.wav')
                 ->setDuracion('2:05')
                 ->setGeneroMusical('Pop')
                 ->setFechaLanzamiento(new \DateTime('2023-05-02'))
                 ->setNumeroReproducciones(150)
                 ->setArtista($artista2)
                 ->setAlbum($album2);
        $manager->persist($cancion2);

        $manager->flush();
    }
}
