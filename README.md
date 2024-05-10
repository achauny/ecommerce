# Mise en place du login form

## Entité User
On créer l'entité `User` via le maker.
```ssh
php bin/console make:user
```
On répond oui a tous et on choisi l'attribut qui va être unique, soit l'email, soit l'username soit un uuid.
On répond ouipour le hash du mot de passe.

### Mise à jour de la base de données (migrations)

Les migrations dans Symfony (et dans d'autres frameworks) servent à gérer les modifications de schéma de base de données de manière contrôlée et reproductible.

On met à jour la base de données pour ajouter la table `User`.
```ssh
 php bin/console make:migration
 ```

```ssh
 php bin/console doctrine:migrations:migrate
```
On répond oui pour éxecuter la migration.

## Login form
On utilise le maker pour générer le formulaire de connexion
```ssh
php bin/console make:auth
```
On choisi le choix `[1] Login form authenticator` pour mettre en place un formulaire de connexion.


On choisi le nom de l'Authenticator, c'est lui qui va gérer la logique de connexion. Par défaut on laisse `AppCustomAuthenticator`.
Pareil pour le nom du controller, on laisse  `SecurityController`.


### Création du controller
Comme indiqué, il faut change dans le `AppCustomAuthenticator` la route vers laquelle on redirige après que l'utilisateur se connecte.

On va donc créer le `DefaultController` dans le dossier `Controller`. 
```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('default/home.html.twig');
    }
}
```

### Création de la vue

Puis créer la vue `home.html.twig` dans le dossier `templates` puis `default` :
```twig
{% extends 'base.html.twig' %}

{% block title %} Accueil {% endblock %}

{% block body %}
    Page d'accueil
{% endblock %}
```


### Redirection vers la page d'accueil

Après que l'utilisateur soit connecté, le `AppCustomAuthenticator` va rediriger vers une 
```php
public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
{
   // ... 
    return new RedirectResponse($this->urlGenerator->generate('app_home'));
}
```

### Obliger la connexion pour l'ensemble des routes
Pour accéder à l'application, l'utilisateur doit forcément se connecter, on protège donc toutes les routes qui commencent par `/` sauf `/login`.

Dans le `security.yaml` du dossier `config/packages` :
```yaml
...
access_control:
    - { path: ^/login, roles: PUBLIC_ACCESS }
    - { path: ^/, roles: ROLE_USER }
```

## Création d'un utilisateur dans la base de données
Pour créer un utilisateur dans la base de données, on aura besoin du mot de passe hashé.
```ssh
php bin/console security:hash-password
```
Puis saisir le mot de passe à encoder. La ligne de commande nous retournera le `Password hash` qu'il faudra mettre dans le champ `password` pour l'utilisateur dans la base de données.

On peut ensuite se connecter avec les informations saisie dans la base de données.


# Mise en place d'un User Checker

Un User Checker est une fonctionnalité de sécurité dans Symfony qui est utilisée pour vérifier l'état de l'utilisateur avant qu'il ne soit authentifié. Cela permet de bloquer l'accès aux utilisateurs qui ne sont pas activés, dont le compte a été supprimé ou qui ont d'autres restrictions qui les empêchent de se connecter.

## Ajout du champ enable dans l'entité User
On utilise le maker pour ajouter le champ `enable` :
```ssh
 php bin/console make:entity User
```

On met à jour la base de données :
```ssh
 php bin/console make:migration
 ```

```ssh
 php bin/console doctrine:migrations:migrate
```

## Ajout du UserChecker
Dans le dossier `Security` on ajoute un fichier `UserChecker.php` qui va vérifier avant la connexion si l'utilisateur est bien activé.
```php
<?php

namespace App\Security;

use App\Entity\User as AppUser;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof AppUser) {
            return;
        }

        // On vérifie si l'utilisateur est activé
        if (!$user->isEnable()) {
            // the message passed to this exception is meant to be displayed to the user
            throw new CustomUserMessageAccountStatusException('Votre compte a été désactivé');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof AppUser) {
            return;
        }

        /*// user account is expired, the user may be notified
        if ($user->isExpired()) {
            throw new AccountExpiredException('...');
        }*/
    }
}
```

### Paramétrage du UserChecker
Il faut définir sur quel firewall on va utiliser le UserChecker. Il faut se rendre dans le fichier `security.yaml` et pour le firewall `main` définir le paramètre `user_checker` :
```yaml
firewalls:
  #...
  main:
    #...
    custom_authenticator: App\Security\AppAuthenticator
    user_checker: App\Security\UserChecker
    #...
```

# Trait
En PHP, un trait est un mécanisme permettant la réutilisation d'attributs et de méthodes dans différentes classes.
Au lieu de les créer dans toutes les entités, on va importer le trait qui va importer les attributs et méthodes.

## Installation

```bash
composer require stof/doctrine-extensions-bundle
```

## Configuration
Changer la configuration par defaut dans le `stof_doctrine_extensions.yaml` créé par défaut (package/config) pour activer le timestampable pour les dates :
```yaml
stof_doctrine_extensions:
    default_locale: fr_FR
    orm:
        default:
            timestampable: true
```

## Utilisation
On veut ajouter les champs `createdAt` et `updatedAt` et les getters/setters.

### Création du trait
Créer un dossier `Traits` dans le dossier `Entity` :
```php
<?php
namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait DateTrait {

    #[ORM\Column(name: "created_at", type: "datetime", nullable: true)]
    #[Gedmo\Timestampable(on: "create")]
    private ?\DateTimeInterface $createdAt;

    #[ORM\Column(name: "created_at", type: "datetime", nullable: true)]
    #[Gedmo\Timestampable(on: "update")]
    private ?\DateTimeInterface $updatedAt;

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
```

### Importation du trait dans une entité
Dans la classe où on souhaite utiliser le trait on l'importe avec le use :
```php
use App\Entity\Traits\DateTrait;

class Product
{
    use DateTrait;
    
    // ...
}
```

Dans cette entité `Product`, chaque fois qu'un objet `Product` est créé, le trait `DateTrait` est utilisé pour vérifier s'il y a des annotations Gedmo avec les instructions `on: "create"`. 

De même, chaque fois qu'un objet `Product` est mis à jour, le trait vérifie les annotations Gedmo avec les instructions `on: "update"`.

Les champs `createdAt` et `updatedAt` sont automatiquement mis à jour en conséquence.

### Mise à jour de la base de données
On met à jour la base de données pour ajouter les champs `createdAt` et `updatedAt`.
```ssh
 php bin/console make:migration
 ```

```ssh
 php bin/console doctrine:migrations:migrate
```