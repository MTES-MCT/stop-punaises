# Stop-punaises.gouv.fr

La solution stop-punaises a pour objectifs de mettre en relation des usagers signalant des problèmes d'infestations de punaises avec des entreprises labellisées
, d'informer sur les punaises de lit et les démarches pour traiter son logement et de créer un observatoire des punaises de lit.

Stop-punaises est une application web écrite en PHP et utilisant le framework Symfony, avec une base de données MySQL.

## Environnement

Cette application est déployé chez Scalingo, hébergé par Outscale.

- Production: [stop-punaises.gouv.fr](https://stop-punaises.gouv.fr)

- Staging: [stop-punaises-staging.osc-fr1.scalingo.io](https://stop-punaises-staging.osc-fr1.scalingo.io)


## Pré-requis

Requirements|Release
------------|--------
Docker engine (minimum)| [20.10.17](https://www.docker.com/)
Scalingo CLI (minimum) | [1.24](https://doc.scalingo.com/platform/cli/start)
AWS CLI OVH Object storage (optionnel) | [1.25](https://docs.ovh.com/fr/storage/s3/debuter-avec-s3/#utilisation-de-aws-cli)
PHP (optionnel)| [8.3.*](https://www.php.net/)
Composer (optionnel) | [2.4.*](https://getcomposer.org/download/)
Node (optionnel)| [16.*](https://nodejs.org/en/)

## Environnement technique

### Versions des dépendances

Service|Version
-------|-------
Nginx | 1.20.2
PHP | 8.3.x (latest)
MySQL | 8.0.31
Redis | 7.0.x (latest)

### URL(s)

Description| Lien
---------|------------- 
Plateforme stop-punaises| [localhost:8090](http://localhost:8090)
phpMyAdmin | [localhost:8091](http://localhost:8091)
MailCatcher  | [localhost:1090](http://localhost:1090)


### Hôtes des environnements et ports

Merci de vérifier que ces ports ne soient pas utilisés sur votre poste local

Service| Hostname              |Port number
-------|-----------------------|-----------
Nginx| stopunaises_nginx     | **8090**
PHP-FPM| stopunaises_phpfpm     |**9000**
MySQL| stopunaises_mysql      |**3308**
PhpMyAdmin | stopunaises_phpmyadmin | **8091**
Mailcatcher| stopunaises_mailer     | **1035** et **1090**
Redis| histologe_redis      | /

## Installation

### Commandes

Un [Makefile](Makefile) est disponible, qui sert de point d’entrée aux différents outils :

```
$ make help
```

### Lancement

1. Executer la commande

La commande permet d'installer l'environnement de developpement avec un jeu de données

```
$ make build
```

2. Configurer les variables d'environnements du service object storage S3 d'OVH Cloud

> Se rapprocher de l'équipe afin de vous fournir les accès au bucket de dev

```
# .env.local
### object storage S3 ###
S3_ENDPOINT=
S3_KEY=
S3_SECRET=
S3_BUCKET=
S3_URL_BUCKET=
### object storage S3 ###
```

3. Se rendre sur http://localhost:8090

> Pour tous les utilisateurs, le mot de passe est `punaises`

Territoire             | Email                               | Rôle       
-----------------------|-------------------------------------|----------------------
N/A                    | admin@punaises.fr               | ROLE_ADMIN 
Bouches-du-Rhône       | company-01@punaises.fr | ROLE_ENTREPRISE
Rhône       | company-69-02@punaises.fr | ROLE_ENTREPRISE

## Documentaton projet

[Consulter la documentation](https://github.com/MTES-MCT/stop-punaises/wiki)

## Contribuer

[Consulter les instructions de contributions](./CONTRIBUTING.md).
