// src/pages/Home.js
// Page d'accueil affichant le catalogue des produits
import React, { useEffect, useState } from "react";
// Importe les fonctions API pour récupérer les données
import { getProduits, getCategories } from "../services/api";
import "../App.css";

// Composant Home pour afficher la liste des produits
function Home() {
  // État pour stocker la liste des produits
  const [produits, setProduits] = useState([]);
  // État pour stocker les catégories
  const [categories, setCategories] = useState([]);
  // État pour la catégorie actuellement sélectionnée
  const [selectedCat, setSelectedCat] = useState(null);
  // État pour afficher l'indicateur de chargement
  const [loading, setLoading] = useState(true);
  // État pour stocker les messages d'erreur
  const [error, setError] = useState(null);

  // Effet qui se déclenche au montage du composant
  useEffect(() => {
    const load = async () => {
      try {
        // Affiche l'indicateur de chargement
        setLoading(true);
        // Récupère les produits et les catégories en parallèle
        const [data, cats] = await Promise.all([
          getProduits(),
          getCategories(),
        ]);
        // Stocke les données récupérées
        setProduits(data || []);
        setCategories(cats || []);
        setSelectedCat(null);
      } catch (err) {
        // Affiche un message d'erreur en cas de problème
        setError("Impossible de charger les produits.");
      } finally {
        // Masque l'indicateur de chargement
        setLoading(false);
      }
    };
    // Appelle la fonction de chargement
    load();
  }, []);

  // Fonction pour ouvrir la page de détail d'un produit
  const openProduct = (id) => {
    // Ajoute une nouvelle entrée dans l'historique du navigateur
    window.history.pushState({}, "", `/product?id=${id}`);
    // Déclenche un événement popstate pour la navigation
    window.dispatchEvent(new PopStateEvent("popstate"));
  };

  return (
    <div className="home">
      {/* En-tête de la page */}
      <header className="App-header">
        <h1>Catalogue</h1>
        <p>Parcourez nos produits disponibles</p>
      </header>

      <div className="container">
        {/* Affiche l'indicateur de chargement */}
        {loading && <p>Chargement...</p>}
        {/* Affiche les messages d'erreur */}
        {error && <div className="error-message">⚠️ {error}</div>}

        {/* Affiche un message si aucun produit n'est disponible */}
        {!loading && produits.length === 0 && <p>Aucun produit disponible.</p>}

        {/* Boutons de filtrage par catégorie */}
        <div style={{ marginBottom: 12 }}>
          <div style={{ display: "flex", gap: 8, flexWrap: "wrap" }}>
            {/* Bouton pour afficher tous les produits */}
            <button
              onClick={() => setSelectedCat(null)}
              style={{
                padding: "6px 10px",
                background: selectedCat === null ? "#333" : "#fff",
                color: selectedCat === null ? "#fff" : "#333",
                borderRadius: 6,
                border: "1px solid #ccc",
                cursor: "pointer",
              }}
            >
              All
            </button>
            {/* Boutons pour chaque catégorie */}
            {categories.map((c) => (
              <button
                key={c.id}
                onClick={() => setSelectedCat(c.id)}
                style={{
                  padding: "6px 10px",
                  background: selectedCat === c.id ? "#333" : "#fff",
                  color: selectedCat === c.id ? "#fff" : "#333",
                  borderRadius: 6,
                  border: "1px solid #ccc",
                  cursor: "pointer",
                }}
              >
                {c.nom}
              </button>
            ))}
          </div>
        </div>

        {/* Affiche la liste des produits filtrés */}
        <div className="users-grid">
          {produits
            .filter((p) =>
              selectedCat === null
                ? true
                : Number(p.id_categorie) === Number(selectedCat)
            )
            .map((p) => (
              <div key={p.id} className="user-card">
                <h3>{p.nom}</h3>
                <p>{p.description}</p>
                <p>
                  <strong>{p.prix} €</strong>
                </p>
                <br />
                <button onClick={() => openProduct(p.id)}>Voir</button>
              </div>
            ))}
        </div>
      </div>
    </div>
  );
}

export default Home;
