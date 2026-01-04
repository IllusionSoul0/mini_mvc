// src/index.js
// Point d'entrée principal de l'application React

// Importe React et ReactDOM pour le rendu
import React from "react";
import ReactDOM from "react-dom/client";
// Importe les styles globaux
import "./index.css";
// Importe le composant principal App
import App from "./App";

// Crée le point de montage de React dans le DOM (élément #root du HTML)
const root = ReactDOM.createRoot(document.getElementById("root"));

// Rend l'application avec StrictMode pour détecter les problèmes de développement
root.render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);
