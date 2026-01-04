// src/pages/Cart.js
// Page du panier pour gérer les articles ajoutés à la commande
import React, { useState, useEffect } from "react";
// Importe les fonctions API pour gérer les commandes
import {
  updateCommandeStatut,
  deleteLigneCommande,
  getCommandesByClient,
  updateCommande,
} from "../services/api";

// Composant Cart pour afficher et gérer le panier
function Cart() {
  // État pour stocker les articles du panier
  const [cart, setCart] = useState([]);
  // État pour l'adresse de livraison
  const [adresse, setAdresse] = useState("");
  // État pour afficher l'indicateur de chargement
  const [loading, setLoading] = useState(false);
  // État pour l'ID de la commande en attente
  const [commandeId, setCommandeId] = useState(null);

  useEffect(() => {
    const loadCart = async () => {
      const client_id = localStorage.getItem("client_id");
      if (!client_id) return setCart([]);
      try {
        const commandes = await getCommandesByClient(client_id);
        const current = Array.isArray(commandes)
          ? commandes.find((c) => c.statut === "en attente")
          : null;
        if (current && Array.isArray(current.lignes)) {
          setCart(
            current.lignes.map((l) => ({
              id: l.id_produit || l.id_produit,
              nom: l.produit_nom || l.nom,
              prix: l.prix_unitaire || l.prix_unitaire,
              quantite: l.quantite,
            }))
          );
          setCommandeId(current.id);
        } else {
          setCart([]);
          setCommandeId(null);
        }
      } catch (e) {
        console.warn("Erreur chargement commande en attente", e);
        setCart([]);
        setCommandeId(null);
      }
    };

    loadCart();
  }, []);

  const openProduct = (id) => {
    window.history.pushState({}, "", `/product?id=${id}`);
    window.dispatchEvent(new PopStateEvent("popstate"));
  };

  const remove = async (index) => {
    if (!commandeId) return;
    try {
      await deleteLigneCommande({
        id_commande: commandeId,
        produit: cart[index].nom,
      });
      // Refresh cart from server
      const client_id = localStorage.getItem("client_id");
      const commandes = await getCommandesByClient(client_id);
      const current = Array.isArray(commandes)
        ? commandes.find((c) => c.statut === "en attente")
        : null;
      if (current && Array.isArray(current.lignes)) {
        setCart(
          current.lignes.map((l) => ({
            id: l.id_produit || l.id_produit,
            nom: l.produit_nom || l.nom,
            prix: l.prix_unitaire || l.prix_unitaire,
            quantite: l.quantite,
          }))
        );
        setCommandeId(current.id);
      } else {
        setCart([]);
        setCommandeId(null);
      }

      // If cart empty, mark commande annulée on server
      if (!current || current.lignes.length === 0) {
        try {
          await updateCommandeStatut(commandeId, "annulée");
        } catch (e) {
          console.warn("Erreur annulation commande", e);
        } finally {
          setCommandeId(null);
        }
      }
    } catch (e) {
      console.warn("Erreur suppression ligne", e);
    }
  };

  const total = cart.reduce((s, it) => s + it.prix * it.quantite, 0).toFixed(2);

  const handleCheckout = async () => {
    const client_id = localStorage.getItem("client_id");
    const commandeIdLocal = commandeId;

    if (!client_id) return alert("Veuillez vous connecter");
    if (cart.length === 0) return alert("Panier vide");
    if (!commandeIdLocal) return alert("Aucune commande active");
    if (!adresse || adresse.trim() === "")
      return alert("Veuillez renseigner une adresse de livraison");

    setLoading(true);
    try {
      // Envoie l'adresse de livraison au serveur avant de valider le paiement
      await updateCommande({
        id_commande: commandeIdLocal,
        adresse_livraison: adresse,
      });
      await updateCommandeStatut(commandeIdLocal, "payée");

      setCart([]);
      setCommandeId(null);

      window.history.pushState({}, "", "/orders");
      window.dispatchEvent(new PopStateEvent("popstate"));
    } catch (err) {
      console.error(err);
      alert(err.message || "Erreur lors du paiement");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="cart">
      <header className="App-header">
        <h1>Mon Panier</h1>
      </header>

      <div className="container">
        {cart.length === 0 && (
          <p className="empty-text">Votre panier est vide.</p>
        )}

        {cart.length > 0 && (
          <div style={{ display: "grid", gap: "1rem" }}>
            {cart.map((it, idx) => (
              <div
                key={it.id}
                style={{
                  background: "white",
                  padding: "1rem",
                  borderRadius: 6,
                }}
              >
                <h3>{it.nom}</h3>
                <p>{it.prix} €</p>
                <div>
                  <p>Quantité: {it.quantite}</p>
                </div>
                <div className="cart-button">
                  <button onClick={() => openProduct(it.id)}>Voir</button>
                  <button
                    onClick={() => remove(idx)}
                    style={{ marginLeft: "1rem" }}
                  >
                    Supprimer
                  </button>
                  <br />
                </div>
              </div>
            ))}

            <div
              style={{ background: "white", padding: "1rem", borderRadius: 6 }}
            >
              <h3>Total: {total} €</h3>

              <label>Adresse de livraison</label>
              <textarea
                value={adresse}
                onChange={(e) => setAdresse(e.target.value)}
                rows={3}
                style={{ width: "100%", marginTop: 8 }}
              />

              <div style={{ marginTop: 12 }}>
                <button
                  onClick={handleCheckout}
                  disabled={loading}
                  style={{ marginRight: 8 }}
                >
                  {loading ? "En cours..." : "Commander"}
                </button>
                <button onClick={() => setAdresse("")}>Effacer</button>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

export default Cart;
