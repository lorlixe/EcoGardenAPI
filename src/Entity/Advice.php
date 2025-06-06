<?php

namespace App\Entity;

use App\Repository\AdviceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

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
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
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
