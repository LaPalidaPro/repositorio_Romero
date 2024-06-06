<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'El campo email es obligatorio.')]
    #[Assert\Email(message: 'El email {{ value }} no es un email válido.')]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: 'El campo contraseña es obligatorio.')]
    #[Assert\Length(
        min: 8,
        minMessage: 'La contraseña debe tener al menos {{ limit }} caracteres.',
    )]
    #[Assert\Regex(
        pattern: '/^(?=.*[A-Z])(?=.*\W).+$/',
        message: 'La contraseña debe contener al menos una letra mayúscula y un signo especial.',
    )]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'El campo nombre es obligatorio.')]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'El campo apellidos es obligatorio.')]
    private ?string $apellidos = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fechaRegistro = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $foto = null;

    #[ORM\Column(length: 500)]
    private ?string $sid = null;

    /**
     * @var Collection<int, Artista>
     */
    #[ORM\OneToMany(targetEntity: Artista::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $artistas;

    /**
     * @var Collection<int, Favorito>
     */
    #[ORM\OneToMany(targetEntity: Favorito::class, mappedBy: 'usuario')]
    private Collection $favoritos;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }
    public function __construct()
    {
        $this->fechaRegistro = new \DateTime(); // Asignar la fecha actual al crear la entidad
        $this->sid = bin2hex(random_bytes(16)); // Generar un valor por defecto para 'sid'
        $this->roles = ['ROLE_USER']; // Asignar el rol por defecto
        $this->artistas = new ArrayCollection();
        $this->favoritos = new ArrayCollection();
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // Garantizar que todos los usuarios tengan al menos el rol ROLE_USER
        if (!in_array('ROLE_USER', $roles, true)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getApellidos(): ?string
    {
        return $this->apellidos;
    }

    public function setApellidos(string $apellidos): static
    {
        $this->apellidos = $apellidos;

        return $this;
    }

    public function getFechaRegistro(): ?\DateTimeInterface
    {
        return $this->fechaRegistro;
    }

    public function setFechaRegistro(\DateTimeInterface $fechaRegistro): static
    {
        $this->fechaRegistro = $fechaRegistro;

        return $this;
    }

    public function getFoto(): ?string
    {
        return $this->foto;
    }

    public function setFoto(string $foto): static
    {
        $this->foto = $foto;

        return $this;
    }

    public function getSid(): ?string
    {
        return $this->sid;
    }

    public function setSid(string $sid): static
    {
        $this->sid = $sid;

        return $this;
    }

    /**
     * @return Collection<int, Artista>
     */
    public function getArtistas(): Collection
    {
        return $this->artistas;
    }

    public function addArtista(Artista $artista): static
    {
        if (!$this->artistas->contains($artista)) {
            $this->artistas->add($artista);
            $artista->setUser($this);
        }

        return $this;
    }

    public function removeArtista(Artista $artista): static
    {
        if ($this->artistas->removeElement($artista)) {
            // set the owning side to null (unless already changed)
            if ($artista->getUser() === $this) {
                $artista->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Favorito>
     */
    public function getFavoritos(): Collection
    {
        return $this->favoritos;
    }

    public function addFavorito(Favorito $favorito): static
    {
        if (!$this->favoritos->contains($favorito)) {
            $this->favoritos->add($favorito);
            $favorito->setUsuario($this);
        }

        return $this;
    }

    public function removeFavorito(Favorito $favorito): static
    {
        if ($this->favoritos->removeElement($favorito)) {
            // set the owning side to null (unless already changed)
            if ($favorito->getUsuario() === $this) {
                $favorito->setUsuario(null);
            }
        }

        return $this;
    }

}
