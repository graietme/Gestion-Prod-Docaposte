<?php
/**
 * Auteur : Mehdi Graiet
 * Version : 1.0.0
 * Générateur de SKU unique
 * - Format : PROD-{4 lettres}-{7 caractères aléatoires}
 */

namespace App\Service;

final class SkuGenerator
{
    /** Génère un SKU basé sur le nom produit */
    public function generate(string $productName): string
    {
		// On prend les 4 premiers caractères du nom,  suppression des espaces et caratères spéciaux
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $productName), 0, 4));
		// On génère une chaîne hexadécimale aléatoire de 7 caractères
        $random = substr(bin2hex(random_bytes(4)), 0, 7);
        return sprintf("PROD-%s-%s", $prefix, $random);
    }
}
