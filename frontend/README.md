# Cours React.js pour débutant avec connexion PHP

## Installation

### 1. Installer Node.js
Téléchargez et installez Node.js depuis https://nodejs.org/

### 2. Créer le projet React
```bash
npx create-react-app mon-app-react
cd mon-app-react
npm start
```

Le serveur démarre sur http://localhost:3000

## Structure du projet

```
mon-app-react/
├── node_modules/
├── public/
│   └── index.html
├── src/
│   ├── App.js          (composant principal)
│   ├── index.js        (point d'entrée)
│   └── services/
│       └── api.js      (communication avec le backend)
└── package.json
```

## Problème de CORS avec PHP

Votre backend PHP doit autoriser les requêtes depuis React. Ajoutez ceci au début de vos fichiers PHP :

```php
<?php
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Pour les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
?>
```

## Utilisation

1. Démarrez votre serveur PHP sur le port 2001
2. Lancez React avec `npm start`
3. Ouvrez http://localhost:3000
