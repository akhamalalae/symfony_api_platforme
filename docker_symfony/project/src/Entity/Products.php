<?php

namespace App\Entity;

use App\Repository\ProductsRepository;
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

#[ApiResource(
    normalizationContext: ['groups' => ['read:product']],
    denormalizationContext: ['groups' => ['write:product']],
)]
#[Delete]
#[Get(
    security: "is_granted('ROLE_USER') and object.user == user",
    securityMessage: 'Sorry, but you are not the product owner.'
)]
#[Put(
    securityPostDenormalize: "is_granted('ROLE_USER') or (object.user == user and previous_object.user == user)",
    securityPostDenormalizeMessage: 'Sorry, but you are not the actual product owner.'
)]
#[GetCollection(
)]
#[Post(
    validationContext: ['groups' => ['validation:write:product']],
    security: "is_granted('ROLE_USER')",
)]
#[ORM\Entity(repositoryClass: ProductsRepository::class)]
class Products
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read:product', 'write:product'])]
    #[Assert\NotNull(groups: ['validation:write:product'])]
    #[Assert\NotBlank(groups: ['validation:write:product'])]
    #[Assert\Length(min: 2, max: 50, groups: ['validation:write:product'])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read:product', 'write:product'])]
    #[ApiProperty(
        openapiContext: [
            'type' => 'string',
            'example' => 'product libelle'
        ]
    )]
    private ?string $libelle = null;

    #[ORM\ManyToMany(targetEntity: Categories::class, inversedBy: 'products')]
    #[Groups(['read:product', 'write:product'])]
    private Collection $productCategories;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[Groups(['read:product', 'write:product'])]
    public ?User $user = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['read:product', 'write:product'])]
    private ?int $price = null;

    #[ORM\ManyToMany(targetEntity: Orders::class, mappedBy: 'products')]
    private Collection $orders;

    #[ORM\ManyToOne(inversedBy: 'products', cascade:['persist'])]
    #[Groups(['read:product', 'write:product'])]
    private ?ProductType $type = null;

    public function __construct()
    {
        $this->productCategories = new ArrayCollection();
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    #[Groups(['read:product', 'write:product'])]
    #[ApiProperty(
        openapiContext: [
            'type' => 'int',
            'example' => 'TVA'
        ]
    )]
    public function getPriceTVA(): ?int
    {
        return $this->price * 20 / 100;
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

    /**
     * @return Collection<int, Categories>
     */
    public function getProductCategories(): Collection
    {
        return $this->productCategories;
    }

    public function addProductCategory(Categories $productCategory): self
    {
        if (!$this->productCategories->contains($productCategory)) {
            $this->productCategories->add($productCategory);
        }

        return $this;
    }

    public function removeProductCategory(Categories $productCategory): self
    {
        $this->productCategories->removeElement($productCategory);

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

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection<int, Orders>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Orders $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->addProduct($this);
        }

        return $this;
    }

    public function removeOrder(Orders $order): self
    {
        if ($this->orders->removeElement($order)) {
            $order->removeProduct($this);
        }

        return $this;
    }

    public function getType(): ?ProductType
    {
        return $this->type;
    }

    public function setType(?ProductType $type): self
    {
        $this->type = $type;

        return $this;
    }
}
