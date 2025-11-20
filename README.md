# PSR-11 Dependency Injection Container

[English](#english) | [Français](#français)

---

<a name="english"></a>
## English

A lightweight PHP Dependency Injection Container implementing the PSR-11 standard. This library is designed for simplicity, performance, and ease of use.

### Features
- **PSR-11 Compliant**: Interoperable with other libraries.
- **Autowiring**: Automatically resolves dependencies using Reflection.
- **Compilation/Caching**: Compiles definitions to plain PHP for zero-overhead production performance.
- **Parameter Resolution**: Supports `#{variable}` syntax in strings.

### Installation

```bash
composer require phpdevcommunity/psr11-dependency-injection
```

### Usage

#### 1. Basic Usage (ContainerBuilder)

The `ContainerBuilder` is the recommended way to create your container.

```php
use PhpDevCommunity\DependencyInjection\ContainerBuilder;

$builder = new ContainerBuilder();

// Add definitions
$builder->addDefinitions([
    'database.host' => 'localhost',
    'database.name' => 'app_db',
    PDO::class => function ($c) {
        return new PDO(
            "mysql:host={$c->get('database.host')};dbname={$c->get('database.name')}",
            "root",
            ""
        );
    }
]);

$container = $builder->build();

$pdo = $container->get(PDO::class);
```

#### 2. Autowiring

You don't need to define every class manually. If a class exists, the container will try to instantiate it and inject its dependencies automatically.

```php
class Mailer {
    // ...
}

class UserManager {
    public function __construct(Mailer $mailer) {
        $this->mailer = $mailer;
    }
}

// No definitions needed!
$container = (new ContainerBuilder())->build();

$userManager = $container->get(UserManager::class);
```

#### 3. Production Performance (Caching)

In production, using Reflection for every request is slow. You can enable compilation to generate a PHP file containing all your definitions and resolved dependencies.

**How it works:**
1. The first time, it inspects your code and generates a PHP file.
2. Subsequent requests load this file directly, bypassing Reflection entirely.

```php
$builder = new ContainerBuilder();
$builder->addDefinitions([/* ... */]);

// Enable compilation
// Ideally, do this only in production or when the cache file doesn't exist
$builder->enableCompilation(__DIR__ . '/var/cache/container.php');

$container = $builder->build();
```

> **Note:** The compiler recursively discovers and compiles all dependencies for "total" resolution caching.

#### 4. Variable Replacement

You can use placeholders in your string definitions.

```php
$builder->addDefinitions([
    'app.path' => '/var/www/html',
    'app.log_file' => '#{app.path}/var/log/app.log',
]);
```

---

<a name="français"></a>
## 🇫🇷 Français

Un conteneur d'injection de dépendances PHP léger implémentant le standard PSR-11. Cette bibliothèque est conçue pour la simplicité, la performance et la facilité d'utilisation.

### Fonctionnalités
- **Compatible PSR-11** : Interopérable avec d'autres bibliothèques.
- **Autowiring** : Résout automatiquement les dépendances via la Réflexion.
- **Compilation/Cache** : Compile les définitions en PHP pur pour des performances maximales en production.
- **Résolution de paramètres** : Supporte la syntaxe `#{variable}` dans les chaînes.

### Installation

```bash
composer require phpdevcommunity/psr11-dependency-injection
```

### Utilisation

#### 1. Utilisation de base (ContainerBuilder)

Le `ContainerBuilder` est la méthode recommandée pour créer votre conteneur.

```php
use PhpDevCommunity\DependencyInjection\ContainerBuilder;

$builder = new ContainerBuilder();

// Ajouter des définitions
$builder->addDefinitions([
    'database.host' => 'localhost',
    'database.name' => 'app_db',
    PDO::class => function ($c) {
        return new PDO(
            "mysql:host={$c->get('database.host')};dbname={$c->get('database.name')}",
            "root",
            ""
        );
    }
]);

$container = $builder->build();

$pdo = $container->get(PDO::class);
```

#### 2. Autowiring (Injection Automatique)

Vous n'avez pas besoin de définir chaque classe manuellement. Si une classe existe, le conteneur essaiera de l'instancier et d'injecter ses dépendances automatiquement.

```php
class Mailer {
    // ...
}

class UserManager {
    public function __construct(Mailer $mailer) {
        $this->mailer = $mailer;
    }
}

// Aucune définition nécessaire !
$container = (new ContainerBuilder())->build();

$userManager = $container->get(UserManager::class);
```

#### 3. Performance en Production (Cache)

En production, utiliser la Réflexion à chaque requête est lent. Vous pouvez activer la compilation pour générer un fichier PHP contenant toutes vos définitions et dépendances résolues.

**Comment ça marche :**
1. La première fois, il inspecte votre code et génère un fichier PHP.
2. Les requêtes suivantes chargent directement ce fichier, contournant totalement la Réflexion.

```php
$builder = new ContainerBuilder();
$builder->addDefinitions([/* ... */]);

// Activer la compilation
// Idéalement, faites ceci uniquement en production
$builder->enableCompilation(__DIR__ . '/var/cache/container.php');

$container = $builder->build();
```

> **Note :** Le compilateur découvre et compile récursivement toutes les dépendances pour une mise en cache "totale" de la résolution.

#### 4. Remplacement de variables

Vous pouvez utiliser des espaces réservés dans vos définitions de chaînes.

```php
$builder->addDefinitions([
    'app.path' => '/var/www/html',
    'app.log_file' => '#{app.path}/var/log/app.log',
]);
```

## License

MIT License.
