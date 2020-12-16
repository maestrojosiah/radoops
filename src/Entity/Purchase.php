<?php

namespace App\Entity;

use App\Repository\PurchaseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PurchaseRepository::class)
 */
class Purchase
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $purchased_on;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="purchases")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $product;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $quantity;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $cost;

    /**
     * @ORM\ManyToOne(targetEntity=Orda::class, inversedBy="purchases")
     * @ORM\JoinColumn(name="orda_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $orda;

    /**
     * @ORM\ManyToOne(targetEntity=Prospect::class, inversedBy="purchases")
     * @ORM\JoinColumn(name="prospect_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $prospect;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPurchasedOn(): ?\DateTimeInterface
    {
        return $this->purchased_on;
    }

    public function setPurchasedOn(\DateTimeInterface $purchased_on): self
    {
        $this->purchased_on = $purchased_on;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    public function setQuantity(string $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getCost(): ?string
    {
        return $this->cost;
    }

    public function setCost(string $cost): self
    {
        $this->cost = $cost;

        return $this;
    }

    public function getOrda(): ?Orda
    {
        return $this->orda;
    }

    public function setOrda(?Orda $orda): self
    {
        $this->orda = $orda;

        return $this;
    }

    public function getProspect(): ?Prospect
    {
        return $this->prospect;
    }

    public function setProspect(?Prospect $prospect): self
    {
        $this->prospect = $prospect;

        return $this;
    }
}
