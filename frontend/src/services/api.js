// src/services/api.js
// Ce fichier gère toute la communication avec le backend PHP

// URL de base de l'API backend
const API_BASE_URL = "http://localhost:2001";

// =====================
// Clients / Utilisateurs
// =====================
// Récupère la liste de tous les clients
export const getUsers = async () => {
  const response = await fetch(`${API_BASE_URL}/clients/json`);
  if (!response.ok) throw new Error("Erreur getUsers");
  return response.json();
};

// Crée un nouvel utilisateur (client)
export const createUser = async (userData) => {
  const response = await fetch(`${API_BASE_URL}/clients`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(userData),
  });
  if (!response.ok) {
    const err = await response.json().catch(() => ({}));
    throw new Error(err.error || "Erreur lors de la création de l'utilisateur");
  }
  return response.json();
};

// =====================
// Produits
// =====================
// Récupère la liste de tous les produits
export const getProduits = async () => {
  const res = await fetch(`${API_BASE_URL}/produits/json`);
  if (!res.ok) throw new Error("Erreur lors de la récupération des produits");
  return res.json();
};

// Récupère la liste de toutes les catégories
export const getCategories = async () => {
  const res = await fetch(`${API_BASE_URL}/categories/json`);
  if (!res.ok) throw new Error("Erreur lors de la récupération des catégories");
  return res.json();
};

// Crée un nouveau produit
export const createProduit = async (produit) => {
  const res = await fetch(`${API_BASE_URL}/produits`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(produit),
  });
  // Essaie de parser la réponse JSON; si l'analyse échoue, retourne le texte pour les meilleurs erreurs
  if (!res.ok) {
    const bodyText = await res.text().catch(() => "");
    try {
      const err = JSON.parse(bodyText || "{}");
      throw new Error(
        err.error || bodyText || "Erreur lors de la création du produit"
      );
    } catch (_e) {
      throw new Error(bodyText || "Erreur lors de la création du produit");
    }
  }

  const text = await res.text();
  try {
    return JSON.parse(text || "{}");
  } catch (e) {
    // Le serveur a retourné un corps non-JSON (probablement un avertissement PHP/HTML) — retourne-le à l'appelant
    throw new Error(text || "Réponse invalide du serveur");
  }
};

// Récupère les détails d'un produit par son ID
export const getProduit = async (id) => {
  const res = await fetch(`${API_BASE_URL}/produits/json?id=${id}`);
  if (!res.ok) throw new Error("Produit introuvable");
  return res.json();
};

// =====================
// Authentification
// =====================
// Authentifie un utilisateur avec email et mot de passe
export const login = async ({ email, mdp }) => {
  const res = await fetch(`${API_BASE_URL}/clients/login`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ email, mdp }),
  });
  if (!res.ok) {
    const err = await res.json().catch(() => ({}));
    throw new Error(err.error || "Échec de la connexion");
  }
  return res.json();
};

export const logout = async () => {
  await fetch(`${API_BASE_URL}/clients/logout`, { method: "POST" });
};

// Commandes
export const deleteLigneCommande = async (ligne) => {
  const res = await fetch(`${API_BASE_URL}/lignecommande`, {
    method: "DELETE",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(ligne),
  });
  if (!res.ok) {
    const err = await res.json().catch(() => ({}));
    throw new Error(err.error || "Erreur lors de la suppresion du produit");
  }
  return res.json();
};

export const createCommande = async (commande) => {
  const res = await fetch(`${API_BASE_URL}/commandes`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(commande),
  });
  if (!res.ok) {
    const err = await res.json().catch(() => ({}));
    throw new Error(err.error || "Erreur lors de la création de la commande");
  }
  return res.json();
};

export const updateCommande = async (commande) => {
  console.log(commande)
  const res = await fetch(`${API_BASE_URL}/commandes`, {
    method: "PUT",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(commande),
  });
  if (!res.ok) {
    const err = await res.json().catch(() => ({}));
    throw new Error(
      err.error || "Erreur lors de la mise à jour de la commande"
    );
  }
  return res.json();
};

export const getCommandesByClient = async (id_client) => {
  const res = await fetch(
    `${API_BASE_URL}/commandes/json?id_client=${id_client}`
  );
  if (!res.ok) throw new Error("Erreur lors de la récupération des commandes");
  return res.json();
};

export const updateCommandeStatut = async (id, statut) => {
  const res = await fetch(`${API_BASE_URL}/commandes/statut`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id, statut }),
  });
  if (!res.ok) {
    const err = await res.json().catch(() => ({}));
    throw new Error(err.error || "Erreur lors de la mise à jour du statut");
  }
  return res.json();
};
