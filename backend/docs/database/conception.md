1. Il vaut mieux stocker le prix unitaire dans la table ligne de commande, car le prix de l'object peut changer mais le prix de la commande sera toujours le meme.

2. Pour gérer les suppressions, par exemple pour la suppression d'un client on pourrai utiliser la Mise a Null pour toujours avoir un registre des commandes complets mais sans les données du client.

3. La gestion des stocks se fera du coté back-end avec des boucles conditionnelles sur la quantité restante en stock pour empecher le client de commander plus que possible, de plus le stock ne doit etre décrémenté qu'après le payement car si il est décrémenté à la validation et que l'utilisateur ne paye pas au final ça posera problème.

4. J'ai rajouté une catégorie id dans chaque table, ça permet de simplifier pour plus tard la manipulation de donnée du coté back-end ainsi que les jointures entre les tables.

5. L'unicité du numéro de commande est assuré grace à son ID dans la base sql.

6. On pourrai par exemple rajouter une table images_reference pour ajouter plusieurs images par produit ainsi qu'une table pour gérer les avis (contenant : numéro client, avis, date).

## Instruction d'installation :

- Dans PgAdmin, créer une nouvelle database nommée mini_mvc (utiliser postgresql pour le schema.sql).
- Exécuter le code de schema.sql puis de fixtures.sql dans la database mini_mvc.
