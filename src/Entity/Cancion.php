<?php

namespace App\Entity;

use App\Repository\CancionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CancionRepository::class)]
class Cancion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Artista::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Artista $artista = null;

    #[ORM\ManyToOne(targetEntity: Album::class, inversedBy: 'canciones')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Album $album = null;

    #[ORM\Column(length: 255)]
    private ?string $titulo = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0)]
    private ?string $duracion = null;

    #[ORM\Column(length: 255)]
    private ?string $generoMusical = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fechaLanzamiento = null;

    #[ORM\Column]
    private ?int $numeroReproducciones = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getArtista(): ?Artista
    {
        return $this->artista;
    }

    public function setArtista(Artista $artista): static
    {
        $this->artista = $artista;

        return $this;
    }

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $album): self
    {
        $this->album = $album;
        return $this;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): static
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getDuracion(): ?string
    {
        return $this->duracion;
    }

    public function setDuracion(string $duracion): static
    {
        $this->duracion = $duracion;

        return $this;
    }

    public function getGeneroMusical(): ?string
    {
        return $this->generoMusical;
    }

    public function setGeneroMusical(string $generoMusical): static
    {
        $this->generoMusical = $generoMusical;

        return $this;
    }

    public function getFechaLanzamiento(): ?\DateTimeInterface
    {
        return $this->fechaLanzamiento;
    }

    public function setFechaLanzamiento(\DateTimeInterface $fechaLanzamiento): static
    {
        $this->fechaLanzamiento = $fechaLanzamiento;

        return $this;
    }

    public function getNumeroReproducciones(): ?int
    {
        return $this->numeroReproducciones;
    }

    public function setNumeroReproducciones(int $numeroReproducciones): static
    {
        $this->numeroReproducciones = $numeroReproducciones;

        return $this;
    }
}
