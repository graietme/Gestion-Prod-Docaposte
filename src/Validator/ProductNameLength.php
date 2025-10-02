<?php
/**
 * Auteur : Mehdi Graiet
 * Version : 1.0.0
 * Contrainte personnalisée pour valider dynamiquement la longueur du nom produit
 */

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class ProductNameLength extends Constraint
{
    public string $messageMin = 'Le nom doit contenir au moins {{ limit }} caractères.';
    public string $messageMax = 'Le nom ne doit pas dépasser {{ limit }} caractères.';
}
