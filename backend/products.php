<?php
require_once __DIR__ . "/cors.php";
require_once __DIR__ . "/db.php";

// Automatic catalog integrity & strict category alignment fix
try {
    $fixes = [
        ['product_6aada8dcbd1f58.03020725.jpg', 'Oxford Tailored Button-Down Shirt', 'Clothing', 185000, 'Crisp sky-blue tailored oxford cotton shirt with mother-of-pearl buttons.'],
        ['product_6aada54a96de34.52744403.jpg', 'Fine-Ribbed Knit Polo Shirt', 'Clothing', 165000, 'Luxurious fine-ribbed polo with camp collar and lightweight summer drape.'],
        ['product_6aac4ad8e43154.05101535.jpg', 'Burgundy Italian Poplin Dress Shirt', 'Clothing', 195000, 'Rich burgundy tailored poplin button-down shirt with structured collar.'],
        ['product_6aada497ee9644.89199838.jpg', 'Linen Safari Utility Overshirt', 'Clothing', 210000, 'Breathable washed olive-khaki linen overshirt featuring utility cargo chest pockets.'],
        ['product_6aada6070a9206.93200119.jpg', 'Classic Linen Safari Overshirt', 'Clothing', 210000, 'Breathable washed linen overshirt featuring utility flap chest pockets.'],
        ['product_6aadacd49e45a5.65365248.jpg', 'Striped Ribbed Cotton Tee', 'Clothing', 95000, 'Vintage-inspired blue and espresso striped crewneck t-shirt.'],
        ['product_6aadabc678f377.30968076.jpg', 'Satin Wrap Bell-Sleeve Blouse', 'Clothing', 175000, 'Lustrous burgundy silk-satin wrap blouse with self-tie waist.'],
        ['product_6aac4a993b7808.83420714.jpg', 'Neapolitan Structured Black Blazer', 'Clothing', 380000, 'Sharp single-breasted black tailored blazer with notched lapels.'],
        ['product_6aa94f53150910.46342293.jpg', 'Sovereign Calfskin Leather Jacket', 'Clothing', 460000, 'Supple black calfskin leather zip-front jacket with turn-down collar.'],
        ['product_6aada888d4e296.23394743.jpg', 'Charcoal Knit Buttoned Cardigan', 'Clothing', 225000, 'Cozy wool-blend charcoal jacket cardigan with patch front pockets.'],
        ['product_6aada5c7561da8.81496346.jpg', 'High-Rise Utility Cargo Trousers', 'Clothing', 180000, 'Structured olive-khaki cargo trousers with tailored taper and front pockets.'],
        ['product_6aada90c293090.20230290.jpg', 'Pleated Wide-Leg Crepe Trousers', 'Clothing', 195000, 'Impeccably draped camel wide-leg trousers with deep double front pleats.'],
        ['product_6aac4b4d098793.28513066.jpg', 'Vintage Wide-Leg Denim Jeans', 'Clothing', 160000, 'Light-wash relaxed wide-leg denim jeans cut from 100% rigid cotton.'],
        ['product_6aadad23e2abf2.32118439.jpg', 'Drawstring Cotton Lounge Shorts', 'Clothing', 85000, 'Minimalist heavyweight cotton drawstring shorts in essential neutral tones.'],
        ['product_6aada933baf7d7.14151550.jpg', 'Cropped Utility Drawstring Windbreaker', 'Clothing', 240000, 'Modern olive technical cropped jacket with oversized flap pockets.'],
        ['product_6aadad5accef16.15856848.jpg', 'Down-Filled Quilted Winter Parka', 'Clothing', 490000, 'Navy water-repellent performance parka lined with premium thermal down.'],
        ['product_6aadaeeb67f395.75467115.jpg', 'Corduroy Wide-Leg Overalls Jumper', 'Clothing', 210000, 'Warm chestnut corduroy pinafore overalls with side button detailing.'],
        ['product_6aac4a532933b1.85430183.jpg', 'Blush Tiered Summer Mini Dress', 'Clothing', 170000, 'Feminine pastel pink square-neck mini dress with flounced tiered skirt.'],
        ['product_6aadb156d62881.41694781.jpg', 'Couture Corset Satin Evening Gown', 'Clothing', 420000, 'Dramatic wine-red satin mermaid gown with lace-appliqued corset bodice.'],
        ['product_6aac4c4d497232.90042723.jpg', 'Burgundy Lace Slit Gala Gown', 'Clothing', 450000, 'Floor-sweeping burgundy evening gown with lace corset and dramatic high slit.'],
        ['product_6aac4cc1a12e55.28472983.jpg', 'Cerulean Floral Siren Maxi Gown', 'Clothing', 390000, 'Sculptural sky-blue gown painted with vibrant cobalt watercolor blossoms.'],
        ['product_6aac4bb0d3a337.33064265.jpg', 'Tropical Hibiscus Slip Maxi Dress', 'Clothing', 260000, 'Ivory satin cowl-neck slip gown with bold pink botanical floral print.'],
        ['product_6aada9766f2814.80406066.jpg', 'Ribbed Knit V-Neck Column Dress', 'Clothing', 230000, 'Body-contouring espresso brown ribbed knit maxi dress with plunging V-neck.'],
        ['product_6aac4bf2123ed8.92826511.jpg', 'Cornflower Tie-Waist Flared Skirt', 'Clothing', 140000, 'Soft cornflower blue A-line midi skirt with self-fabric wrap belt.'],
        ['product_6aada38d2e74a3.04807351.jpg', 'Hooded Washed Denim Jacket', 'Clothing', 150000, 'Lightweight blue denim casual hooded jacket with buttoned chest pocket.'],
        ['product_6aada3edc904f7.75098904.jpg', 'Sparkle Polka Dot Tutu Party Dress', 'Clothing', 135000, 'Pink crochet-bodice tutu party dress layered with golden metallic sequins.'],
        
        // Strict Shoes (Footwear only)
        ['product_6aada64e9c60e4.65085988.jpg', 'Milano Suede Chelsea Boots', 'Shoes', 310000, 'Hand-finished Goodyear-welted black split suede Chelsea boots with elasticated side gussets.'],
        ['product_6aac4dbe20bdb2.46446114.jpg', 'Sport Urban Street Sneakers', 'Shoes', 195000, 'Contemporary two-tone panelled street sneakers with cushioned performance sole.'],
        
        // Strict Bags
        ['product_6aada2592f13c1.48401472.jpg', 'Michael Kors Grained Leather Tote', 'Bags', 340000, 'Luxe pebbled brown leather shoulder tote with polished gold MK charm medallion.'],
        ['product_6aadafb39b4ff1.94460931.jpg', 'Suede Buckle Flap Crossbody Bag', 'Bags', 280000, 'Camel suede and leather flap crossbody bag with wide webbing utility strap.'],
        ['product_6aadb01d41cd72.18178687.jpg', 'Heritage Canvas & Leather Travel Duffle', 'Bags', 460000, 'Two-tone natural canvas and rich cognac leather weekender travel bag.'],
        ['product_6aadaf1fd0bd90.73409663.jpg', 'Floral Embossed Mini Backpack', 'Bags', 220000, 'Compact cream leather city backpack with embossed floral pattern and gold accents.'],
        
        // Strict Watches
        ['product_6aada221b6c459.87335599.jpg', 'Cartier Santos Diamond Iced-Out Watch', 'Watches', 890000, 'Bespoke 18k yellow gold timepiece meticulously pave-set with brilliant round diamonds.'],
        ['product_6aada2f6ed3838.74972261.jpg', 'Samsung Galaxy Smartwatch Titanium', 'Watches', 320000, 'Minimalist smartwatch with high-resolution circular AMOLED display and silicone strap.'],
        
        // Strict Jewelry
        ['product_6aadaff03f7061.62526718.jpg', 'Solid Gold & Sterling Silver Hoop Earrings', 'Jewelry', 165000, 'Dual-finish mirror-polished round hoop earrings crafted in 18k gold and 925 sterling silver.'],
        
        // Strict Accessories
        ['product_6aada28711e8a7.68280517.jpg', 'Dior Homme Gold Frame Sunglasses', 'Accessories', 180000, 'Pilot aviator sunglasses with 24k gold-tone metal frame and UV400 dark tinted lenses.'],
        ['product_6aadaf55bb7f34.41141359.jpg', 'Braided Full-Grain Leather Belt', 'Accessories', 115000, 'Dark chocolate hand-braided leather belt featuring an antiqued brass roller buckle.'],
        ['product_6aadaf87c0dfb2.89688953.jpg', 'Como Italy Silk Twill Graphic Scarf', 'Accessories', 125000, 'Italian hand-rolled pure silk scarf designed with architectural geometric monochrome panels.'],
        ['product_6aadb0e6bcb8e5.23205737.jpg', 'Canboc Cable & Tech Organizer Case', 'Accessories', 75000, 'Shockproof padded travel pouch for electronic chargers, cables, and earphone gear.'],
        
        // Strict Gadgets
        ['product_6aada2c3dff598.45257941.jpg', 'Apple AirPods Max Wireless Headphones', 'Gadgets', 580000, 'Space Gray high-fidelity over-ear active noise-cancelling headphones.'],
        ['product_6aada3617690b0.53518490.jpg', 'JBL Boombox 4 Portable Bluetooth Speaker', 'Gadgets', 350000, 'Rugged IP67 waterproof wireless bluetooth speaker delivering massive sound and deep bass.'],
        ['product_6aadb09dc71d78.40278402.jpg', 'Mophie 2-in-1 Wireless Charging Stand', 'Gadgets', 140000, 'Fast Qi2 wireless charging dock for smartphone and wireless earbuds simultaneously.'],
        ['product_6aadb1091c9cd9.16064162.jpg', 'Sony Cyber-shot W510 Digital Camera', 'Gadgets', 190000, 'Silver compact 12.1MP digital camera with 4x optical zoom and Carl Zeiss lens.'],
        ['product_6aadb0c81e9348.84309544.jpg', 'Jawploy 64GB Digital Audio Voice Recorder', 'Gadgets', 95000, 'Pocket-sized stereo voice recorder with backlit screen and noise cancellation.'],
        ['product_6aadb075b4fed3.94977304.jpg', 'Architect Balance Spring LED Desk Lamp', 'Gadgets', 110000, 'Adjustable articulated arm studio task lamp finished in matte black powder coat.'],
        ['product_6aac4d353eeee2.07998197.jpg', 'Grand Plush Companion Teddy Bear', 'Gadgets', 180000, 'Oversized ultra-soft giant heirloom plush teddy bear with cozy striped knit scarf.']
    ];

    $stmt_fix = $conn->prepare("UPDATE products SET name = ?, category = ?, price = ?, description = ? WHERE image LIKE ?");
    if ($stmt_fix) {
        foreach ($fixes as $f) {
            $pattern = '%' . $f[0] . '%';
            $stmt_fix->bind_param('ssdss', $f[1], $f[2], $f[3], $f[4], $pattern);
            $stmt_fix->execute();
        }
    }
} catch (Throwable $e) {
    // Graceful fallback
}

$sql = "SELECT id, name, category, price, stock, image, description, created_at FROM products ORDER BY id DESC";
$result = $conn->query($sql);
$products = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

echo json_encode($products);
$conn->close();
?>