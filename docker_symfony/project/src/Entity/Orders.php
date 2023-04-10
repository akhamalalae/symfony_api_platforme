<?php

namespace App\Entity;

use App\Repository\OrdersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiProperty;

#[ORM\Entity(repositoryClass: OrdersRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['read:order']],
    denormalizationContext: ['groups' => ['write:order']],
)]
#[Delete]
#[Get(
    security: "is_granted('ROLE_ADMIN') and object.user == user",
    securityMessage: 'Sorry, but you are not the order owner.'
)]
#[Put(
    securityPostDenormalize: "is_granted('ROLE_ADMIN') or (object.user == user and previous_object.user == user)",
    securityPostDenormalizeMessage: 'Sorry, but you are not the actual order owner.'
)]
#[GetCollection(
)]
#[Post(
    validationContext: ['groups' => ['validation:write:order']],
    security: "is_granted('ROLE_ADMIN')",
)]
class Orders
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read:order', 'write:order'])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read:order', 'write:order'])]
    private ?string $libelle = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[Groups(['read:order', 'write:order'])]
    private ?User $user = null;

    #[ORM\ManyToMany(targetEntity: Products::class, inversedBy: 'orders')]
    #[Groups(['read:order', 'write:order'])]
    private Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(?string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Products>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Products $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }

        return $this;
    }

    public function removeProduct(Products $product): self
    {
        $this->products->removeElement($product);

        return $this;
    }

}
