<?php

namespace App\Entity;

use App\Entity\Artista;
use App\Repository\AlbumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AlbumRepository::class)]
class Album
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Artista::class, inversedBy: 'albums')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Artista $artista = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fechaLanzamiento = null;

    #[ORM\Column]
    private ?int $numPistas = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 50, scale: 0)]
    private ?string $duracionTotal = null;

    #[ORM\Column(type: 'json')]
    private array $generosMusicales = [];

    #[ORM\Column(length: 500)]
    private ?string $fotoPortada = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\OneToMany(mappedBy: 'album', targetEntity: Cancion::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $canciones;

    public function __construct()
    {
        $this->canciones = new ArrayCollection();
    }

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

    public function setArtista(?Artista $artista): static
    {
        $this->artista = $artista;

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

    public function getNumPistas(): ?int
    {
        return $this->numPistas;
    }

    public function setNumPistas(int $numPistas): static
    {
        $this->numPistas = $numPistas;

        return $this;
    }

    public function getDuracionTotal(): ?string
    {
        return $this->duracionTotal;
    }

    public function setDuracionTotal(string $duracionTotal): static
    {
        $this->duracionTotal = $duracionTotal;

        return $this;
    }

    public function getGenerosMusicales(): array
    {
        return $this->generosMusicales;
    }

    public function setGenerosMusicales(array $generosMusicales): static
    {
        $this->generosMusicales = $generosMusicales;

        return $this;
    }

    public function getFotoPortada(): ?string
    {
        return $this->fotoPortada;
    }

    public function setFotoPortada(string $fotoPortada): static
    {
        $this->fotoPortada = $fotoPortada;

        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }
    public function getCanciones(): Collection
    {
        return $this->canciones;
    }

    public function calculateDuracionTotal(): void
    {
        $totalDuration = 0;
        foreach ($this->canciones as $cancion) {
            $totalDuration += $cancion->getDuracion();
        }
        $this->duracionTotal = $totalDuration;
    }

    public function addCancion(Cancion $cancion): self
    {
        if (!$this->canciones->contains($cancion)) {
            $this->canciones[] = $cancion;
            $cancion->setAlbum($this);
            $this->calculateDuracionTotal();
        }

        return $this;
    }

    public function removeCancion(Cancion $cancion): self
    {
        if ($this->canciones->removeElement($cancion)) {
            // set the owning side to null (unless already changed)
            if ($cancion->getAlbum() === $this) {
                $cancion->setAlbum(null);
                $this->calculateDuracionTotal();
            }
        }

        return $this;
    }
}
