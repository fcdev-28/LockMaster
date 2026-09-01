<?php

namespace App\Entity;

use App\Repository\AuthorizationRuleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuthorizationRuleRepository::class)]
class AuthorizationRule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne]
    private ?UserGroup $userGroup = null;

    #[ORM\ManyToOne]
    private ?AccessPoint $accessPoint = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startTimestamp = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endTimestamp = null;

    #[ORM\Column]
    private ?bool $mon = null;

    #[ORM\Column]
    private ?bool $tue = null;

    #[ORM\Column]
    private ?bool $wed = null;

    #[ORM\Column]
    private ?bool $thu = null;

    #[ORM\Column]
    private ?bool $fri = null;

    #[ORM\Column]
    private ?bool $sat = null;

    #[ORM\Column]
    private ?bool $sun = null;

    #[ORM\Column]
    private ?bool $temp = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getUserGroup(): ?UserGroup
    {
        return $this->userGroup;
    }

    public function setUserGroup(?UserGroup $userGroup): static
    {
        $this->userGroup = $userGroup;

        return $this;
    }

    public function getAccessPoint(): ?AccessPoint
    {
        return $this->accessPoint;
    }

    public function setAccessPoint(?AccessPoint $accessPoint): static
    {
        $this->accessPoint = $accessPoint;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getStartTimestamp(): ?\DateTimeImmutable
    {
        return $this->startTimestamp;
    }

    public function setStartTimestamp(?\DateTimeImmutable $startTimestamp): static
    {
        $this->startTimestamp = $startTimestamp;

        return $this;
    }

    public function getEndTimestamp(): ?\DateTimeImmutable
    {
        return $this->endTimestamp;
    }

    public function setEndTimestamp(?\DateTimeImmutable $endTimestamp): static
    {
        $this->endTimestamp = $endTimestamp;

        return $this;
    }

    public function isMon(): ?bool
    {
        return $this->mon;
    }

    public function setMon(bool $mon): static
    {
        $this->mon = $mon;

        return $this;
    }

    public function isTue(): ?bool
    {
        return $this->tue;
    }

    public function setTue(bool $tue): static
    {
        $this->tue = $tue;

        return $this;
    }

    public function isWed(): ?bool
    {
        return $this->wed;
    }

    public function setWed(bool $wed): static
    {
        $this->wed = $wed;

        return $this;
    }

    public function isThu(): ?bool
    {
        return $this->thu;
    }

    public function setThu(bool $thu): static
    {
        $this->thu = $thu;

        return $this;
    }

    public function isFri(): ?bool
    {
        return $this->fri;
    }

    public function setFri(bool $fri): static
    {
        $this->fri = $fri;

        return $this;
    }

    public function isSat(): ?bool
    {
        return $this->sat;
    }

    public function setSat(bool $sat): static
    {
        $this->sat = $sat;

        return $this;
    }

    public function isSun(): ?bool
    {
        return $this->sun;
    }

    public function setSun(bool $sun): static
    {
        $this->sun = $sun;

        return $this;
    }

    public function isTemp(): ?bool
    {
        return $this->temp;
    }

    public function setTemp(bool $temp): static
    {
        $this->temp = $temp;

        return $this;
    }
}
