# Stop-punaises.beta.gouv.fr

La solution stop-punaises a pour objectifs de mettre en relation des usagers signalant des problèmes d'infestations de punaises avec des entreprises labellisées
, d'informer sur les punaises de lit et les démarches pour traiter son logement et de créer un observatoire des punaises de lit.

Stop-punaises est une application web écrite en PHP et utilisant le framework Symfony, avec une base de données MySQL.

Cette application est déployé chez Scalingo, hébergé par Outscale.

- Production: [stop-punaises.beta.gouv.fr](https://stop-punaises.beta.gouv.fr)

- Staging: [stop-punaises-staging.osc-fr1.scalingo.io](https://stop-punaises-staging.osc-fr1.scalingo.io)

## Démarrer l'application

```
symfony server:start -d
```