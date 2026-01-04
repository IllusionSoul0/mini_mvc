// src/pages/CreateProduct.js
// Page pour créer un nouveau produit
import React, { useEffect, useState } from "react";
// Importe les fonctions API pour les catégories et produits
import { getCategories, createProduit } from "../services/api";
import "../App.css";

// Composant CreateProduct pour créer un nouveau produit
function CreateProduct() {
  // États pour les champs du formulaire
  const [nom, setNom] = useState("");
  const [description, setDescription] = useState("");
  const [prix, setPrix] = useState(0);
  const [stock, setStock] = useState(0);
  const [image, setImage] = useState("");
  const [idCategorie, setIdCategorie] = useState("");
  const [actif, setActif] = useState(true);
  // État pour stocker les catégories disponibles
  const [categories, setCategories] = useState([]);
  // État pour afficher l'indicateur de chargement
  const [loading, setLoading] = useState(false);
  // État pour stocker les messages d'erreur
  const [error, setError] = useState(null);

  // Effet pour charger les catégories au montage du composant
  useEffect(() => {
    const load = async () => {
      try {
        // Récupère la liste des catégories
        const cats = await getCategories();
        setCategories(cats || []);
        // Sélectionne la première catégorie par défaut
        if (cats && cats.length > 0) setIdCategorie(cats[0].id);
      } catch (e) {
        console.warn("Impossible de charger les catégories", e);
      }
    };
    load();
  }, []);

  // Fonction pour traiter la soumission du formulaire
  const handleSubmit = async (e) => {
    e.preventDefault();
    // Validation côté client pour correspondre aux contraintes de la base de données
    const MAX_NOM = 30;
    const MAX_IMAGE = 500;

    // Valide les champs obligatoires
    if (!nom || prix === "" || stock === "")
      return alert("Nom, prix et stock requis");
    if (nom.length > MAX_NOM)
      return alert(`Nom trop long (max ${MAX_NOM} caractères)`);
    if (image && image.length > MAX_IMAGE)
      return alert(`URL d'image trop longue (max ${MAX_IMAGE} caractères)`);
    if (Number(prix) < 0) return alert("Le prix doit être positif ou nul");
    if (!Number.isInteger(Number(stock)) || Number(stock) < 0)
      return alert("Le stock doit être un entier positif ou nul");

    const payload = {
      nom,
      description,
      prix: Number(prix),
      stock: Number(stock),
      image,
      actif: actif,
      id_categorie: idCategorie,
    };

    try {
      setLoading(true);
      setError(null);
      await createProduit(payload);
      setNom("");
      setDescription("");
      setPrix(0);
      setStock(0);
      setImage("");
      setActif(true);
      alert("Produit créé avec succès");
    } catch (err) {
      console.error(err);
      setError(err.message || "Erreur création produit");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="create-product-page">
      <header className="App-header">
        <h1>Créer un produit</h1>
      </header>

      <div className="container">
        <div style={{ background: "white", padding: 16, borderRadius: 6 }}>
          <form onSubmit={handleSubmit} style={{ display: "grid", gap: 12 }}>
            <div className="form-group">
              <label>Nom</label>
              <input
                value={nom}
                onChange={(e) => setNom(e.target.value)}
                disabled={loading}
              />
            </div>

            <div className="form-group">
              <label>Description</label>
              <textarea
                value={description}
                onChange={(e) => setDescription(e.target.value)}
                rows={3}
                disabled={loading}
              />
            </div>

            <div style={{ display: "flex", gap: 8 }}>
              <div className="form-group" style={{ flex: 1 }}>
                <label>Prix (€)</label>
                <input
                  type="number"
                  step="0.01"
                  value={prix}
                  onChange={(e) => setPrix(e.target.value)}
                  disabled={loading}
                />
              </div>

              <div className="form-group" style={{ flex: 1 }}>
                <label>Stock</label>
                <input
                  type="number"
                  value={stock}
                  onChange={(e) => setStock(e.target.value)}
                  disabled={loading}
                />
              </div>
            </div>

            <div className="form-group">
              <label>Image (URL)</label>
              <input
                value={image}
                onChange={(e) => setImage(e.target.value)}
                disabled={loading}
              />
            </div>

            <div style={{ display: "flex", gap: 8, alignItems: "center" }}>
              <div style={{ flex: 1 }}>
                <label>Catégorie</label>
                <select
                  value={idCategorie}
                  onChange={(e) => setIdCategorie(e.target.value)}
                  disabled={loading}
                >
                  {categories.map((c) => (
                    <option key={c.id} value={c.id}>
                      {c.nom}
                    </option>
                  ))}
                </select>
              </div>

              <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                <label>Actif</label>
                <input
                  type="checkbox"
                  checked={actif}
                  onChange={(e) => setActif(e.target.checked)}
                  disabled={loading}
                />
              </div>
            </div>

            {error && <div className="error-message">⚠️ {error}</div>}

            <div>
              <button type="submit" disabled={loading}>
                {loading ? "Création..." : "Créer le produit"}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}

export default CreateProduct;
