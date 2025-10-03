# 1. Choix de conception

Pour cette API, j’ai fait le choix de séparer clairement les responsabilités :

DTO (CreateProductDTO / UpdateProductDTO) :
Ils permettent de valider les données envoyées par l’API avant même d’atteindre la base.
Ça évite d’exposer directement l’entité Doctrine et ça rend le code plus souple si la structure de la base évolue.
J’ai ajouté une contrainte personnalisée pour que la longueur du nom de produit soit configurable dans app.yaml, afin que la règle métier puisse changer sans modifier le code.

Service métier (ProductService) :
Toute la logique métier est concentrée ici : création, mise à jour, génération et vérification du SKU, enregistrement en base, log des actions et envoi d’alertes email si besoin.
Le contrôleur reste simple et ne fait qu’orchestrer les appels.

SKU Generator :
Service indépendant qui génère un identifiant unique de type PROD-XXXX-XXXXXXX.
Même si une collision est peu probable, j’ai prévu une vérification et une régénération automatique.

Logging et alertes :
J’ai configuré Monolog pour générer des fichiers de logs quotidiens avec un nom clair : AAAAMMJJ-gestion-produit.log.
Chaque fichier est limité à 30 Mo et les plus anciens sont automatiquement supprimés après 60 jours.
En plus, un mail d’alerte peut être envoyé si la taille des logs dépasse la limite, ce qui permet de surveiller facilement la santé de l’application.

# 2. Risques ou inconvénients potentiels

Les collisions de SKU : elles sont rares, mais pas impossibles. J’ai prévu une régénération automatique, mais en cas de très forte volumétrie, il faudrait envisager un système encore plus robuste.

Les validations métier : par exemple, la règle des 5 à 100 caractères pour le nom est pratique pour le SEO, mais elle peut être perçue comme trop stricte par certains utilisateurs. C’est un point qui doit être validé par l’équipe commerciale.

La performance avec Doctrine : l’ORM est très pratique mais peut devenir lourd avec une base énorme. Sur un trafic très élevé, il faudrait optimiser les requêtes ou utiliser des requêtes SQL plus directes pour certaines opérations critiques.

Les logs : même si la rotation est en place, en cas de pic d’activité les logs peuvent encore devenir volumineux. Sans compression ou archivage externe, le serveur peut saturer.

# 3. Préparation pour un environnement de production à fort trafic

Pour préparer cette API à la production et à une utilisation intensive, je mettrais en place plusieurs optimisations :

Performances et scalabilité : mise en cache (Redis/Memcached) pour réduire les accès DB, indexation des colonnes importantes (comme sku et name).

Base de données : mise en place de la réplication ou du sharding si la volumétrie devient importante, afin de répartir la charge, effectuer des sauvegardes fréquentes avec un CRON.

Logs et monitoring : connecter les logs à un système centralisé (ELK Stack, Grafana/Prometheus) pour pouvoir suivre en temps réel les erreurs, la charge et l’utilisation.

Sécurité : protéger les endpoints avec une authentification JWT ou OAuth2 et mettre en place un système de limitation de débit (rate limiting) pour éviter les abus.

Documentation : fournir une documentation claire de l’API avec OpenAPI/Swagger pour faciliter l’intégration par d’autres équipes ou partenaires.

Les logs : Compression et Archivage des logs tous les 30 jours sur un autre serveur.

# Notes techniques - Version 1.0.0

## Identifiants
- UUIDv6 utilisé (optimisé pour l’ordre temporel et les index DB).

## Nom produit
- Configurable via `app.yaml` (min=5, max=100).
- SEO friendly (~80 chars visibles sur Google).

## SKU
- Format `PROD-XXXX-XXXXXXX`
- Longueur ≤ 17
- Collision rare → régénération automatique

## Prix
- > 0
- Valeur min exacte à discuter avec le service commercial.

## Logs
- Gérés via **Monolog** (rotation, alerte mail).
- Mail d’alerte via Symfony Mailer.
- Configurable (`monolog.yaml`).

## Tests
- Unitaires :
  - `SkuGeneratorTest` (format, unicité, longueur, préfixe).
- Fonctionnels :
  - `ProductControllerTest` (POST, GET, PUT, erreurs, alertes).

## Documentation
- API documentée via **NelmioApiDocBundle**.
- Accessible sur `/api/doc`.

## Améliorations possibles
- Ajouter `DELETE /api/products/{id}` et `GET /api/products` (liste).
- Sécurité : JWT ou API Key (LexikJWT).
- Monitoring centralisé (Elastic, Graylog).
