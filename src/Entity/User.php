<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
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
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column]
    private \DateTimeImmutable $registeredAt;

    #[ORM\Column(length: 12)]
    private string $verificationCode;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $verifiedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastLogInDate = null;

    /**
     * @var Collection<int, ShoppingList>
     */
    #[ORM\OneToMany(targetEntity: ShoppingList::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $shoppingLists;

    public function __construct()
    {
        $this->registeredAt = new \DateTimeImmutable();
        $this->verificationCode = bin2hex(random_bytes(6));
        $this->shoppingLists = new ArrayCollection();
    }

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
     * This code is generated, so no need to make static analysis
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        // @phpstan-ignore return.type
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
    public function getPassword(): ?string
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

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getRegisteredAt(): \DateTimeImmutable
    {
        return $this->registeredAt;
    }

    public function setRegisteredAt(\DateTimeImmutable $registeredAt): static
    {
        $this->registeredAt = $registeredAt;

        return $this;
    }

    public function getVerificationCode(): ?string
    {
        return $this->verificationCode;
    }

    public function setVerificationCode(string $verificationCode): static
    {
        $this->verificationCode = $verificationCode;

        return $this;
    }

    public function getVerifiedAt(): ?\DateTimeImmutable
    {
        return $this->verifiedAt;
    }

    private function setVerified(): static
    {
        $this->verifiedAt = new \DateTimeImmutable();

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->verifiedAt !== null;
    }

    public function verify(string $verificationCode): static
    {
        if ($verificationCode != $this->verificationCode) {
            throw new BadRequestHttpException("Given verification code is incorrect");
        }

        return $this->setVerified();
    }

    public function getLastLogInDate(): ?\DateTimeImmutable
    {
        return $this->lastLogInDate;
    }

    private function setLastLogInDate(): static
    {
        $this->lastLogInDate = new \DateTimeImmutable();

        return $this;
    }

    public function setLoggedIn(): static
    {
        return $this->setLastLogInDate();
    }

    /**
     * @return Collection<int, ShoppingList>
     */
    public function getShoppingLists(): Collection
    {
        return $this->shoppingLists;
    }

    public function addShoppingList(ShoppingList $shoppingList): static
    {
        if (!$this->shoppingLists->contains($shoppingList)) {
            $this->shoppingLists->add($shoppingList);
            $shoppingList->setOwner($this);
        }

        return $this;
    }

    public function removeShoppingList(ShoppingList $shoppingList): static
    {
        if ($this->shoppingLists->removeElement($shoppingList)) {
            // set the owning side to null (unless already changed)
            if ($shoppingList->getOwner() === $this) {
                $shoppingList->setOwner(null);
            }
        }

        return $this;
    }
}
