<?php
/**
 * Auteur : Mehdi Graiet
 * Version : 1.0.0
 * Description : Tests fonctionnels de l’API Produits
 * 
 * Objectif : vérifier les endpoints REST (POST, GET, PUT)
 * et s’assurer que les règles métier sont respectées.
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Transport\InMemoryTransport;

final class ProductControllerTest extends WebTestCase
{
    private InMemoryTransport $transport;

    protected function setUp(): void
    {
        self::bootKernel();

        // Récupère le transport InMemory pour simuler l’envoi de mails
        $container = static::getContainer();
        $mailer = $container->get(MailerInterface::class);

        $transportProp = new \ReflectionProperty($mailer, 'transport');
        $transportProp->setAccessible(true);
        $this->transport = $transportProp->getValue($mailer);

        if (!$this->transport instanceof InMemoryTransport) {
            $this->markTestSkipped('Mailer must use InMemoryTransport for this test');
        }
    }

    /**
     * Cas nominal : création d’un produit valide
     * - Vérifie que la réponse est 201
     * - Vérifie que le nom du produit est bien celui envoyé
     */
    public function testCreateProductValid(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/products', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'ONE PIECE Statue BIJUtsu Zoro The Monster',
            'price' => 799.90
        ]));

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('ONE PIECE Statue BIJUtsu Zoro The Monster', $data['data']['name']);
    }

    /**
     * Cas d’erreur : création d’un produit avec nom trop court (< 5 caractères)
     * - Vérifie que la réponse est 400
     * - Vérifie que l’erreur concerne bien le champ "name"
     */
    public function testCreateProductInvalidNameTooShort(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/products', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'One ',
            'price' => 10
        ]));

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('name', $data['errors'][0]['field']);
    }

    /**
     * Cas d’erreur : création d’un produit avec prix = 0
     * - Vérifie que la validation échoue
     * - Vérifie que l’erreur concerne bien le champ "price"
     */
    public function testCreateProductInvalidPriceZero(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/products', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'SAINT SEIYA Final Edition Tome 4',
            'price' => 0
        ]));

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('price', $data['errors'][0]['field']);
    }

    /**
     * Cas d’erreur : lecture d’un produit inexistant
     * - Vérifie que l’API retourne bien une 404 Not Found
     */
    public function testGetProductNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/products/00000000-0000-0000-0000-000000000000');
        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * Cas nominal : mise à jour d’un produit existant
     * - Crée un produit
     * - Met à jour son nom et son prix
     * - Vérifie que la réponse est 200 et que les données sont bien modifiées
     */
    public function testUpdateProductValid(): void
    {
        $client = static::createClient();

        // Crée un produit
        $client->request('POST', '/api/products', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'SAINT SEIYA Final Edition Tome 4',
            'price' => 9.99
        ]));

        $data = json_decode($client->getResponse()->getContent(), true);
        $id = $data['data']['id'];

        // Met à jour le produit
        $client->request('PUT', "/api/products/$id", [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'SAINT SEIYA Final Edition Tome 4 premier tirage Kana',
            'price' => 10.25
        ]));

        $this->assertResponseStatusCodeSame(200);
        $updated = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('SAINT SEIYA Final Edition Tome 4 premier tirage Kana', $updated['data']['name']);
    }

    /**
     * Cas spécial : simulation d’un fichier log trop gros
     * - Crée un fichier artificiel > 25 Mo
     * - Déclenche la création d’un produit
     * - Vérifie qu’un email d’alerte est bien envoyé
     */
    public function testMailerAlertOnLogExceeded(): void
    {
        $logDir = __DIR__ . '/../../var/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $date = (new \DateTimeImmutable())->format('Ymd');
        $file = "$logDir/{$date}-gestion-produit.log";

        // Simule un fichier log dépassant la taille max (30 Mo)
        file_put_contents($file, str_repeat("x", 31 * 1024 * 1024));

        $client = static::createClient();
        $client->request('POST', '/api/products', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Produit Avec Log Plein',
            'price' => 19.99
        ]));

        $this->assertResponseStatusCodeSame(201);

        // Vérifie qu’un mail a bien été envoyé
        $messages = $this->transport->getSent();
        $this->assertNotEmpty($messages, 'Un mail doit être envoyé');
        $this->assertStringContainsString('Alerte', $messages[0]->getSubject());
    }
}
