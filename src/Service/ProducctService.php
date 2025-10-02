<?php
/**
 * Auteur : Mehdi Graiet
 * Version : 1.0.0
 * Service métier ProductService
 * - Gère la création et mise à jour des produits
 */

namespace App\Service;

use App\DTO\CreateProductDTO;
use App\DTO\UpdateProductDTO;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class ProductService
{
    private int $skuMaxLength;

    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly ProductRepository $repo,
        private readonly SkuGenerator $skuGenerator,
        private readonly LoggerInterface $logger,
        private readonly MailerInterface $mailer,
        private readonly string $alertEmail,
        ParameterBagInterface $params
    ) {
        $this->skuMaxLength = $params->get('app.product.sku_max_length');
    }

    /** Crée un produit à partir d’un DTO validé */
    public function create(CreateProductDTO $dto): Product
    {
        $sku = $this->skuGenerator->generate($dto->name);
        while ($this->repo->findOneBy(['sku' => $sku])) {
            $sku = $this->skuGenerator->generate($dto->name);
        }

        if (strlen($sku) > $this->skuMaxLength) {
            throw new \InvalidArgumentException("Le SKU dépasse la longueur maximale autorisée.");
        }

        $product = new Product($dto->name, $sku, $dto->price);

        $em = $this->doctrine->getManager();
        $em->persist($product);
        $em->flush();

        $this->logger->info("Produit créé", ['sku' => $sku, 'name' => $dto->name]);

        return $product;
    }

    /** Met à jour un produit existant */
    public function update(Product $product, UpdateProductDTO $dto): Product
    {
        $product->setName($dto->name);
        $product->setPrice($dto->price);
        $product->setUpdatedAt(new \DateTimeImmutable());

        $em = $this->doctrine->getManager();
        $em->flush();

        $this->logger->info("Produit mis à jour", ['sku' => $product->getSku()]);

        return $product;
    }

    /** Envoie un mail d’alerte critique */
    public function alert(string $message): void
    {
        $email = (new Email())
            ->from($this->alertEmail)
            ->to($this->alertEmail)
            ->subject("Alerte API Produits")
            ->text($message);

        $this->mailer->send($email);
    }
}
