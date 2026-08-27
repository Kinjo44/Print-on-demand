# Site de commande d'impression 3D

Site vitrine minimaliste permettant de déposer une demande d'impression 3D avec un ou plusieurs fichiers `.stp` ou `.step`.

## Fonctionnalités

- Formulaire en français pour collecter le nom, le prénom, la description du besoin, le niveau d'urgence, la technologie souhaitée, la couleur de préférence et un commentaire.
- Upload multiple de fichiers avec validation côté navigateur et côté serveur des extensions `.stp` et `.step`.
- Envoi automatique de la demande et des pièces jointes à `cyclone44@wanadoo.fr` via la fonction PHP `mail()`.

## Lancement local

```bash
php -S localhost:8000
```

Ouvrez ensuite <http://localhost:8000>.

> L'envoi d'e-mail nécessite un serveur PHP configuré avec un agent SMTP compatible avec `mail()`.
