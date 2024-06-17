<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Artista;
use App\Entity\Cancion;
use App\Entity\Album;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        // Crear el primer usuario
        $usuario1 = new User();
        $usuario1->setEmail('usu1@gmail.com');
        $usuario1->setRoles(['ROLE_USER']);
        $hashedPassword1 = $this->passwordHasher->hashPassword(
            $usuario1,
            'Password1'
        );
        $usuario1->setPassword($hashedPassword1);
        $usuario1->setNombre('Nombre1');
        $usuario1->setApellidos('Apellidos1');
        $usuario1->setFechaRegistro(new \DateTime());
        $usuario1->setFoto('defaultImage.jpg'); 
        $usuario1->setSid(bin2hex(random_bytes(16)));

        // Crear el segundo usuario
        $usuario2 = new User();
        $usuario2->setEmail('usu2@gmail.com');
        $usuario2->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $hashedPassword2 = $this->passwordHasher->hashPassword(
            $usuario2,
            'Password2'
        );
        $usuario2->setPassword($hashedPassword2);
        $usuario2->setNombre('Nombre2');
        $usuario2->setApellidos('Apellidos2');
        $usuario2->setFechaRegistro(new \DateTime());
        $usuario2->setFoto('defaultImage.jpg'); // Puedes asignar una foto si tienes una ruta específica
        $usuario2->setSid(bin2hex(random_bytes(16)));

        // Persistir los usuarios
        $manager->persist($usuario1);
        $manager->persist($usuario2);

        // Guardar los cambios
        $manager->flush();
        // Crear algunos artistas
        $artista1 = new Artista();
        $artista1->setNombre('Adelfas')
                 ->setAnoDebut(new \DateTime('2010-01-01'))
                 ->setPaisOrigen('España')
                 ->setBiografia('Biografía de Artista 1')
                 ->setImgArtista('defaultImage.jpg')
                 ->setUser($usuario1);
        $manager->persist($artista1);

        $artista2 = new Artista();
        $artista2->setNombre('Artista2')
                 ->setAnoDebut(new \DateTime('2015-01-01'))
                 ->setPaisOrigen('México')
                 ->setBiografia('Biografía de Artista 2')
                 ->setImgArtista('665b109e77615.jpg')
                 ->setUser($usuario2);
        $manager->persist($artista2);

        // Crear algunos álbumes
        $album1 = new Album();
        $album1->setNombre('Album 1')
               ->setFechaLanzamiento(new \DateTime('2020-01-01'))
               ->setGenerosMusicales(['Rock', 'Indie'])
               ->setNumPistas(10)
               ->setDuracionTotal('45:30')
               ->setFotoPortada('defaultImage.jpg')
               ->setArtista($artista1);
        $manager->persist($album1);

        $album2 = new Album();
        $album2->setNombre('Album 2')
               ->setFechaLanzamiento(new \DateTime('2021-01-01'))
               ->setGenerosMusicales(['Pop', 'Dance'])
               ->setNumPistas(12)
               ->setDuracionTotal('50:45')
               ->setFotoPortada('defaultImage.jpg')
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
