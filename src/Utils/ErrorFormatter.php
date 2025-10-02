<?php
/**
 * Auteur : Mehdi Graiet
 * Version : 1.0.0
 * Formateur d’erreurs de validation Symfony
 */

namespace App\Utils;

use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ErrorFormatter
{
    /** Transforme les erreurs en tableau pour JSON */
    public static function format(ConstraintViolationListInterface $errors): array
    {
        $formatted = [];
        foreach ($errors as $error) {
            $formatted[] = [
                'field' => $error->getPropertyPath(),
                'message' => $error->getMessage()
            ];
        }
        return $formatted;
    }
}
