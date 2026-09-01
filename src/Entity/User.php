<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @method string getUserIdentifier()
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'This email is already registered')]
#[UniqueEntity(fields: ['username'], message: 'This username is already taken')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'First name is required')]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Last name is required')]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Email is required')]
    #[Assert\Email(message: 'Please enter a valid email address')]
    private ?string $email = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotBlank(message: 'Birth date is required')]
    #[Assert\LessThan('today', message: 'Birth date must be in the past')]
    private ?\DateTimeImmutable $birthDate = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Username is required')]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Password is required')]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/',
        message: 'Password must be at least 8 characters and include an uppercase letter, a lowercase letter, a number, and a special character'
    )]
    private ?string $password = null;

    #[ORM\Column(length: 15)]
    #[Assert\Regex(
        pattern: '/^\d{3} \d{2} \d{2} \d{2}$/',
        message: 'Phone number must be in the format XXX XX XX XX'
    )]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private $photo;

    #[ORM\Column]
    private ?bool $administrator = null;

    #[ORM\ManyToMany(targetEntity: UserGroup::class, mappedBy: 'users')]
    private Collection $userGroups;

    public function __construct()
    {
        $this->userGroups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
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

    public function getBirthDate(): ?\DateTimeImmutable
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeImmutable $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getPhoto(): ?string
    {
        if (is_resource($this->photo)) {
            // Convertimos el recurso en una cadena (datos binarios)
            return stream_get_contents($this->photo);
        }

        return $this->photo;
    }

    public function getPhotoAsBase64(): ?string
    {
        $data = $this->getPhoto(); // Cogemos los datos binarios y los pasamos a base64

        if (!$data) {
            return null;
        }

        // Devolvemos el src completo de la imagen
        return 'data:image/jpeg;base64,' . base64_encode($data);
    }

    public function setPhoto($photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function isAdministrator(): ?bool
    {
        return $this->administrator;
    }

    public function setAdministrator(bool $administrator): static
    {
        $this->administrator = $administrator;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = [];
        $roles[] = 'ROLE_USER';

        if ($this->isAdministrator()) {
            $roles[] = 'ROLE_ADMIN';
        }

        return array_unique($roles);
    }

    public function getSalt(): null
    {
        return null;
    }

    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function __toString(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @return Collection<int, UserGroup>
     */
    public function getUserGroups(): Collection
    {
        return $this->userGroups;
    }

    public function addUserGroup(UserGroup $userGroup): static
    {
        if (!$this->userGroups->contains($userGroup)) {
            $this->userGroups->add($userGroup);
        }

        return $this;
    }

    public function removeUserGroup(UserGroup $userGroup): static
    {
        $this->userGroups->removeElement($userGroup);

        return $this;
    }
}