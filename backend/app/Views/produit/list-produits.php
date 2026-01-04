<!-- Liste des produits -->
<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2>Liste des produits</h2>
        <a href="/produits/create" style="padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; display: inline-block;">
            ➕ Ajouter un produit
        </a>
    </div>
    
    <?php if (empty($produits)): ?>
        <div style="text-align: center; padding: 40px; background-color: #f8f9fa; border-radius: 4px;">
            <p style="color: #666; font-size: 18px;">Aucun produit disponible.</p>
            <a href="/produits/create" style="color: #007bff; text-decoration: none;">Créer le premier produit</a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
            <?php foreach ($produits as $produit): ?>
                <div style="border: 1px solid #ddd; border-radius: 8px; padding: 15px; background-color: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Image du produit -->
                    <?php if (!empty($produit['image_url'])): ?>
                        <div style="margin-bottom: 15px; text-align: center;">
                            <img 
                                src="<?= htmlspecialchars($produit['image_url']) ?>" 
                                alt="<?= htmlspecialchars($produit['nom']) ?>" 
                                style="max-width: 100%; max-height: 200px; border-radius: 4px; object-fit: contain;"
                                onerror="this.style.display='none'"
                            >
                        </div>
                    <?php else: ?>
                        <div style="margin-bottom: 15px; text-align: center; padding: 40px; background-color: #f8f9fa; border-radius: 4px;">
                            <span style="color: #999; font-size: 14px;">Aucune image</span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Informations du produit -->
                    <h3 style="margin: 0 0 10px 0; color: #333; font-size: 20px;">
                        <?= htmlspecialchars($produit['nom']) ?>
                    </h3>
                    
                    <?php if (!empty($produit['description'])): ?>
                        <p style="margin: 0 0 15px 0; color: #666; font-size: 14px; line-height: 1.5;">
                            <?= htmlspecialchars($produit['description']) ?>
                        </p>
                    <?php endif; ?>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                        <div>
                            <div style="font-size: 24px; font-weight: bold; color: #007bff;">
                                <?= number_format((float)$produit['prix'], 2, ',', ' ') ?> €
                            </div>
                            <div style="font-size: 12px; color: #666; margin-top: 5px;">
                                Stock: <?= htmlspecialchars($produit['stock']) ?>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 10px; font-size: 12px; color: #999;">
                        ID: <?= htmlspecialchars($produit['id']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div style="margin-top: 30px; text-align: center;">
        <a href="/" style="color: #007bff; text-decoration: none;">← Retour à l'accueil</a>
    </div>
</div>

