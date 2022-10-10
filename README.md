# Stop-punaises.beta.gouv.fr

La solution stop-punaises a pour objectifs de mettre en relation des usagers signalant des problèmes d'infestations de punaises avec des entreprises labellisées
, d'informer sur les punaises de lit et les démarches pour traiter son logement et de créer un observatoire des punaises de lit.

Stop-punaises est une application web écrite en PHP et utilisant le framework Symfony, avec une base de données MySQL.

Cette application est déployé chez Scalingo, hébergé par Outscale.

- Production: [stop-punaises.beta.gouv.fr](https://stop-punaises.beta.gouv.fr)

- Staging: [stop-punaises-staging.osc-fr1.scalingo.io](https://stop-punaises-staging.osc-fr1.scalingo.io)


## Pré-requis

Requirements|Release
------------|--------
Docker engine (minimum)| [20.10.17](https://www.docker.com/)
Scalingo CLI (minimum) | [1.24](https://doc.scalingo.com/platform/cli/start)
AWS CLI OVH Object storage (optionnel) | [1.25](https://docs.ovh.com/fr/storage/s3/debuter-avec-s3/#utilisation-de-aws-cli)
PHP (optionnel)| [8.1.*](https://www.php.net/)
Composer (optionnel) | [2.4.*](https://getcomposer.org/download/)
Node (optionnel)| [16.*](https://nodejs.org/en/)


## Clone du projet

### HTTP
```bash
git clone https://github.com/MTES-MCT/stop-punaises.git
```

### SSH
```
git clone git@github.com:MTES-MCT/stop-punaises.git
```

[Vérification des clés SSH existantes](https://docs.github.com/en/authentication/connecting-to-github-with-ssh/checking-for-existing-ssh-keys)

[Génération d'une nouvelle clé SSH](https://docs.github.com/en/authentication/connecting-to-github-with-ssh/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent)

## Environnement

### Versions des dépendances

Service|Version
-------|-------
Nginx | 1.20.2
PHP | 8.1.x (latest)
MySQL | 5.7.38

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

## Installation

### Commandes

Un [Makefile](Makefile) est disponible, qui sert de point d’entrée aux différents outils :

```
$ make help

build                          Install local environement
run                            Start containers
down                           Shutdown containers
sh                             Log to phpfpm container
mysql                          Log to mysql container
logs                           Show container logs
console                        Execute application command
composer                       Install composer dependencies
clear-cache                    Clear cache prod: make-clear-cache env=[dev|prod|test]
create-db                      Create database
drop-db                        Drop database
load-data                      Load database from dump
load-migrations                Play migrations
load-fixtures                  Load database from fixtures
create-db-test                 Create test database
test                           Run all tests
test-coverage                  Generate phpunit coverage report in html
e2e                            Run E2E tests
stan                           Run PHPStan
cs-check                       Check source code with PHP-CS-Fixer
cs-fix                         Fix source code with PHP-CS-Fixer
```

### Lancement

1. Executer la commande

La commande permet d'installer l'environnement de developpement avec un jeu de données

```
make build
```
