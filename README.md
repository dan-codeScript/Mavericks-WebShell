# Mavericks-WebShell
 Une WebShell sécurisée en PHP avec authentification et liste blanche de commandes

![MavWbshl](https://github.com/user-attachments/assets/e7c63080-afd7-4fc1-98c2-4f701d34d0ed)

Fonctionnalités

✔ Authentification sécurisée (mot de passe fort)
✔ Liste blanche de commandes (seules les commandes autorisées sont exécutées)
✔ Historique des commandes (stocké en session)
✔ Gestion de fichiers (upload, téléchargement, suppression)
✔ Interface intuitive avec autocomplétion des commandes
✔ Protection contre les injections (escaping des commandes shell)

📌 Avertissement

⚠ À utiliser uniquement à des fins légitimes (administration système autorisée).
⚠ Les développeurs ne sont pas responsables des utilisations abusives.

⚙ Installation

    Téléchargez le projet :
    sh

téléchareger le code

Configurez le mot de passe :
Modifiez AUTH_PASSWORD dans mavericks_webshell_pro.php

    define('AUTH_PASSWORD', 'changeme123'); // 🔐 Changez ceci !

    Déployez sur un serveur web (Apache/Nginx avec PHP).

🔧 Utilisation

    Accès à l'interface :

        Ouvrez http://votre-serveur.com/mavericks_webshell_pro.php dans un navigateur.

        Connectez-vous avec le mot de passe défini.

![MavWbshl](https://github.com/user-attachments/assets/49c6c95e-df82-4408-b2db-863086e83391)

  
    Gestion des fichiers :

        📥 Télécharger : Cliquez sur un fichier dans la liste.

        📤 Uploader : Utilisez le formulaire d'upload.

        🗑 Supprimer : Entrez le nom du fichier et validez.

        ![cink](https://github.com/user-attachments/assets/40d69c8a-969b-4306-87ae-429e23a9debd)


