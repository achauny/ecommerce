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