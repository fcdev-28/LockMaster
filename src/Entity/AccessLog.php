<?php

namespace App\Entity;

use App\Repository\AccessLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccessLogRepository::class)]
class AccessLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?AccessPoint $accessPoint = null;

    #[ORM\Column]
    private ?bool $granted = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?AuthorizationRule $grantedBy = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $timestamp = null;

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

    public function getAccessPoint(): ?AccessPoint
    {
        return $this->accessPoint;
    }

    public function setAccessPoint(?AccessPoint $accessPoint): static
    {
        $this->accessPoint = $accessPoint;

        return $this;
    }

    public function isGranted(): ?bool
    {
        return $this->granted;
    }

    public function setGranted(bool $granted): static
    {
        $this->granted = $granted;

        return $this;
    }

    public function getGrantedBy(): ?AuthorizationRule
    {
        return $this->grantedBy;
    }

    public function setGrantedBy(?AuthorizationRule $grantedBy): static
    {
        $this->grantedBy = $grantedBy;

        return $this;
    }

    public function getTimestamp(): ?\DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeImmutable $timestamp): static
    {
        $this->timestamp = $timestamp;

        return $this;
    }
}
