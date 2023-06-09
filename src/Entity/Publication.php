<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\Repository\PublicationRepository;
use App\State\PublicationUserSetter;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PublicationRepository::class)]
#[ApiResource(
    uriTemplate: '/utilisateur/{idUtilisateur}/publications',
    operations: [new GetCollection()],
    uriVariables: [
        'idUtilisateur' => new Link(
            fromProperty: 'publications',
            fromClass: Utilisateur::class
        )
    ],
    normalizationContext: ["groups" => ["publication:read", "utilisateur:read"]],
    order: ["datePublication" => "DESC"]
)]
#[ApiResource(operations: [new GetCollection(), new Get(),
    new Delete(security: "is_granted('ROLE_USER') and object.auteur = user"),
    new Post(security: "is_granted('ROLE_USER')", processor: PublicationUserSetter::class)],
    normalizationContext: ["groups" => ["publication:read", "utilisateur:read"]],
    order: ["datePublication" => "DESC"]
)]
class Publication
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['publication:read'])]
    private ?int $id = null;

    #[Groups(['publication:read'])]
    #[Assert\Length(min: 4, max: 200)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[ORM\Column(length: 200)]
    private ?string $message;

    #[Groups(['publication:read'])]
    #[ApiProperty(writable: false)]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $datePublication;

    #[Groups(['publication:read'])]
    #[ApiProperty(writable: false)]
    #[ORM\ManyToOne(inversedBy: 'publications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $auteur = null;

    public function __construct()
    {
        $this->datePublication = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getDatePublication(): ?\DateTimeInterface
    {
        return $this->datePublication;
    }

    public function setDatePublication(\DateTimeInterface $datePublication): self
    {
        $this->datePublication = $datePublication;

        return $this;
    }

    public function getAuteur(): ?Utilisateur
    {
        return $this->auteur;
    }

    public function setAuteur(?Utilisateur $auteur): self
    {
        $this->auteur = $auteur;

        return $this;
    }
}
