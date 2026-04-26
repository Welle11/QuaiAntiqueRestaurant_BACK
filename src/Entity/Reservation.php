<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['reservation:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['reservation:read'])]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    #[Groups(['reservation:read'])]
    private ?string $time = null;

    #[ORM\Column]
    #[Groups(['reservation:read'])]
    private ?int $nbGuests = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['reservation:read'])]
    private ?string $allergies = null;

    #[ORM\Column]
    #[Groups(['reservation:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    // Pas de Groups — évite la référence circulaire avec User
    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int { return $this->id; }

    public function getDate(): ?\DateTimeInterface { return $this->date; }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getTime(): ?string { return $this->time; }

    public function setTime(string $time): static
    {
        $this->time = $time;
        return $this;
    }

    public function getNbGuests(): ?int { return $this->nbGuests; }

    public function setNbGuests(int $nbGuests): static
    {
        $this->nbGuests = $nbGuests;
        return $this;
    }

    public function getAllergies(): ?string { return $this->allergies; }

    public function setAllergies(?string $allergies): static
    {
        $this->allergies = $allergies;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUser(): ?User { return $this->user; }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }
}
