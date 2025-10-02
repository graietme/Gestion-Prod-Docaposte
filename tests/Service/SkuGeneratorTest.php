<?php
/**
 * Auteur : Mehdi Graiet
 * Version : 1.0.0
 * Description : Tests unitaires du service SkuGenerator
 * 
 * Objectif : vérifier que le générateur de SKU respecte bien le format,
 * la longueur maximale et l’unicité.
 */

namespace App\Tests\Service;

use App\Service\SkuGenerator;
use PHPUnit\Framework\TestCase;

final class SkuGeneratorTest extends TestCase
{
    private SkuGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new SkuGenerator();
    }

    /**
     * Vérifie que le SKU généré respecte le format attendu :
     * PROD-XXXX-XXXXXXX
     */
    public function testGenerateSkuFormat(): void
    {
        $sku = $this->generator->generate("Macbook Pro");
        $this->assertMatchesRegularExpression('/^PROD-[A-Z0-9]{1,4}-[a-f0-9]{7}$/', $sku);
    }

    /**
     * Vérifie que deux produits avec le même nom génèrent
     * des SKU différents (unicité garantie par randomisation).
     */
    public function testGenerateSkuIsUnique(): void
    {
        $sku1 = $this->generator->generate("Produit Test");
        $sku2 = $this->generator->generate("Produit Test");

        $this->assertNotEquals($sku1, $sku2);
    }

    /**
     * Vérifie que le SKU généré ne dépasse jamais 17 caractères,
     * conformément à la contrainte en base de données.
     */
    public function testSkuDoesNotExceedMaxLength(): void
    {
        $sku = $this->generator->generate("Produit Très Long Avec Beaucoup De Caractères");
        $this->assertLessThanOrEqual(17, strlen($sku), "Le SKU ne doit pas dépasser 17 caractères");
    }

    /**
     * Vérifie que le préfixe du SKU correspond bien
     * aux 4 premières lettres du nom du produit.
     */
    public function testSkuPrefixIsFirstFourLettersOfName(): void
    {
        $sku = $this->generator->generate("TestProduit");
        $this->assertStringStartsWith("PROD-TEST-", $sku);
    }
}
