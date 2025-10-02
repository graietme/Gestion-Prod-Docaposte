<?php
/**
 * Auteur : Mehdi Graiet
 * Version : 1.0.0
 * DTO pour création produit
 * - Découplé de Doctrine
 * - Validation via Symfony Validator
 */

namespace App\DTO;

use App\Validator\ProductNameLength;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateProductDTO
{
    /**
     * Constructeur : représente les données d'entrée pour la création
     */
    public function __construct(
        #[Assert\NotBlank(message: "Le nom est obligatoire")]
        #[ProductNameLength]
        public readonly string $name,

        #[Assert\NotBlank(message: "Le prix est obligatoire")]
        #[Assert\GreaterThan(value: 0, message: "Le prix doit être strictement supérieur à 0")]
        public readonly float $price,
    ) {}
}
