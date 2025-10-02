<?php
/**
 * Auteur : Mehdi Graiet
 * Version : 1.0.0
 * Validator qui lit les paramètres de app.yaml et applique la validation
 */

namespace App\Validator;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ProductNameLengthValidator extends ConstraintValidator
{
    public function __construct(private readonly ParameterBagInterface $params) {}

    /** Vérifie si la longueur du nom respecte les bornes définies dans app.yaml */
    public function validate(mixed $value, Constraint $constraint)
    {
        if (!$constraint instanceof ProductNameLength || $value === null) {
            return;
        }

        $min = $this->params->get('app.product.name_min_length');
        $max = $this->params->get('app.product.name_max_length');

        $length = mb_strlen($value);

        if ($length < $min) {
            $this->context->buildViolation($constraint->messageMin)
                ->setParameter('{{ limit }}', $min)
                ->addViolation();
        }

        if ($length > $max) {
            $this->context->buildViolation($constraint->messageMax)
                ->setParameter('{{ limit }}', $max)
                ->addViolation();
        }
    }
}
