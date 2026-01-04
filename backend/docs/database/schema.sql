BEGIN;


CREATE TABLE IF NOT EXISTS public.administrateur
(
    id serial NOT NULL,
    nom character varying(30) COLLATE pg_catalog."default",
    email character varying(100) COLLATE pg_catalog."default",
    mdp character varying(50) COLLATE pg_catalog."default",
    role character varying(20) COLLATE pg_catalog."default",
    CONSTRAINT administrateur_pkey PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS public.categorie
(
    id serial NOT NULL,
    nom character varying(30) COLLATE pg_catalog."default",
    description text COLLATE pg_catalog."default",
    image character varying(100) COLLATE pg_catalog."default",
    CONSTRAINT categorie_pkey PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS public.client
(
    id serial NOT NULL,
    nom character varying(30) COLLATE pg_catalog."default",
    prenom character varying(30) COLLATE pg_catalog."default",
    adresse character varying(200) COLLATE pg_catalog."default",
    email character varying(100) COLLATE pg_catalog."default",
    mdp character varying(50) COLLATE pg_catalog."default",
    CONSTRAINT client_pkey PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS public.commande
(
    id serial NOT NULL,
    id_client integer,
    statut character varying(10) COLLATE pg_catalog."default",
    montant numeric(7, 2),
    adresse_livraison character varying(200) COLLATE pg_catalog."default",
    CONSTRAINT commande_pkey PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS public.ligne_commande
(
    id serial NOT NULL,
    id_commande integer,
    id_produit integer,
    quantite integer,
    prix_unitaire numeric(7, 2),
    prix_total numeric(7, 2),
    CONSTRAINT ligne_commande_pkey PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS public.produit
(
    id serial NOT NULL,
    nom character varying(30) COLLATE pg_catalog."default",
    description text COLLATE pg_catalog."default",
    prix numeric(7, 2),
    image character varying(500) COLLATE pg_catalog."default",
    stock integer,
    actif boolean,
    id_categorie integer,
    CONSTRAINT produit_pkey PRIMARY KEY (id)
);

ALTER TABLE IF EXISTS public.commande
    ADD CONSTRAINT commande_id_client_fkey FOREIGN KEY (id_client)
    REFERENCES public.client (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;


ALTER TABLE IF EXISTS public.ligne_commande
    ADD CONSTRAINT ligne_commande_id_commande_fkey FOREIGN KEY (id_commande)
    REFERENCES public.commande (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;


ALTER TABLE IF EXISTS public.ligne_commande
    ADD CONSTRAINT ligne_commande_id_produit_fkey FOREIGN KEY (id_produit)
    REFERENCES public.produit (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

ALTER TABLE IF EXISTS public.produit
    ADD CONSTRAINT produit_id_categorie_fkey FOREIGN KEY (id_categorie)
    REFERENCES public.categorie (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

END;