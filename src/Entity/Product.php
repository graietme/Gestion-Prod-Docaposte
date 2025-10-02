<?php
/**
 * Auteur : Mehdi Graiet
 * Version : 1.0.0
 * Entité Product - représente un produit en base
 * Identifiant : UUIDv6 (ordre temporel, plus efficace que UUIDv4 pour les index DB)
 */

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    private readonly Uuid $id;

    #[ORM\Column(type: "string", length: 255)]
    private string $name;

    #[ORM\Column(type: "string", length: 17, unique: true)]
    private string $sku;

    #[ORM\Column(type: "float")]
    private float $price;

    #[ORM\Column(type: "datetime_immutable")]
    private readonly \DateTimeImmutable $createdAt;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Constructeur
     * - Initialise un UUIDv6 (meilleur pour tri temporel que v4)
     * - Stocke la date de création
     */
    public function __construct(string $name, string $sku, float $price)
    {
        $this->id = Uuid::v6();
        $this->name = $name;
        $this->sku = $sku;
        $this->price = $price;
        $this->createdAt = new \DateTimeImmutable();
    }

    /** Retourne l'identifiant unique du produit */
    public function getId(): Uuid { return $this->id; }

    /** Retourne le nom du produit */
    public function getName(): string { return $this->name; }

    /** Met à jour le nom du produit */
    public function setName(string $name): self { $this->name = $name; return $this; }

    /** Retourne le SKU du produit */
    public function getSku(): string { return $this->sku; }

    /** Met à jour le SKU du produit */
    public function setSku(string $sku): self { $this->sku = $sku; return $this; }

    /** Retourne le prix du produit */
    public function getPrice(): float { return $this->price; }

    /** Met à jour le prix du produit */
    public function setPrice(float $price): self { $this->price = $price; return $this; }

    /** Retourne la date de création */
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    /** Retourne la date de dernière mise à jour */
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    /** Met à jour la date de dernière modification */
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }
}
