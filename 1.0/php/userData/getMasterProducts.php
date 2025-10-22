<?php
function getProductIcon($productName) {
    $icons = [
        'салфетка' => '🧵',
        'вышив' => '🧵',
        'деревянн' => '🔨',
        'доска' => '🔨',
        'резьб' => '🔨',
        'варежк' => '🧶',
        'вязан' => '🧶',
        'шерст' => '🧶',
        'кружка' => '⚱️',
        'керамик' => '⚱️',
        'глин' => '⚱️',
        'колье' => '💎',
        'бижутери' => '💎',
        'камен' => '💎',
        'сумка' => '🪡',
        'льнян' => '🪡',
        'шить' => '🪡'
    ];
    
    $productNameLower = mb_strtolower($productName, 'UTF-8');
    
    foreach ($icons as $keyword => $icon) {
        if (mb_strpos($productNameLower, $keyword, 0, 'UTF-8') !== false) {
            return $icon;
        }
    }
    
    return '📦';
}

if (!empty($masterProducts)): 
    foreach ($masterProducts as $product): 
        $icon = getProductIcon($product['productName']);
        $price = number_format($product['price'], 2, '.', ' ') . ' руб.';
?>
        <div class="product-card">
            <div class="product-image"><?php echo $icon; ?></div>
            <div class="product-info">
                <h3 class="product-name"><?php echo htmlspecialchars($product['productName']); ?></h3>
                <p class="product-description"><?php echo htmlspecialchars($product['aboutProduct']); ?></p>
                <div class="product-footer">
                    <div class="product-price"><?php echo $price; ?></div>
                    <div class="product-stock">В наличии: <?php echo $product['countOfProduct']; ?> шт.</div>
                </div>
            </div>
        </div>
<?php 
    endforeach; 
else: 
?>
    <div class="no-products">
        <h3>Товары отсутствуют</h3>
        <p>У этого мастера пока нет товаров в продаже.</p>
    </div>
<?php endif; ?>