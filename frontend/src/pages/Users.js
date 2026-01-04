// src/pages/Users.js
// Page de connexion et inscription des utilisateurs
import React, { useState, useEffect } from "react";
// Importe les fonctions API pour gérer les utilisateurs
import { getUsers, createUser, login } from "../services/api";
import "../App.css";

// Composant Users pour l'authentification et la gestion des utilisateurs
function Users() {
  // État pour stocker la liste des utilisateurs
  const [users, setUsers] = useState([]);
  // États pour le formulaire d'inscription
  const [nom, setNom] = useState("");
  const [email, setEmail] = useState("");
  const [mdp, setMdp] = useState("");
  // États pour le formulaire de connexion
  const [loginEmail, setLoginEmail] = useState("");
  const [loginMdp, setLoginMdp] = useState("");
  // État pour afficher l'indicateur de chargement
  const [loading, setLoading] = useState(false);
  // État pour stocker les messages d'erreur
  const [error, setError] = useState(null);

  // Effet pour charger la liste des utilisateurs au montage du composant
  useEffect(() => {
    chargerUtilisateurs();
  }, []);

  // Fonction pour charger la liste des utilisateurs
  const chargerUtilisateurs = async () => {
    try {
      setLoading(true);
      setError(null);
      // Récupère la liste des utilisateurs du serveur
      const data = await getUsers();
      setUsers(data);
    } catch (err) {
      // Affiche un message d'erreur si le serveur n'est pas disponible
      setError(
        "Impossible de charger les utilisateurs. Le serveur PHP est-il démarré ?"
      );
    } finally {
      setLoading(false);
    }
  };

  // Fonction pour traiter la connexion
  const handleLogin = async (e) => {
    e.preventDefault();
    try {
      setLoading(true);
      // Authentifie l'utilisateur
      const res = await login({ email: loginEmail, mdp: loginMdp });
      // Stocke l'ID du client dans le localStorage
      localStorage.setItem("client_id", res.client.id);
      localStorage.setItem("client_nom", res.client.nom);
      // Notify app in same window/tab that auth changed
      window.dispatchEvent(new Event("authChange"));
      window.history.pushState({}, "", "/orders");
      window.dispatchEvent(new PopStateEvent("popstate"));
      alert("Connecté");
    } catch (err) {
      alert("Erreur lors de la connexion: " + err.message);
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!nom || !email || !mdp) {
      alert("Veuillez remplir tous les champs (incl. mot de passe)");
      return;
    }

    try {
      setLoading(true);
      setError(null);

      const newUser = { nom: nom, email: email, mdp: mdp };
      const res = await createUser(newUser);
      // Auto-login after signup: store client id & name and notify app
      localStorage.setItem("client_id", res.client.id);
      localStorage.setItem("client_nom", res.client.nom);
      window.dispatchEvent(new Event("authChange"));
      window.history.pushState({}, "", "/orders");
      window.dispatchEvent(new PopStateEvent("popstate"));
      setNom("");
      setEmail("");
      setMdp("");
      await chargerUtilisateurs();
      alert("Utilisateur créé avec succès !");
    } catch (err) {
      setError("Erreur lors de la création de l'utilisateur");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="users-page">
      <header className="App-header">
        <h1>Gestion des Utilisateurs</h1>
      </header>

      <div className="container">
        <div className="form-section" style={{ display: "grid", gap: "1rem" }}>
          <form
            onSubmit={handleLogin}
            style={{ background: "white", padding: "1rem", borderRadius: 6 }}
          >
            <h2>Connexion</h2>
            <div className="form-group">
              <label htmlFor="loginEmail">Email :</label>
              <input
                type="email"
                id="loginEmail"
                value={loginEmail}
                onChange={(e) => setLoginEmail(e.target.value)}
                placeholder="john@example.com"
                disabled={loading}
              />
            </div>
            <div className="form-group">
              <label htmlFor="loginMdp">Mot de passe :</label>
              <input
                type="password"
                id="loginMdp"
                value={loginMdp}
                onChange={(e) => setLoginMdp(e.target.value)}
                placeholder="Mot de passe"
                disabled={loading}
              />
            </div>
            <button type="submit" disabled={loading}>
              {loading ? "Connexion..." : "Se connecter"}
            </button>
          </form>

          <div
            style={{ background: "white", padding: "1rem", borderRadius: 6 }}
          >
            <h2>Créer un nouvel utilisateur</h2>
            <form onSubmit={handleSubmit}>
              <div className="form-group">
                <label htmlFor="nom">Nom :</label>
                <input
                  type="text"
                  id="nom"
                  value={nom}
                  onChange={(e) => setNom(e.target.value)}
                  placeholder="John Doe"
                  disabled={loading}
                />
              </div>

              <div className="form-group">
                <label htmlFor="email">Email :</label>
                <input
                  type="email"
                  id="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="john@example.com"
                  disabled={loading}
                />
              </div>

              <div className="form-group">
                <label htmlFor="mdp">Mot de passe :</label>
                <input
                  type="password"
                  id="mdp"
                  value={mdp}
                  onChange={(e) => setMdp(e.target.value)}
                  placeholder="Mot de passe"
                  disabled={loading}
                />
              </div>

              <button type="submit" disabled={loading}>
                {loading ? "Création..." : "Créer l'utilisateur"}
              </button>
            </form>
          </div>
        </div>

        {error && <div className="error-message">⚠️ {error}</div>}

        <div className="users-section">
          <h2>Liste des utilisateurs</h2>
          <button onClick={chargerUtilisateurs} disabled={loading}>
            🔄 Actualiser
          </button>

          {loading && <p>Chargement...</p>}

          {!loading && users.length === 0 && <p>Aucun utilisateur trouvé.</p>}

          {!loading && users.length > 0 && (
            <div className="users-grid">
              {users.map((user) => (
                <div key={user.id || user.email} className="user-card">
                  <h3>{user.nom}</h3>
                  <p>{user.email}</p>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

export default Users;
