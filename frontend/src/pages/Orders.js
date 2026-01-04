// src/pages/Orders.js
// Page d'affichage de l'historique des commandes
import React, { useEffect, useState } from "react";
// Importe la fonction API pour récupérer les commandes
import { getCommandesByClient } from "../services/api";

// Composant Orders pour afficher les commandes de l'utilisateur
function Orders() {
  // État pour stocker la liste des commandes
  const [orders, setOrders] = useState([]);
  // État pour afficher l'indicateur de chargement
  const [loading, setLoading] = useState(true);
  // État pour stocker les messages d'erreur
  const [error, setError] = useState(null);

  // Effet qui se déclenche au montage du composant
  useEffect(() => {
    const load = async () => {
      // Récupère l'ID du client depuis le localStorage
      const client_id = localStorage.getItem("client_id");
      if (!client_id) {
        setError("Veuillez vous connecter.");
        setLoading(false);
        return;
      }

      try {
        // Récupère les commandes du client
        const data = await getCommandesByClient(client_id);
        setOrders(data || []);
      } catch (err) {
        // Affiche un message d'erreur en cas de problème
        setError("Impossible de charger les commandes");
      } finally {
        // Masque l'indicateur de chargement
        setLoading(false);
      }
    };
    load();
  }, []);

  // Affiche l'indicateur de chargement
  if (loading) return <p>Chargement...</p>;
  // Affiche le message d'erreur
  if (error) return <div className="error-message">⚠️ {error}</div>;

  return (
    <div className="order">
      {/* En-tête de la page */}
      <header className="App-header">
        <h1>Commandes</h1>
        <p>Historique de vos commandes</p>
      </header>

      {orders.length === 0 && <p className="empty-text"> Aucune commande.</p>}

      <div className="orders-list">
        {orders.map((o, index) => (
          <div key={o.id} className="order-card">
            <div className="order-header">
              <h3>Commande #{orders.length - index}</h3>
              <div className="order-meta">
                <span
                  className={`badge status-${o.statut.replace(/\s+/g, "-")}`}
                >
                  {o.statut}
                </span>
                <span className="order-amount">{o.montant} €</span>
              </div>
            </div>

            {/* N'affiche l'adresse que si la commande est payée/expédiée/livrée */}
            {["payée", "expédiée", "livrée"].includes(o.statut) ? (
              <p className="order-address">Adresse: {o.adresse_livraison}</p>
            ) : null}

            <div className="order-lines">
              {o.lignes &&
                o.lignes.map((l) => (
                  <div key={l.id} className="order-line">
                    <strong>{l.produit_nom}</strong> x {l.quantite} —{" "}
                    {l.prix_total} €
                  </div>
                ))}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

export default Orders;
