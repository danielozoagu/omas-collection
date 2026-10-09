<?php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';

$items = [
    // --- CLOTHING (Shirts, Blouses, Jackets, Knitwear, Pants, Dresses) ---
    ['name' => 'Oxford Tailored Button-Down Shirt', 'category' => 'Clothing', 'price' => 185000, 'stock' => 20, 'img' => 'product_6aada8dcbd1f58.03020725.jpg', 'desc' => 'Crisp sky-blue tailored oxford cotton shirt with mother-of-pearl buttons.'],
    ['name' => 'Fine-Ribbed Knit Polo Shirt', 'category' => 'Clothing', 'price' => 165000, 'stock' => 18, 'img' => 'product_6aada54a96de34.52744403.jpg', 'desc' => 'Luxurious fine-ribbed polo with camp collar and lightweight summer drape.'],
    ['name' => 'Burgundy Italian Poplin Dress Shirt', 'category' => 'Clothing', 'price' => 195000, 'stock' => 15, 'img' => 'product_6aac4ad8e43154.05101535.jpg', 'desc' => 'Rich burgundy tailored poplin button-down shirt with structured collar.'],
    ['name' => 'Linen Safari Utility Overshirt', 'category' => 'Clothing', 'price' => 210000, 'stock' => 16, 'img' => 'product_6aada497ee9644.89199838.jpg', 'desc' => 'Breathable washed olive-khaki linen overshirt featuring utility cargo chest pockets.'],
    ['name' => 'Striped Ribbed Cotton Tee', 'category' => 'Clothing', 'price' => 95000, 'stock' => 25, 'img' => 'product_6aadacd49e45a5.65365248.jpg', 'desc' => 'Vintage-inspired blue and espresso striped crewneck t-shirt.'],
    ['name' => 'Satin Wrap Bell-Sleeve Blouse', 'category' => 'Clothing', 'price' => 175000, 'stock' => 14, 'img' => 'product_6aadabc678f377.30968076.jpg', 'desc' => 'Lustrous burgundy silk-satin wrap blouse with self-tie waist.'],
    ['name' => 'Neapolitan Structured Black Blazer', 'category' => 'Clothing', 'price' => 380000, 'stock' => 10, 'img' => 'product_6aac4a993b7808.83420714.jpg', 'desc' => 'Sharp single-breasted black tailored blazer with notched lapels.'],
    ['name' => 'Sovereign Calfskin Leather Jacket', 'category' => 'Clothing', 'price' => 460000, 'stock' => 8, 'img' => 'product_6aa94f53150910.46342293.jpg', 'desc' => 'Supple black calfskin leather zip-front jacket with turn-down collar.'],
    ['name' => 'Charcoal Knit Buttoned Cardigan', 'category' => 'Clothing', 'price' => 225000, 'stock' => 12, 'img' => 'product_6aada888d4e296.23394743.jpg', 'desc' => 'Cozy wool-blend charcoal jacket cardigan with patch front pockets.'],
    ['name' => 'High-Rise Utility Cargo Trousers', 'category' => 'Clothing', 'price' => 180000, 'stock' => 16, 'img' => 'product_6aada5c7561da8.81496346.jpg', 'desc' => 'Structured olive-khaki cargo trousers with tailored taper and front pockets.'],
    ['name' => 'Pleated Wide-Leg Crepe Trousers', 'category' => 'Clothing', 'price' => 195000, 'stock' => 14, 'img' => 'product_6aada90c293090.20230290.jpg', 'desc' => 'Impeccably draped camel wide-leg trousers with deep double front pleats.'],
    ['name' => 'Vintage Wide-Leg Denim Jeans', 'category' => 'Clothing', 'price' => 160000, 'stock' => 22, 'img' => 'product_6aac4b4d098793.28513066.jpg', 'desc' => 'Light-wash relaxed wide-leg denim jeans cut from 100% rigid cotton.'],
    ['name' => 'Drawstring Cotton Lounge Shorts', 'category' => 'Clothing', 'price' => 85000, 'stock' => 20, 'img' => 'product_6aadad23e2abf2.32118439.jpg', 'desc' => 'Minimalist heavyweight cotton drawstring shorts in essential neutral tones.'],
    ['name' => 'Cropped Utility Drawstring Windbreaker', 'category' => 'Clothing', 'price' => 240000, 'stock' => 11, 'img' => 'product_6aada933baf7d7.14151550.jpg', 'desc' => 'Modern olive technical cropped jacket with oversized flap pockets.'],
    ['name' => 'Down-Filled Quilted Winter Parka', 'category' => 'Clothing', 'price' => 490000, 'stock' => 7, 'img' => 'product_6aadad5accef16.15856848.jpg', 'desc' => 'Navy water-repellent performance parka lined with premium thermal down.'],
    ['name' => 'Corduroy Wide-Leg Overalls Jumper', 'category' => 'Clothing', 'price' => 210000, 'stock' => 9, 'img' => 'product_6aadaeeb67f395.75467115.jpg', 'desc' => 'Warm chestnut corduroy pinafore overalls with side button detailing.'],
    ['name' => 'Blush Tiered Summer Mini Dress', 'category' => 'Clothing', 'price' => 170000, 'stock' => 15, 'img' => 'product_6aac4a532933b1.85430183.jpg', 'desc' => 'Feminine pastel pink square-neck mini dress with flounced tiered skirt.'],
    ['name' => 'Couture Corset Satin Evening Gown', 'category' => 'Clothing', 'price' => 420000, 'stock' => 6, 'img' => 'product_6aadb156d62881.41694781.jpg', 'desc' => 'Dramatic wine-red satin mermaid gown with lace-appliqued corset bodice.'],
    ['name' => 'Burgundy Lace Slit Gala Gown', 'category' => 'Clothing', 'price' => 450000, 'stock' => 5, 'img' => 'product_6aac4c4d497232.90042723.jpg', 'desc' => 'Floor-sweeping burgundy evening gown with lace corset and dramatic high slit.'],
    ['name' => 'Cerulean Floral Siren Maxi Gown', 'category' => 'Clothing', 'price' => 390000, 'stock' => 8, 'img' => 'product_6aac4cc1a12e55.28472983.jpg', 'desc' => 'Sculptural sky-blue gown painted with vibrant cobalt watercolor blossoms.'],
    ['name' => 'Tropical Hibiscus Slip Maxi Dress', 'category' => 'Clothing', 'price' => 260000, 'stock' => 10, 'img' => 'product_6aac4bb0d3a337.33064265.jpg', 'desc' => 'Ivory satin cowl-neck slip gown with bold pink botanical floral print.'],
    ['name' => 'Ribbed Knit V-Neck Column Dress', 'category' => 'Clothing', 'price' => 230000, 'stock' => 12, 'img' => 'product_6aada9766f2814.80406066.jpg', 'desc' => 'Body-contouring espresso brown ribbed knit maxi dress with plunging V-neck.'],
    ['name' => 'Cornflower Tie-Waist Flared Skirt', 'category' => 'Clothing', 'price' => 140000, 'stock' => 16, 'img' => 'product_6aac4bf2123ed8.92826511.jpg', 'desc' => 'Soft cornflower blue A-line midi skirt with self-fabric wrap belt.'],
    ['name' => 'Hooded Washed Denim Jacket', 'category' => 'Clothing', 'price' => 150000, 'stock' => 14, 'img' => 'product_6aada38d2e74a3.04807351.jpg', 'desc' => 'Lightweight blue denim casual hooded jacket with buttoned chest pocket.'],
    ['name' => 'Sparkle Polka Dot Tutu Party Dress', 'category' => 'Clothing', 'price' => 135000, 'stock' => 10, 'img' => 'product_6aada3edc904f7.75098904.jpg', 'desc' => 'Pink crochet-bodice tutu party dress layered with golden metallic sequins.'],

    // --- SHOES (STRICTLY FOOTWEAR ONLY) ---
    ['name' => 'Milano Suede Chelsea Boots', 'category' => 'Shoes', 'price' => 310000, 'stock' => 12, 'img' => 'product_6aada64e9c60e4.65085988.jpg', 'desc' => 'Hand-finished Goodyear-welted black split suede Chelsea boots with elasticated side gussets.'],
    ['name' => 'Sport Urban Street Sneakers', 'category' => 'Shoes', 'price' => 195000, 'stock' => 15, 'img' => 'product_6aac4dbe20bdb2.46446114.jpg', 'desc' => 'Contemporary two-tone panelled street sneakers with cushioned performance sole.'],

    // --- BAGS (STRICTLY BAGS & TOTES ONLY) ---
    ['name' => 'Michael Kors Grained Leather Tote', 'category' => 'Bags', 'price' => 340000, 'stock' => 14, 'img' => 'product_6aada2592f13c1.48401472.jpg', 'desc' => 'Luxe pebbled brown leather shoulder tote with polished gold MK charm medallion.'],
    ['name' => 'Suede Buckle Flap Crossbody Bag', 'category' => 'Bags', 'price' => 280000, 'stock' => 15, 'img' => 'product_6aadafb39b4ff1.94460931.jpg', 'desc' => 'Camel suede and leather flap crossbody bag with wide webbing utility strap.'],
    ['name' => 'Heritage Canvas & Leather Travel Duffle', 'category' => 'Bags', 'price' => 460000, 'stock' => 9, 'img' => 'product_6aadb01d41cd72.18178687.jpg', 'desc' => 'Two-tone natural canvas and rich cognac leather weekender travel bag.'],
    ['name' => 'Floral Embossed Mini Backpack', 'category' => 'Bags', 'price' => 220000, 'stock' => 16, 'img' => 'product_6aadaf1fd0bd90.73409663.jpg', 'desc' => 'Compact cream leather city backpack with embossed floral pattern and gold accents.'],

    // --- WATCHES (STRICTLY WATCHES & TIMEPIECES ONLY) ---
    ['name' => 'Cartier Santos Diamond Iced-Out Watch', 'category' => 'Watches', 'price' => 890000, 'stock' => 4, 'img' => 'product_6aada221b6c459.87335599.jpg', 'desc' => 'Bespoke 18k yellow gold timepiece meticulously pave-set with brilliant round diamonds.'],
    ['name' => 'Samsung Galaxy Smartwatch Titanium', 'category' => 'Watches', 'price' => 320000, 'stock' => 12, 'img' => 'product_6aada2f6ed3838.74972261.jpg', 'desc' => 'Minimalist smartwatch with high-resolution circular AMOLED display and silicone strap.'],

    // --- JEWELRY (STRICTLY JEWELRY ONLY) ---
    ['name' => 'Solid Gold & Sterling Silver Hoop Earrings', 'category' => 'Jewelry', 'price' => 165000, 'stock' => 18, 'img' => 'product_6aadaff03f7061.62526718.jpg', 'desc' => 'Dual-finish mirror-polished round hoop earrings crafted in 18k gold and 925 sterling silver.'],

    // --- ACCESSORIES (STRICTLY ACCESSORIES ONLY) ---
    ['name' => 'Dior Homme Gold Frame Sunglasses', 'category' => 'Accessories', 'price' => 180000, 'stock' => 22, 'img' => 'product_6aada28711e8a7.68280517.jpg', 'desc' => 'Pilot aviator sunglasses with 24k gold-tone metal frame and UV400 dark tinted lenses.'],
    ['name' => 'Braided Full-Grain Leather Belt', 'category' => 'Accessories', 'price' => 115000, 'stock' => 20, 'img' => 'product_6aadaf55bb7f34.41141359.jpg', 'desc' => 'Dark chocolate hand-braided leather belt featuring an antiqued brass roller buckle.'],
    ['name' => 'Como Italy Silk Twill Graphic Scarf', 'category' => 'Accessories', 'price' => 125000, 'stock' => 25, 'img' => 'product_6aadaf87c0dfb2.89688953.jpg', 'desc' => 'Italian hand-rolled pure silk scarf designed with architectural geometric monochrome panels.'],
    ['name' => 'Canboc Cable & Tech Organizer Case', 'category' => 'Accessories', 'price' => 75000, 'stock' => 24, 'img' => 'product_6aadb0e6bcb8e5.23205737.jpg', 'desc' => 'Shockproof padded travel pouch for electronic chargers, cables, and earphone gear.'],

    // --- GADGETS (STRICTLY TECH & ELECTRONICS ONLY) ---
    ['name' => 'Apple AirPods Max Wireless Headphones', 'category' => 'Gadgets', 'price' => 580000, 'stock' => 6, 'img' => 'product_6aada2c3dff598.45257941.jpg', 'desc' => 'Space Gray high-fidelity over-ear active noise-cancelling headphones.'],
    ['name' => 'JBL Boombox 4 Portable Bluetooth Speaker', 'category' => 'Gadgets', 'price' => 350000, 'stock' => 8, 'img' => 'product_6aada3617690b0.53518490.jpg', 'desc' => 'Rugged IP67 waterproof wireless bluetooth speaker delivering massive sound and deep bass.'],
    ['name' => 'Mophie 2-in-1 Wireless Charging Stand', 'category' => 'Gadgets', 'price' => 140000, 'stock' => 15, 'img' => 'product_6aadb09dc71d78.40278402.jpg', 'desc' => 'Fast Qi2 wireless charging dock for smartphone and wireless earbuds simultaneously.'],
    ['name' => 'Sony Cyber-shot W510 Digital Camera', 'category' => 'Gadgets', 'price' => 190000, 'stock' => 7, 'img' => 'product_6aadb1091c9cd9.16064162.jpg', 'desc' => 'Silver compact 12.1MP digital camera with 4x optical zoom and Carl Zeiss lens.'],
    ['name' => 'Jawploy 64GB Digital Audio Voice Recorder', 'category' => 'Gadgets', 'price' => 95000, 'stock' => 14, 'img' => 'product_6aadb0c81e9348.84309544.jpg', 'desc' => 'Pocket-sized stereo voice recorder with backlit screen and noise cancellation.'],
    ['name' => 'Architect Balance Spring LED Desk Lamp', 'category' => 'Gadgets', 'price' => 110000, 'stock' => 12, 'img' => 'product_6aadb075b4fed3.94977304.jpg', 'desc' => 'Adjustable articulated arm studio task lamp finished in matte black powder coat.'],
    ['name' => 'Grand Plush Companion Teddy Bear', 'category' => 'Gadgets', 'price' => 180000, 'stock' => 5, 'img' => 'product_6aac4d353eeee2.07998197.jpg', 'desc' => 'Oversized ultra-soft giant heirloom plush teddy bear with cozy striped knit scarf.']
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

$conn->query("DELETE FROM products WHERE image = '' OR image IS NULL");
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