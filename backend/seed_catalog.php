<?php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';

$items = [
    ['name' => 'Sovereign Calfskin Briefcase', 'category' => 'Bags', 'price' => 420000, 'stock' => 14, 'img' => 'product_6aa94f53150910.46342293.jpg', 'desc' => 'Hand-buffed Italian vegetable-tanned leather briefcase with brass lock and suede interior.'],
    ['name' => 'Monogram Travel Keepall 55', 'category' => 'Bags', 'price' => 540000, 'stock' => 8, 'img' => 'product_6aac4a993b7808.83420714.jpg', 'desc' => 'Spacious cabin-size travel bag crafted from durable coated canvas and natural cowhide trim.'],
    ['name' => 'Vendome Structured Shoulder Bag', 'category' => 'Bags', 'price' => 380000, 'stock' => 12, 'img' => 'product_6aac4ad8e43154.05101535.jpg', 'desc' => 'Architectural shoulder silhouette featuring gold turn-lock closure and dual interior compartments.'],
    ['name' => 'Riviera Woven Straw Tote', 'category' => 'Bags', 'price' => 210000, 'stock' => 18, 'img' => 'product_6aac4b4d098793.28513066.jpg', 'desc' => 'Handwoven raffia beach and city tote with smooth chestnut calfskin handles and inner pouch.'],
    ['name' => 'Palazzo Quilted Crossbody', 'category' => 'Bags', 'price' => 340000, 'stock' => 15, 'img' => 'product_6aac4bb0d3a337.33064265.jpg', 'desc' => 'Supple lambskin leather with geometric chevron quilting and sliding chain shoulder strap.'],
    ['name' => 'Elysium Mini Trunk', 'category' => 'Bags', 'price' => 490000, 'stock' => 6, 'img' => 'product_6aac4bf2123ed8.92826511.jpg', 'desc' => 'Rigid miniature trunk silhouette inspired by historic travel archives, finished with polished gold corners.'],
    ['name' => 'Atelier Leather Messenger', 'category' => 'Bags', 'price' => 310000, 'stock' => 20, 'img' => 'product_6aac4c4d497232.90042723.jpg', 'desc' => 'Effortless crossbody messenger featuring water-resistant lining and secure magnetic flap.'],
    ['name' => 'Milano Suede Weekender', 'category' => 'Bags', 'price' => 460000, 'stock' => 9, 'img' => 'product_6aadb09dc71d78.40278402.jpg', 'desc' => 'Rich chocolate brown suede overnight bag with reinforced rolled leather handles.'],
    ['name' => 'Crocodile-Embossed Evening Clutch', 'category' => 'Bags', 'price' => 280000, 'stock' => 14, 'img' => 'product_6aadb0c81e9348.84309544.jpg', 'desc' => 'Sleek structured evening clutch crafted from croc-embossed calfskin with magnetic clasp.'],
    ['name' => 'Saffiano Executive Document Case', 'category' => 'Bags', 'price' => 350000, 'stock' => 11, 'img' => 'product_6aadb0e6bcb8e5.23205737.jpg', 'desc' => 'Cross-grain scratch-resistant Saffiano leather slim briefcase engineered for contemporary devices.'],
    ['name' => 'Grand Master Tourbillon', 'category' => 'Watches', 'price' => 920000, 'stock' => 3, 'img' => 'product_6aac4a532933b1.85430183.jpg', 'desc' => 'Exquisite haute horlogerie tourbillon masterpiece cased in hand-polished 18k rose gold.'],
    ['name' => 'Chronoscope Platinum Automatic', 'category' => 'Watches', 'price' => 780000, 'stock' => 5, 'img' => 'product_6aac4cc1a12e55.28472983.jpg', 'desc' => 'Swiss-movement self-winding chronograph with exhibition sapphire caseback and alligator strap.'],
    ['name' => 'Nautilus Dual-Time Chronograph', 'category' => 'Watches', 'price' => 620000, 'stock' => 7, 'img' => 'product_6aac4d353eeee2.07998197.jpg', 'desc' => 'Brushed stainless steel sports timepiece with sunburst ceramic dial and 100m water resistance.'],
    ['name' => 'Royal Skeleton Mechanical', 'category' => 'Watches', 'price' => 850000, 'stock' => 4, 'img' => 'product_6aac4dbe20bdb2.46446114.jpg', 'desc' => 'Open-work skeletonized dial revealing the intricate tourbillon movement in satin-brushed rose gold.'],
    ['name' => 'Celestial Moonphase Watch', 'category' => 'Watches', 'price' => 560000, 'stock' => 9, 'img' => 'product_6aada221b6c459.87335599.jpg', 'desc' => 'Deep midnight blue dial displaying genuine lunar phases with hand-applied gold indices.'],
    ['name' => 'Imperiale Diamond Bezel Watch', 'category' => 'Watches', 'price' => 720000, 'stock' => 6, 'img' => 'product_6aada2592f13c1.48401472.jpg', 'desc' => 'Two-tone rose gold timepiece framed with 48 brilliant-cut pave diamonds and mother-of-pearl dial.'],
    ['name' => 'Aero Vintage Pilot Watch', 'category' => 'Watches', 'price' => 480000, 'stock' => 11, 'img' => 'product_6aada28711e8a7.68280517.jpg', 'desc' => 'Matte black dial with luminescent numerals, oversized winding crown, and aged leather strap.'],
    ['name' => 'Aqua Mariner Chronometer', 'category' => 'Watches', 'price' => 590000, 'stock' => 8, 'img' => 'product_6aadb1091c9cd9.16064162.jpg', 'desc' => 'Deep-sea diver watch tested to 300 meters with unidirectional rotating ceramic bezel.'],
    ['name' => 'Aurelia 18K Solid Gold Cuff', 'category' => 'Jewelry', 'price' => 360000, 'stock' => 10, 'img' => 'product_6aada2c3dff598.45257941.jpg', 'desc' => 'Minimalist torque bangle crafted in solid 18-karat yellow gold with beveled mirror finish.'],
    ['name' => 'Eternity Pave Diamond Ring', 'category' => 'Jewelry', 'price' => 440000, 'stock' => 8, 'img' => 'product_6aada2f6ed3838.74972261.jpg', 'desc' => 'Continuous band of conflict-free round brilliant diamonds set in rhodium-plated platinum.'],
    ['name' => 'Solstice Tahitian Pearl Pendant', 'category' => 'Jewelry', 'price' => 290000, 'stock' => 14, 'img' => 'product_6aada3617690b0.53518490.jpg', 'desc' => 'Lustrous peacock-green Tahitian cultured pearl suspended from a delicate diamond-cut chain.'],
    ['name' => 'Medallion Coin Necklace', 'category' => 'Jewelry', 'price' => 220000, 'stock' => 16, 'img' => 'product_6aada38d2e74a3.04807351.jpg', 'desc' => 'Ancient Roman coin-inspired talisman cast in hand-hammered vermeil gold on an adjustable chain.'],
    ['name' => 'Cascade Emerald Drop Earrings', 'category' => 'Jewelry', 'price' => 410000, 'stock' => 7, 'img' => 'product_6aada3edc904f7.75098904.jpg', 'desc' => 'Natural Zambian emeralds paired with baguette diamonds in elegant drop articulated mounts.'],
    ['name' => 'Signet Hexagon Ring', 'category' => 'Jewelry', 'price' => 185000, 'stock' => 15, 'img' => 'product_6aada497ee9644.89199838.jpg', 'desc' => 'Geometric brushed signet ring featuring a flat hexagonal face suitable for custom engraving.'],
    ['name' => 'Luminary Diamond Tennis Bracelet', 'category' => 'Jewelry', 'price' => 530000, 'stock' => 5, 'img' => 'product_6aadb156d62881.41694781.jpg', 'desc' => 'Classic line bracelet with four-prong set brilliant-cut diamonds with safety clasp.'],
    ['name' => 'Monte Carlo Velvet Loafers', 'category' => 'Shoes', 'price' => 275000, 'stock' => 12, 'img' => 'product_6aada54a96de34.52744403.jpg', 'desc' => 'Opulent Venetian velvet smoking loafers with quilted silk lining and stitched leather soles.'],
    ['name' => 'Chelsea Burnished Leather Boots', 'category' => 'Shoes', 'price' => 330000, 'stock' => 10, 'img' => 'product_6aada5c7561da8.81496346.jpg', 'desc' => 'Hand-finished Goodyear-welted calfskin Chelsea boots with elasticated side gussets.'],
    ['name' => 'Savile Row Double Monkstraps', 'category' => 'Shoes', 'price' => 310000, 'stock' => 14, 'img' => 'product_6aada6070a9206.93200119.jpg', 'desc' => 'Refined formal monk shoes with twin silver buckles cut from full-grain French box calf.'],
    ['name' => 'Milano Suede Driving Moccasins', 'category' => 'Shoes', 'price' => 240000, 'stock' => 16, 'img' => 'product_6aada64e9c60e4.65085988.jpg', 'desc' => 'Buttery soft split suede slip-ons with pebbled rubber grip soles for effortless comfort.'],
    ['name' => 'Capri Ankle-Strap Stiletto', 'category' => 'Shoes', 'price' => 295000, 'stock' => 8, 'img' => 'product_6aada888d4e296.23394743.jpg', 'desc' => 'Sculptural 90mm heels in patent leather with minimal ankle fastening and padded insole.'],
    ['name' => 'St. Moritz Shearling Combat Boots', 'category' => 'Shoes', 'price' => 370000, 'stock' => 9, 'img' => 'product_6aada8dcbd1f58.03020725.jpg', 'desc' => 'Rugged luxe lug-sole boots lined with natural shearling wool and waterproof oiled leather.'],
    ['name' => 'Kensington Oxford Wingtips', 'category' => 'Shoes', 'price' => 325000, 'stock' => 11, 'img' => 'product_6aada90c293090.20230290.jpg', 'desc' => 'Full brogue Oxford dress shoes with medallion toe perforations and oak-bark tanned soles.'],
    ['name' => 'Cashmere Double-Breasted Overcoat', 'category' => 'Clothing', 'price' => 680000, 'stock' => 6, 'img' => 'product_6aada933baf7d7.14151550.jpg', 'desc' => 'Pure Mongolian cashmere winter overcoat with horn buttons and peak lapels.'],
    ['name' => 'Silk Charmeuse Evening Gown', 'category' => 'Clothing', 'price' => 450000, 'stock' => 7, 'img' => 'product_6aada9766f2814.80406066.jpg', 'desc' => 'Fluid bias-cut silk gown with cowl neckline and sweeping floor-length hemline.'],
    ['name' => 'Savile Merino Wool Tuxedo', 'category' => 'Clothing', 'price' => 580000, 'stock' => 8, 'img' => 'product_6aadabc678f377.30968076.jpg', 'desc' => 'Midnight black tuxedo tailored from Super 150s Australian wool with grosgrain silk lapels.'],
    ['name' => 'Linen Safari Overshirt', 'category' => 'Clothing', 'price' => 175000, 'stock' => 20, 'img' => 'product_6aadacd49e45a5.65365248.jpg', 'desc' => 'Breathable washed Irish linen overshirt featuring utility cargo chest pockets.'],
    ['name' => 'Cable-Knit Heritage Fisherman Sweater', 'category' => 'Clothing', 'price' => 230000, 'stock' => 15, 'img' => 'product_6aadad23e2abf2.32118439.jpg', 'desc' => 'Heavyweight Aran wool crewneck with traditional honeycomb cabling and ribbed trims.'],
    ['name' => 'Pleated Wide-Leg Silk Trousers', 'category' => 'Clothing', 'price' => 195000, 'stock' => 14, 'img' => 'product_6aadad5accef16.15856848.jpg', 'desc' => 'Tailored high-rise trousers crafted in heavyweight silk crepe with double front pleats.'],
    ['name' => 'Neapolitan Unstructured Blazer', 'category' => 'Clothing', 'price' => 420000, 'stock' => 10, 'img' => 'product_6aadaeeb67f395.75467115.jpg', 'desc' => 'Featherweight unlined jacket in wool-silk-linen blend with soft shirt shoulders.'],
    ['name' => 'Aviator Titanium Polarized Sunglasses', 'category' => 'Accessories', 'price' => 165000, 'stock' => 22, 'img' => 'product_6aadaf1fd0bd90.73409663.jpg', 'desc' => 'Ultra-lightweight Japanese titanium frames with anti-reflective polarized UV400 lenses.'],
    ['name' => 'Hermes-Weave Reversible Leather Belt', 'category' => 'Accessories', 'price' => 145000, 'stock' => 18, 'img' => 'product_6aadaf55bb7f34.41141359.jpg', 'desc' => 'Dual-color black and saddle tan reversible calfskin belt with palladium buckle.'],
    ['name' => 'Como Silk Twill Square Scarf', 'category' => 'Accessories', 'price' => 115000, 'stock' => 25, 'img' => 'product_6aadaf87c0dfb2.89688953.jpg', 'desc' => 'Hand-rolled silk scarf printed in Como, Italy, with archival equestrian motifs.'],
    ['name' => 'Bespoke Cashmere Ribbed Beanie', 'category' => 'Accessories', 'price' => 95000, 'stock' => 30, 'img' => 'product_6aadafb39b4ff1.94460931.jpg', 'desc' => 'Superfine 12-gauge Scottish cashmere knit cap offering lightweight winter warmth.'],
    ['name' => 'Saffiano Leather Passport Wallet', 'category' => 'Accessories', 'price' => 130000, 'stock' => 24, 'img' => 'product_6aadaff03f7061.62526718.jpg', 'desc' => 'Dedicated travel organizer holding passport, boarding passes, and six currency cards.'],
    ['name' => 'Sterling Silver Money Clip', 'category' => 'Accessories', 'price' => 110000, 'stock' => 20, 'img' => 'product_6aadb01d41cd72.18178687.jpg', 'desc' => 'Engine-turned 925 solid sterling silver tension spring money clip with hallmark stamp.'],
    ['name' => 'Artisan Braided Leather Key Bell', 'category' => 'Accessories', 'price' => 85000, 'stock' => 28, 'img' => 'product_6aadb075b4fed3.94977304.jpg', 'desc' => 'Hand-braided leather clochette key ring with polished brass split ring and snap strap.']
];

$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'https';
$host = $_SERVER['HTTP_HOST'] ?? 'omas-backend-055z.onrender.com';
$baseUrl = $scheme . '://' . $host . '/uploads/';

// Ensure cart and wishlist orphaned records are cleared
@$conn->query("DELETE FROM cart WHERE product_id NOT IN (SELECT id FROM products)");
@$conn->query("DELETE FROM wishlist WHERE product_id NOT IN (SELECT id FROM products)");

// Remove broken or empty dummy test rows
@$conn->query("DELETE FROM products WHERE name = '' OR name IS NULL OR price <= 0");

$inserted = 0;
$updated = 0;

$stmt_check = $conn->prepare("SELECT id FROM products WHERE image LIKE ? LIMIT 1");
$stmt_update = $conn->prepare("UPDATE products SET name = ?, category = ?, price = ?, stock = ?, image = ?, description = ? WHERE id = ?");
$stmt_insert = $conn->prepare("INSERT INTO products (name, category, price, stock, image, description) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($items as $p) {
    $full_img_url = $baseUrl . $p['img'];
    $img_pattern = '%' . $p['img'] . '%';

    $stmt_check->bind_param('s', $img_pattern);
    $stmt_check->execute();
    $res = $stmt_check->get_result();

    if ($row = $res->fetch_assoc()) {
        $existing_id = $row['id'];
        $stmt_update->bind_param('ssdissi', $p['name'], $p['category'], $p['price'], $p['stock'], $full_img_url, $p['desc'], $existing_id);
        $stmt_update->execute();
        $updated++;
    } else {
        $stmt_insert->bind_param('ssdiss', $p['name'], $p['category'], $p['price'], $p['stock'], $full_img_url, $p['desc']);
        $stmt_insert->execute();
        $inserted++;
    }
}

// Deduplicate any repeated images if any existed earlier
$conn->query("DELETE p1 FROM products p1 INNER JOIN products p2 ON p1.image = p2.image WHERE p1.id < p2.id AND p1.image != ''");
$conn->query("DELETE FROM products WHERE name = '' OR name IS NULL OR price <= 0");

$count_res = $conn->query("SELECT COUNT(*) as total, COUNT(DISTINCT image) as unique_images FROM products WHERE image != ''");
$stats = $count_res ? $count_res->fetch_assoc() : ['total' => 0, 'unique_images' => 0];

echo json_encode([
    'success' => true,
    'message' => 'OMAS Catalogue Seeded & Deduplicated Successfully',
    'inserted' => $inserted,
    'updated' => $updated,
    'total_products' => intval($stats['total'] ?? 0),
    'unique_images' => intval($stats['unique_images'] ?? 0)
]);

$conn->close();
?>