// src/pages/Product.js
// Page de détail d'un produit avec option d'ajout au panier
import React, { useEffect, useState } from "react";
// Importe les fonctions API pour les produits et commandes
import {
  getProduit,
  createCommande,
  updateCommande,
  getCommandesByClient,
} from "../services/api";
import "../App.css";

// Composant Product pour afficher les détails d'un produit
function Product() {
  // État pour stocker les données du produit
  const [product, setProduct] = useState(null);
  // État pour afficher l'indicateur de chargement
  const [loading, setLoading] = useState(true);
  // État pour stocker les messages d'erreur
  const [error, setError] = useState(null);
  // État pour la quantité à ajouter au panier
  const [qty, setQty] = useState(1);

  // Effet qui se déclenche au montage du composant
  useEffect(() => {
    // Récupère l'ID du produit depuis les paramètres d'URL
    const params = new URLSearchParams(window.location.search);
    const id = params.get("id");
    if (!id) {
      setError("Produit non spécifié");
      setLoading(false);
      return;
    }

    // Charge les données du produit
    getProduit(id)
      .then((p) => setProduct(p))
      .catch(() => setError("Impossible de charger le produit"))
      .finally(() => setLoading(false));
  }, []);

  // Fonction pour ajouter le produit au panier
  const addToCart = async () => {
    // Vérifie que l'utilisateur est connecté
    const client_id = localStorage.getItem("client_id");
    if (!client_id) {
      alert("Veuillez vous connecter");
      return;
    }
    // Vérifie que le produit est disponible
    if (product && product.actif === false) {
      alert("Produit indisponible");
      return;
    }
    const available = Number(product.stock || 0);
    const desired = Number(qty || 0);
    if (desired <= 0) return alert("Quantité invalide");
    if (available <= 0) return alert("Produit en rupture de stock");
    if (desired > available)
      return alert(
        `Quantité demandée (${desired}) supérieure au stock disponible (${available})`
      );
    try {
      // Get current 'en attente' commande for this client
      const commandes = await getCommandesByClient(client_id);
      const current = Array.isArray(commandes)
        ? commandes.find((c) => c.statut === "en attente")
        : null;

      if (!current) {
        // Create a new commande with this single item
        await createCommande({
          id_client: client_id,
          adresse_livraison: "",
          items: [{ id_produit: product.id, quantite: qty }],
        });
      } else {
        // Update existing commande by adding this item/quantity
        await updateCommande({
          id_commande: current.id,
          items: [{ id_produit: product.id, quantite: qty }],
        });
      }

      alert("Produit ajouté au panier");
    } catch (err) {
      console.error(err);
      alert(err.message || "Erreur lors de l'ajout au panier");
    }
  };

  if (loading) return <p>Chargement...</p>;
  if (error) return <div className="error-message">⚠️ {error}</div>;
  if (!product) return null;

  return (
    <div className="product">
      <header className="App-header">
        <h1>{product.nom}</h1>
      </header>

      <div className="container">
        <div className="user-card product-card">
          <div>
            <h3>{product.nom}</h3>
            <p>{product.description}</p>
            <p>
              <strong>{product.prix} €</strong>
            </p>

            <div
              style={{
                display: "flex",
                gap: "0.5rem",
                alignItems: "center",
                marginTop: "1rem",
              }}
            >
              <label style={{ minWidth: 70 }}>Quantité</label>
              <input
                type="number"
                value={qty}
                min={1}
                max={product.stock || undefined}
                onChange={(e) => {
                  const v = parseInt(e.target.value, 10);
                  if (Number.isNaN(v)) return setQty(1);
                  const max = Number(product.stock || 0) || 1;
                  setQty(Math.max(1, Math.min(v, max)));
                }}
                disabled={
                  Number(product.stock || 0) <= 0 || product.actif === false
                }
              />
            </div>
            <div style={{ marginTop: 8 }}>
              {Number(product.stock || 0) > 0 ? (
                <small>En stock: {product.stock}</small>
              ) : (
                <small style={{ color: "red" }}>Rupture de stock</small>
              )}
            </div>
            <br />
            {product.image && (
              <img
                src={product.image}
                alt={product.nom}
                style={{ maxWidth: "50%", height: "auto", borderRadius: "8px" }}
              />
            )}
          </div>

          <div style={{ marginTop: "1rem", display: "flex", gap: "0.5rem" }}>
            <button
              onClick={addToCart}
              disabled={
                Number(product.stock || 0) <= 0 || product.actif === false
              }
              style={
                product.actif === false
                  ? { opacity: 0.6, cursor: "not-allowed" }
                  : {}
              }
            >
              {product.actif === false ? "Indisponible" : "Ajouter au panier"}
            </button>
            <button
              onClick={() => window.history.back()}
              style={{ background: "#fff", color: "#333" }}
            >
              Retour
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Product;
