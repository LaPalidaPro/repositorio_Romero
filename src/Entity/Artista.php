<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\ArtistaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArtistaRepository::class)]
class Artista
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $anoDebut = null;

    #[ORM\Column(length: 255)]
    private ?string $paisOrigen = null;

    #[ORM\Column(length: 500)]
    private ?string $biografia = null;

    #[ORM\Column(length: 500)]
    private ?string $imgArtista = null;

    #[ORM\OneToMany(mappedBy: 'artista', targetEntity: Album::class)]
    private Collection $albums;
    public function __construct()
    {
        $this->albums = new ArrayCollection();
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

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getAnoDebut(): ?\DateTimeInterface
    {
        return $this->anoDebut;
    }

    public function setAnoDebut(\DateTimeInterface $anoDebut): static
    {
        $this->anoDebut = $anoDebut;

        return $this;
    }

    public function getPaisOrigen(): ?string
    {
        return $this->paisOrigen;
    }

    public function setPaisOrigen(string $paisOrigen): static
    {
        $this->paisOrigen = $paisOrigen;

        return $this;
    }

    public function getBiografia(): ?string
    {
        return $this->biografia;
    }

    public function setBiografia(string $biografia): static
    {
        $this->biografia = $biografia;

        return $this;
    }

    public function getImgArtista(): ?string
    {
        return $this->imgArtista;
    }

    public function setImgArtista(string $imgArtista): static
    {
        $this->imgArtista = $imgArtista;

        return $this;
    }
     /**
     * @return Collection|Album[]
     */
    public function getAlbums(): Collection
    {
        return $this->albums;
    }

    public function addAlbum(Album $album): self
    {
        if (!$this->albums->contains($album)) {
            $this->albums[] = $album;
            $album->setArtista($this);
        }

        return $this;
    }

    public function removeAlbum(Album $album): self
    {
        if ($this->albums->removeElement($album)) {
            // set the owning side to null (unless already changed)
            if ($album->getArtista() === $this) {
                $album->setArtista(null);
            }
        }

        return $this;
    }
}
