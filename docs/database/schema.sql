CREATE TABLE categorie (id_categorie SERIAL PRIMARY KEY, nom VARCHAR(30), description TEXT, image VARCHAR(100));

CREATE TABLE produit (id_produit SERIAL PRIMARY KEY, nom VARCHAR(30), description TEXT, prix NUMERIC(7,2), image VARCHAR(100), stock INT, actif BOOL, id_categorie INT references categorie(id_categorie));

CREATE TABLE client (id_client SERIAL PRIMARY KEY, nom VARCHAR(30), prenom VARCHAR(30), adresse VARCHAR(200), email VARCHAR(100), mdp VARCHAR(50));

CREATE TABLE commande (id_commande SERIAL PRIMARY KEY, id_client INT references client(id_client), status VARCHAR(10), montant NUMERIC(7,2), adresse_livraison VARCHAR(200));

CREATE TABLE ligne_commande (id_ligne_commande SERIAL PRIMARY KEY, id_commande INT references commande(id_commande), id_produit INT references produit(id_produit), quantite INT, prix_unitaire NUMERIC(7,2), prix_total NUMERIC(7,2));

CREATE TABLE administrateur (id_administrateur SERIAL PRIMARY KEY, nom VARCHAR(30), email VARCHAR(100), mdp VARCHAR(50), role VARCHAR(20));