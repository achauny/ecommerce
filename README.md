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

# Flashbag
Pour avoir les flashbag sur l'ensemble des pages, on va les mettre dans le modèle `base.html.twig` :
```yaml
{% for message in app.flashes('success') %}
    <div class="flash-notice">
        {{ message }}
    </div>
{% endfor %}

{% for message in app.flashes('error') %}
    <div class="flash-notice">
        {{ message }}
    </div>
{% endfor %}

{# .. Les autres type de flashbag .. #}

{% block body %}

{% endblock %}
```

# Nouvelle structure
Il existe différent types de structure, notamment l'architecture DDD mais celle ci-dessous est très facile d'utilisation et ne change pas trop les habitudes.

Le but est de séparer le code en 6 grandes parties :
* `Entity` : Contiendra toutes les entités
* `Controller` : Contiendra tous les controllers, cependant ceux-ci ne serviront qu'a générer les méthodes et les routes. Il ne doit y avoir aucune logique métier dedans
* `Repository` : Aura toutes les méthodes pour récupérer les données comme la structure classique
* `Services` : Chaque Entité aura un service, c'est celui-ci qui va gérer toute la partie métier
* `Model` : Chaque Entité aura un model, c'est lui qui va gérer les intéraction avec la base de données (ajout, modification, suppression)
* `Utils` : Va rassembler les utilitaires qui seront appeler plusieurs fois. Cela évite la dupplication de code. Dans la structure classique MVC, ils correspondent au service.
Dans notre exemple on a un `FormUtils` qui permet de centralisé la validation des formulaires.

## Avantages
En plaçant la logique métier dans des services, on créer des composants réutilisables qui peuvent être appelés à partir de n'importe quel contrôleur.

En isolant la logique métier dans des `Services`, on facilite les tests unitaires en testant chaque service individuellement.

Les `Utils` vont permettre par exemple de gérer la validation des formulaires à un seul et même endroit. Cela peut être utile si on veut activer un système de log ou faire une logique particulière, on n'aura pas à le faire à plusieurs endroits.

Avec une structure bien définie, il est plus facile de localiser et de modifier le code, ce qui rend la maintenance plus simple et moins sujette aux erreurs.
L'ajout et/ou la maintenance de nouvelles fonctionnalités est également plus facile.


# Voter

Les Voters sont des classes utilisées dans Symfony pour implémenter un contrôle d'accès basé sur une logique métier.

Ils retournent true si l'accès est autorisé et false si l'accès est refusé.

## Création d'un Voter générique
Créer un fichier `CustomVoter.php` dans le dossier `src` puis `Security` afin de créer le Voter générique.

Il est possible de créer un Voter par logique métier mais cela sera plus maintenable d'avoir un seul et même voter avec les différentes méthodes.


```php
class CustomVoter extends Voter
{
     // Les constantes sont utilisé pour lister les différentes types d'action qui seront écoutés
     public const EDIT = 'edit';
     public const VIEW = 'view';
     
     // La méthode supports est appelé en premier et va vérifier si dans l'attribut du voter de la méthode on a mis soit edit soit view
     protected function supports(string $attribute, mixed $subject): bool
    {
        // $subject = type de l'objet
        return in_array($attribute, [self::EDIT, self::VIEW]);
    }
    // ....
}
```

## Appel du voter dans le controlleur

On veut bloquer l'édition du produit à l'utilisateur qui l'a créé seulement :
```php
// ...
#[IsGranted('view', 'product')] // On met le type de vérification et le type d'objet
public function edit(Product $product): Response
{

}
```
`IsGranted` :
L'annotation IsGranted est utilisée pour vérifier si un utilisateur a accès à une certaine fonctionnalité ou à une certaine ressource dans Symfony.

`edit` :
C'est le type d'action que l'on vérifie.
C'est ce type qu'on récupère dans notre Voter afin qu'on applique telle ou telle logique métier par rapport au type (edit, view...)


`product` :
C'est la ressource sur laquelle on vérifie les autorisations. Il est important de le préciser car cela sera le type de l'objet récupérer dans le Voter.

## Mise en place du système générique

### Arguments sur les méthodes des controlleurs

Pour passer nos arguments de chaque méthode au voter, on va utiliser le tableau `options` pour passer tous nos arguments :
```php
#[Route('/produits/{id}/modifier/', name: 'app_products_edit', options: ['methodApply' => 'verifyCreatedBy', 'redirectRoute' => 'app_products'])]
#[IsGranted('edit', 'product')]
public function edit(Request $request, Product $product): Response
{
    // ...
}
```
Dans le tableau options, on indique la méthode a appelé pour appliquer la logique métier dans le Voter et la route vers laquelle l'utilisateur sera redirigé si le voter renvoie `false`.

### Méthode générique

La méthode suivante à pour but de récupérer la méthode du controller qui est appelée.
En obtenant cette méthode on va pouvoir lire les attributs de la méthode et ainsi récupérer notre tableau `options` :

```php
class CustomVoter extends Voter
{
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        $request = $this->requestStack->getCurrentRequest();

        $controller = $request?->attributes->get('_controller');
        $tabController = explode('::', $controller);

        $currentController = new \ReflectionClass($tabController[0]);
        $method = $currentController->getMethod($tabController[1]);
        $options = current($method->getAttributes(Route::class))->getArguments()['options'];

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match($attribute) {
            self::EDIT => $this->{$options['methodApply']}($request, $subject, $user, $options['redirectRoute']),
            default => true,
        };
    }
}
```

Exemple de logique métier dans le Voter :

```php
protected function verifyCreatedBy(Request $request, $subject, UserInterface $user, string $routeRedirect): bool{
    if (!($subject->getCreatedBy()->getId() === $user->getId())) {
        $request->getSession()->getFlashBag()->add('error', 'Vous n\'avez pas accès à cette donnée.');
        $url = $this->urlGenerator->generate($routeRedirect);
        header('Location: ' . $url);
        exit;
    }
    return true;
}
```