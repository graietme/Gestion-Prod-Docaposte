<?php
/**
 * Auteur : Mehdi Graiet
 * Version : 1.0.0
 * Description : Contrôleur API Produits
 * 
 * Ce contrôleur expose trois endpoints REST :
 * - POST   /api/products      → création d’un produit
 * - GET    /api/products/{id} → lecture d’un produit
 * - PUT    /api/products/{id} → mise à jour d’un produit
 * 
 * Chaque action retourne une réponse JSON normalisée avec un code HTTP adapté.
 */

namespace App\Controller;

use App\DTO\CreateProductDTO;
use App\DTO\UpdateProductDTO;
use App\Repository\ProductRepository;
use App\Service\ProductService;
use App\Utils\ErrorFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/products')]
final class ProductController extends AbstractController
{
    /**
     * Création d’un produit (POST /api/products)
     * 
     * Étapes :
     * 1. Récupère les données JSON envoyées par le client
     * 2. Valide les données via un DTO + Validator Symfony
     * 3. Si erreurs → retourne un JSON avec code 400
     * 4. Si valide → délègue la création au ProductService
     * 5. Retourne le produit créé avec code 201 Created
     */
    #[Route('', name: 'api.product.create', methods: ['POST'])]
    public function create(Request $request, ValidatorInterface $validator, ProductService $service): JsonResponse
    {
        $data = $request->toArray();

        $dto = new CreateProductDTO($data['name'] ?? '', $data['price'] ?? 0);

        // Validation des règles métier (nom, prix > 0, etc.)
        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['status' => 400, 'errors' => ErrorFormatter::format($errors)], 400);
        }

        $product = $service->create($dto);

        return $this->json(['status' => 201, 'data' => [
            'id' => (string) $product->getId(),
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'price' => $product->getPrice(),
            'createdAt' => $product->getCreatedAt()->format(\DateTime::ATOM)
        ]], 201);
    }

    /**
     * Lecture d’un produit par son UUID (GET /api/products/{id})
     * 
     * Étapes :
     * 1. Récupère l’ID fourni dans l’URL
     * 2. Vérifie si le produit existe en base
     * 3. Si inexistant → retourne 404 Not Found
     * 4. Si trouvé → retourne les informations du produit (200 OK)
     */
    #[Route('/{id}', name: 'api.product.get', methods: ['GET'])]
    public function get(string $id, ProductRepository $repo): JsonResponse
    {
        $product = $repo->find($id);
        if (!$product) {
            return $this->json(['status' => 404, 'error' => 'Product not found'], 404);
        }

        return $this->json(['status' => 200, 'data' => [
            'id' => (string) $product->getId(),
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'price' => $product->getPrice(),
            'createdAt' => $product->getCreatedAt()->format(\DateTime::ATOM),
            'updatedAt' => $product->getUpdatedAt()?->format(\DateTime::ATOM)
        ]]);
    }

    /**
     * Mise à jour d’un produit existant (PUT /api/products/{id})
     * 
     * Étapes :
     * 1. Récupère l’ID du produit depuis l’URL
     * 2. Vérifie si le produit existe
     * 3. Si inexistant → retourne 404 Not Found
     * 4. Si trouvé → valide les données reçues avec UpdateProductDTO
     * 5. Si erreurs → retourne 400 Bad Request avec détails
     * 6. Si valide → délègue la mise à jour au ProductService
     * 7. Retourne le produit mis à jour (200 OK)
     */
    #[Route('/{id}', name: 'api.product.update', methods: ['PUT'])]
    public function update(string $id, Request $request, ValidatorInterface $validator, ProductRepository $repo, ProductService $service): JsonResponse
    {
        $product = $repo->find($id);
        if (!$product) {
            return $this->json(['status' => 404, 'error' => 'Product not found'], 404);
        }

        $data = $request->toArray();
        $dto = new UpdateProductDTO($data['name'] ?? '', $data['price'] ?? 0);

        // Validation des données
        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['status' => 400, 'errors' => ErrorFormatter::format($errors)], 400);
        }

        $product = $service->update($product, $dto);

        return $this->json(['status' => 200, 'data' => [
            'id' => (string) $product->getId(),
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'price' => $product->getPrice(),
            'createdAt' => $product->getCreatedAt()->format(\DateTime::ATOM),
            'updatedAt' => $product->getUpdatedAt()?->format(\DateTime::ATOM)
        ]]);
    }
}
