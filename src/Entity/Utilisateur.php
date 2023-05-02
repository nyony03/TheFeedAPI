<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\UtilisateurRepository;
use App\State\UserPasswordHasher;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Patch(denormalizationContext: ["groups" => ["utilisateur:update"]],
            security: "is_granted('ROLE_USER') and object == user",
            validationContext: ["utilisateur:update", "Default"],
            processor: UserPasswordHasher::class),
        new Post(denormalizationContext: ["groups" => ["utilisateur:create"]],
            validationContext: ["utilisateur:create", "Default"],
            processor: UserPasswordHasher::class),
        new Delete(security: "is_granted('ROLE_USER') and user === object")],
    normalizationContext: ["groups" => ["utilisateur:read"]],
)]
#[UniqueEntity('login','email')]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['utilisateur:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    #[Assert\Length(
        min: 3,
        max: 20
    )]

    #[Assert\NotBlank(groups: ['utilisateur:create'])]
    #[Assert\NotNull(groups: ['utilisateur:create'])]
    #[Groups(['utilisateur:read', 'utilisateur:create'])]
    private ?string $login = null;

    #[ORM\Column(length: 255)]
    #[Assert\Email]
    #[Assert\NotBlank(groups: ['utilisateur:create'])]
    #[Assert\NotNull(groups: ['utilisateur:create'])]
    #[Groups(['utilisateur:read', 'utilisateur:create', 'utilisateur:update'])]
    private ?string $email = null;

    #[ORM\OneToMany(mappedBy: 'auteur', targetEntity: Publication::class, orphanRemoval: true)]
    private Collection $publications;

    #[ORM\Column(length: 255)]
    #[ApiProperty(readable: false, writable: false)]
    private ?string $password = null;

    #[Assert\NotBlank(groups: ['utilisateur:create'])]
    #[Assert\NotNull(groups: ['utilisateur:create'])]
    #[Assert\Length(
        min: 8,
        max: 30
    )]
    #[Assert\Regex('#^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,30}$#')]
    #[Groups(['utilisateur:create', 'utilisateur:update'])]
    private ?string $plainPassword = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    public function __construct()
    {
        $this->publications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, Publication>
     */
    public function getPublications(): Collection
    {
        return $this->publications;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }


    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function getUserIdentifier(): string
    {
        return $this->login;
    }
}
