<?php

namespace App\Entity;

use App\Repository\AdviceRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdviceRepository::class)]
class Advice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['advice_user'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['advice_user'])]
    #[Assert\NotBlank(message: "Le titre est obligatoire")]
    #[Assert\Length(min: 1, max: 255, minMessage: "Le titre doit faire au moins {{ limit }} caractères", maxMessage: "Le titre ne peut pas faire plus de {{ limit }} caractères")]
    private ?string $title = null;

    #[Groups(['advice_user'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $date = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    #[Groups(['advice_user'])]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'advice')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['advice_user'])]
    private ?User $users = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    public function setDate(DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getUsers(): ?User
    {
        return $this->users;
    }

    public function setUsers(?User $users): static
    {
        $this->users = $users;

        return $this;
    }
}
