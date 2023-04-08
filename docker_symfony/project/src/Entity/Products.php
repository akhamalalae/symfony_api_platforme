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
use App\Controller\GetWeather;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    normalizationContext: ['groups' => ['read:product']],
    denormalizationContext: ['groups' => ['write:product']],
    operations: [
        new Get(),
        new Put(),
        new Delete(),
        //new Get(name: 'weather', uriTemplate: '/places/{id}/weather', controller: GetWeather::class),
        new GetCollection(),
        new Post(),
    ]
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
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read:product', 'write:product'])]
    private ?string $libelle = null;

    #[ORM\ManyToMany(targetEntity: Categories::class, inversedBy: 'products')]
    #[Groups(['read:product', 'write:product'])]
    private Collection $productCategories;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[Groups(['read:product', 'write:product'])]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Orders::class, cascade: ['persist'])]
    #[Groups(['read:product', 'write:product'])]
    private Collection $orders;

    public function __construct()
    {
        $this->productCategories = new ArrayCollection();
        $this->orders = new ArrayCollection();
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
            $order->setProduct($this);
        }

        return $this;
    }

    public function removeOrder(Orders $order): self
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getProduct() === $this) {
                $order->setProduct(null);
            }
        }

        return $this;
    }
}
