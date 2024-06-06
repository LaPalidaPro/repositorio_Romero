<?php

namespace App\Entity;

use App\Repository\FavoritoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FavoritoRepository::class)]
class Favorito
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'favoritos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $usuario = null;

    #[ORM\ManyToOne(inversedBy: 'favoritos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cancion $cancion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?User
    {
        return $this->usuario;
    }

    public function setUsuario(?User $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getCancion(): ?Cancion
    {
        return $this->cancion;
    }

    public function setCancion(?Cancion $cancion): static
    {
        $this->cancion = $cancion;

        return $this;
    }
}
