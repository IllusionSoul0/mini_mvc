// src/App.js
// Composant principal qui gère la navigation interne sans dépendances externes

// Importe les hooks React nécessaires
import { useState, useEffect } from "react";
// Importe les pages composants
import Home from "./pages/Home";
import Users from "./pages/Users";
import Product from "./pages/Product";
import Cart from "./pages/Cart";
import Orders from "./pages/Orders";
import CreateProduct from "./pages/CreateProduct";
import "./App.css";

function App() {
  // Mappe le pathname à une route simple
  const pathToRoute = (p) => {
    if (p.startsWith("/users")) return "users";
    if (p.startsWith("/product")) return "product";
    if (p.startsWith("/create-product")) return "create-product";
    if (p.startsWith("/cart")) return "cart";
    if (p.startsWith("/orders")) return "orders";
    return "home";
  };

  // État pour tracker la route actuelle
  const [route, setRoute] = useState(pathToRoute(window.location.pathname));
  // État pour stocker l'ID du client depuis le localStorage
  const [clientId, setClientId] = useState(
    localStorage.getItem("client_id") || null
  );
  // État pour stocker le nom du client depuis le localStorage
  const [clientName, setClientName] = useState(
    localStorage.getItem("client_nom") || null
  );

  // Effet pour synchroniser le localStorage avec le stockage local
  useEffect(() => {
    const onStorage = () => {
      // Met à jour les états quand le localStorage change (dans un autre onglet)
      setClientId(localStorage.getItem("client_id") || null);
      setClientName(localStorage.getItem("client_nom") || null);
    };
    // Écoute les événements de changement de stockage
    window.addEventListener("storage", onStorage);
    window.addEventListener("authChange", onStorage);
    // Nettoie les écouteurs au démontage du composant
    return () => {
      window.removeEventListener("storage", onStorage);
      window.removeEventListener("authChange", onStorage);
    };
  }, []);

  // Effet pour gérer la navigation du navigateur (boutons back/forward)
  useEffect(() => {
    const onPop = () => setRoute(pathToRoute(window.location.pathname));
    window.addEventListener("popstate", onPop);
    // Nettoie l'écouteur au démontage du composant
    return () => window.removeEventListener("popstate", onPop);
  }, []);

  // Fonction pour naviguer vers une nouvelle page
  const navigate = (path) => {
    if (window.location.pathname !== path) {
      // Ajoute une nouvelle entrée dans l'historique du navigateur
      window.history.pushState({}, "", path);
      // Met à jour la route actuelle
      setRoute(pathToRoute(path));
    }
  };

  return (
    <div className="App">
      {/* Navigation principale */}
      <nav className="main-nav">
        <ul>
          <li>
            <button className="link-btn" onClick={() => navigate("/")}>
              Accueil
            </button>
          </li>
          <li>
            <button className="link-btn" onClick={() => navigate("/cart")}>
              Panier
            </button>
          </li>
          <li>
            <button className="link-btn" onClick={() => navigate("/orders")}>
              Mes commandes
            </button>
          </li>

          <li>
            <button
              className="link-btn"
              onClick={() => navigate("/create-product")}
              >
              Créer produit
            </button>
          </li>
          <li>
            {clientId ? (
              <button
                className="link-btn"
                onClick={() => {
                  // Supprime les données du client du localStorage
                  localStorage.removeItem("client_id");
                  localStorage.removeItem("client_nom");
                  setClientId(null);
                  setClientName(null);
                  // Notifie les autres onglets/fenêtres
                  window.dispatchEvent(new Event("authChange"));
                  navigate("/");
                }}
              >
                Déconnexion
              </button>
            ) : (
              <button className="link-btn" onClick={() => navigate("/users")}>
                Connexion / Inscription
              </button>
            )}
          </li>

          {/* Affiche le nom du client connecté */}
          <li className="user-info">
            {clientName ? `Bonjour, ${clientName}` : ""}
          </li>
        </ul>
      </nav>

      {/* Contenu principal - affiche la page selon la route active */}
      <main>
        {route === "home" && <Home />}
        {route === "users" && <Users />}
        {route === "product" && <Product />}
        {route === "create-product" && <CreateProduct />}
        {route === "cart" && <Cart />}
        {route === "orders" && <Orders />}
      </main>
    </div>
  );
}

export default App;
