import { useState, useEffect } from 'react';
import Login from './login';
import './App.css';

const formatNaira = (amount) => new Intl.NumberFormat('en-NG', {
  style: 'currency',
  currency: 'NGN',
  maximumFractionDigits: 0
}).format(Number(amount || 0));

function App() {
  const [products, setProducts] = useState([]);
  const [cartItems, setCartItems] = useState([]);
  const [wishlist, setWishlist] = useState([]);
  const [orders, setOrders] = useState([]);
  const [sales, setSales] = useState([]);
  const [showCart, setShowCart] = useState(false);
  const [showWishlist, setShowWishlist] = useState(false);
  const [showDashboard, setShowDashboard] = useState(false);
  const [showWelcome, setShowWelcome] = useState(false);
  const [showSearch, setShowSearch] = useState(false);
  const [showContact, setShowContact] = useState(false);
  const [footerPanel, setFooterPanel] = useState(null);
  const [newsletterEmail, setNewsletterEmail] = useState('');
  const [contactForm, setContactForm] = useState({ name: '', email: '', message: '' });
  const [activeCategory, setActiveCategory] = useState('All');
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [formData, setFormData] = useState({ name: '', category: '', price: '', stock: '', image: null, description: '', existing_image: '' });
  const [isEditing, setIsEditing] = useState(false);
  const [editId, setEditId] = useState(null);
  const [user, setUser] = useState(null);
  const [search, setSearch] = useState('');
  const [selectedProduct, setSelectedProduct] = useState(null);
  const [quantity, setQuantity] = useState(1);
  const [notice, setNotice] = useState('');
  const [imageVersion, setImageVersion] = useState(() => Date.now());
  const [isSaving, setIsSaving] = useState(false);
  const [showProfile, setShowProfile] = useState(false);
  const [dashboardSearch, setDashboardSearch] = useState('');
  const [dashboardFilter, setDashboardFilter] = useState('All');
  const [selectedOrder, setSelectedOrder] = useState(null);
  const [customerOrders, setCustomerOrders] = useState([]);
  const [showCustomerOrders, setShowCustomerOrders] = useState(false);
  const [selectedCustomerOrder, setSelectedCustomerOrder] = useState(null);
  const [showAuth, setShowAuth] = useState(false);
  const [profile, setProfile] = useState({
    fullName: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    country: '',
    favoriteCategory: 'Women'
  });
  const [profileForm, setProfileForm] = useState({
    fullName: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    country: '',
    favoriteCategory: 'Women'
  });

  const imageSrc = (image) => {
    if (!image) return '';
    return `${image}${image.includes('?') ? '&' : '?'}v=${imageVersion}`;
  };

  const loadProducts = async () => {
    try {
      const res = await fetch('/api/get_products.php', { cache: 'no-store' });
      if (!res.ok) throw new Error(`Product refresh failed: ${res.status}`);
      const data = await res.json();
      if (!Array.isArray(data)) throw new Error(data.message || data.error || 'Invalid product response');
      setProducts(data);
    } catch (err) { console.error("Failed to load products", err); }
  };

  const loadCart = async () => {
    try {
      const res = await fetch('/api/cart.php?action=get', { credentials: 'include' });
      if (!res.ok) throw new Error(`Cart refresh failed: ${res.status}`);
      const data = await res.json();
      if (!Array.isArray(data)) throw new Error(data.message || 'Invalid cart response');
      setCartItems(data);
    } catch (err) { console.error("Failed to load cart", err); }
  };

  const loadWishlist = async () => {
    try {
      const res = await fetch('/api/wishlist.php?action=get', { credentials: 'include' });
      if (!res.ok) throw new Error(`Wishlist refresh failed: ${res.status}`);
      const data = await res.json();
      if (!Array.isArray(data)) throw new Error(data.message || 'Invalid wishlist response');
      setWishlist(data);
    } catch (err) { console.error("Failed to load wishlist", err); }
  };

  const loadOrders = async () => {
    try {
      const res = await fetch('/api/get_orders.php');
      if (!res.ok) throw new Error(`Orders refresh failed: ${res.status}`);
      const data = await res.json();
      const orderRows = Array.isArray(data) ? data : data.orders || data.data || [];
      const groupedOrders = orderRows.reduce((orderMap, row) => {
        const orderId = row.id || row.order_id;
        if (!orderId) return orderMap;
        const existingOrder = orderMap.get(String(orderId));
        const rowItem = row.product_name || row.product || row.product_id
          ? {
              id: row.product_id || row.item_id || row.id,
              name: row.product_name || row.product || row.name,
              price: row.unit_price || row.price || row.amount,
              quantity: row.quantity || row.qty || 1,
              image: row.image || row.product_image
            }
          : null;
        const embeddedItems = Array.isArray(row.items) || Array.isArray(row.order_items) || Array.isArray(row.products)
          ? row.items || row.order_items || row.products
          : [];
        if (!existingOrder) {
          orderMap.set(String(orderId), {
            ...row,
            id: orderId,
            items: rowItem ? [...embeddedItems, rowItem] : embeddedItems
          });
          return orderMap;
        }
        if (rowItem) existingOrder.items = [...existingOrder.items, rowItem];
        return orderMap;
      }, new Map());
      setOrders([...groupedOrders.values()]);
    } catch (err) { console.error("Failed to load orders", err); }
  };

  const loadCustomerOrders = async () => {
    try {
      const res = await fetch('/api/get_customer_orders.php', { credentials: 'include' });
      if (!res.ok) throw new Error(`Customer orders failed: ${res.status}`);
      const data = await res.json();
      setCustomerOrders(Array.isArray(data) ? data : []);
    } catch (err) { console.error('Failed to load customer orders', err); }
  };

  const loadSales = async () => {
    try {
      const res = await fetch('/api/get_sales.php');
      const data = await res.json();
      setSales(data);
    } catch (err) { console.error("Failed to load sales", err); }
  };

  const updateOrderStatus = async (orderId, status, paymentStatus = selectedOrder?.payment_status || 'Unpaid', trackingNumber = selectedOrder?.tracking_number || '') => {
    try {
      const response = await fetch('/api/update_order_status.php', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order_id: orderId, status, payment_status: paymentStatus, tracking_number: trackingNumber })
      });
      const data = await response.json();
      if (!response.ok || data.success === false) throw new Error(data.message || 'Status update failed');
      setOrders((currentOrders) => currentOrders.map((order) => (
        Number(order.id) === Number(orderId) ? { ...order, status, payment_status: paymentStatus, tracking_number: trackingNumber } : order
      )));
      setSelectedOrder((currentOrder) => currentOrder ? { ...currentOrder, status, payment_status: paymentStatus, tracking_number: trackingNumber } : currentOrder);
      setNotice(`Order #${String(orderId).padStart(4, '0')} marked ${status}.`);
      window.setTimeout(() => setNotice(''), 2500);
    } catch (err) {
      console.error('Failed to update order status', err);
      setNotice('Order status could not be updated.');
      window.setTimeout(() => setNotice(''), 2500);
    }
  };

  useEffect(() => {
    let cancelled = false;
    fetch('/api/get_products.php', { cache: 'no-store' })
      .then((res) => {
        if (!res.ok) throw new Error(`Product request failed: ${res.status}`);
        return res.json();
      })
      .then((data) => {
        if (!Array.isArray(data)) throw new Error(data.message || 'Invalid product response');
        if (!cancelled) setProducts(data);
      })
      .catch((err) => console.error("Failed to load products", err));
    return () => { cancelled = true; };
  }, []);

  useEffect(() => {
    let cancelled = false;
    fetch('/api/get_session.php', { credentials: 'include' })
      .then((res) => {
        if (!res.ok) throw new Error(`Session request failed: ${res.status}`);
        return res.json();
      })
      .then((data) => { if (!cancelled && data.user) setUser(data.user); })
      .catch((err) => console.error('Failed to restore session', err));
    return () => { cancelled = true; };
  }, []);

  useEffect(() => {
    if (!user) return undefined;
    let cancelled = false;
    fetch('/api/cart.php?action=get', { credentials: 'include' })
      .then((res) => {
        if (!res.ok) throw new Error(`Cart refresh failed: ${res.status}`);
        return res.json();
      })
      .then((data) => {
        if (!Array.isArray(data)) throw new Error(data.message || 'Invalid cart response');
        if (!cancelled) setCartItems(data);
      })
      .catch((err) => console.error('Failed to load cart', err));
    return () => { cancelled = true; };
  }, [user]);

  useEffect(() => {
    if (!user) return undefined;
    let cancelled = false;
    fetch('/api/wishlist.php?action=get', { credentials: 'include' })
      .then((res) => {
        if (!res.ok) throw new Error(`Wishlist refresh failed: ${res.status}`);
        return res.json();
      })
      .then((data) => {
        if (!Array.isArray(data)) throw new Error(data.message || 'Invalid wishlist response');
        if (!cancelled) setWishlist(data);
      })
      .catch((err) => console.error('Failed to load wishlist', err));
    return () => { cancelled = true; };
  }, [user]);

  useEffect(() => {
    if (user) {
      const showTimer = setTimeout(() => setShowWelcome(true), 0);
      const hideTimer = setTimeout(() => setShowWelcome(false), 4000);
      return () => { clearTimeout(showTimer); clearTimeout(hideTimer); };
    }
  }, [user]);

  useEffect(() => {
    if (!user) return;

    const savedProfile = JSON.parse(localStorage.getItem(`omas-profile-${user.id}`) || '{}');
    const nextProfile = {
      fullName: savedProfile.fullName || user.username || '',
      email: savedProfile.email || user.email || '',
      phone: savedProfile.phone || '',
      address: savedProfile.address || '',
      city: savedProfile.city || '',
      country: savedProfile.country || '',
      favoriteCategory: savedProfile.favoriteCategory || 'Women'
    };

    const updateProfileState = window.setTimeout(() => {
      setProfile(nextProfile);
      setProfileForm(nextProfile);
    }, 0);

    return () => window.clearTimeout(updateProfileState);
  }, [user]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (isSaving) return;
    setIsSaving(true);
    const data = new FormData();
    data.append('name', formData.name);
    data.append('category', formData.category);
    data.append('price', formData.price);
    data.append('stock', formData.stock);
    data.append('description', formData.description);
    if (formData.image) { data.append('image', formData.image); }
    else { data.append('existing_image', formData.existing_image); }

    const url = isEditing 
      ? `/api/update_product.php?id=${editId}`
      : '/api/add_product.php';
    
    try {
      const response = await fetch(url, { method: 'POST', credentials: 'include', body: data });
      const result = await response.json();
      if (!response.ok) throw new Error(`Product save failed: ${response.status}`);
      if (result.success === false || result.error) {
        throw new Error(result.message || result.error || 'Product save failed');
      }
      await loadProducts();
      setImageVersion(Date.now());
      setFormData({ name: '', category: '', price: '', stock: '', image: null, description: '', existing_image: '' });
      setIsEditing(false);
      setNotice(isEditing ? 'Product updated successfully.' : 'Product added successfully.');
    } catch (err) {
      console.error('Failed to save product', err);
      setNotice('Product could not be saved. Please try again.');
    } finally {
      setIsSaving(false);
      window.setTimeout(() => setNotice(''), 3000);
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Delete this item?')) return;

    try {
      const response = await fetch('/api/delete_product.php', {
        method: 'DELETE',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: Number(id) })
      });

      const text = await response.text();
      let data = {};

      if (text) {
        try {
          data = JSON.parse(text);
        } catch {
          data = { message: text };
        }
      }

      if (!response.ok || data.success === false || data.error) {
        throw new Error(data.error || data.message || `Delete failed: ${response.status}`);
      }

      setProducts((currentProducts) =>
        currentProducts.filter((product) => Number(product.id) !== Number(id))
      );
      setNotice('Product deleted successfully.');
      window.setTimeout(() => setNotice(''), 2200);
    } catch (error) {
      console.error('Failed to delete product', error);
      setNotice('Delete failed. Please try again.');
      window.setTimeout(() => setNotice(''), 2600);
    }
  };

  const handleEditClick = (product) => {
    setFormData({ ...product, image: null, existing_image: product.image });
    setIsEditing(true);
    setEditId(product.id);
    document.querySelector('.form-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  };

  const addToCart = async (productId, qty = 1) => {
    if (!user) {
      setNotice('Create an account or login to add pieces to your bag.');
      setShowAuth(true);
      window.setTimeout(() => setNotice(''), 3000);
      return;
    }
    for (let i = 0; i < qty; i++) {
      const response = await fetch('/api/cart.php?action=add', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId })
      });
      if (!response.ok) throw new Error(`Cart update failed: ${response.status}`);
    }
    await loadCart();
  };

  const removeFromCart = async (cartId) => {
    await fetch('/api/cart.php?action=remove', {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ cart_id: cartId })
    });
    loadCart();
  };

  const isInWishlist = (productId) => wishlist.some((w) => Number(w.product_id) === Number(productId));

  const toggleWishlist = async (productId) => {
    if (!user) {
      setNotice('Please login before using the wishlist.');
      return;
    }

    const inList = isInWishlist(productId);
    const url = inList
      ? '/api/wishlist.php?action=remove'
      : '/api/wishlist.php?action=add';

    try {
      const res = await fetch(url, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId })
      });

      if (!res.ok) {
        throw new Error(`Wishlist request failed: ${res.status}`);
      }

      await loadWishlist();
      setNotice(inList ? 'Removed from wishlist.' : 'Saved to wishlist.');
      window.setTimeout(() => setNotice(''), 2200);
    } catch (err) {
      console.error('Failed to toggle wishlist', err);
      setNotice('Wishlist is not connected to the backend.');
      window.setTimeout(() => setNotice(''), 2600);
    }
  };

  const handleCheckout = async () => {
    if (!user) {
      setShowCart(false);
      setShowAuth(true);
      return;
    }
    try {
      const res = await fetch('/api/checkout.php', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({})
      });
      const data = await res.json();
      if (!res.ok || data.success !== true) throw new Error(data.error || data.message || 'Checkout failed');
      alert(`Order placed! Total: ${formatNaira(data.total)}`);
      setCartItems([]);
      setShowCart(false);
      loadProducts();
      loadCustomerOrders();
    } catch (error) {
      console.error('Checkout failed', error);
      alert(error.message || 'Checkout failed. Please try again.');
    }
  };

  const getCartTotal = () => {
    return cartItems.reduce((total, item) => total + (parseFloat(item.price) * item.quantity), 0);
  };

  const handleLogout = async () => {
    try {
      await fetch('/api/logout.php', { method: 'POST', credentials: 'include' });
    } catch (error) {
      console.error('Failed to close server session', error);
    }
    setUser(null);
    setShowWelcome(false);
    setIsMenuOpen(false);
    setShowProfile(false);
    setShowDashboard(false);
    setShowCustomerOrders(false);
    setSelectedCustomerOrder(null);
    setCartItems([]);
    setWishlist([]);
  };

  const handleProfileSave = () => {
    if (!user) return;

    const nextProfile = {
      fullName: profileForm.fullName || user.username || '',
      email: profileForm.email || user.email || '',
      phone: profileForm.phone || '',
      address: profileForm.address || '',
      city: profileForm.city || '',
      country: profileForm.country || '',
      favoriteCategory: profileForm.favoriteCategory || 'Women'
    };

    setProfile(nextProfile);
    setUser((currentUser) => ({
      ...currentUser,
      username: nextProfile.fullName || currentUser.username,
      email: nextProfile.email || currentUser.email
    }));
    localStorage.setItem(`omas-profile-${user.id}`, JSON.stringify(nextProfile));
    setNotice('Profile updated successfully.');
    window.setTimeout(() => setNotice(''), 2200);
    setShowProfile(false);
  };

  const handleContactSubmit = (event) => {
    event.preventDefault();
    const subject = encodeURIComponent(`OMAS Collection enquiry from ${contactForm.name}`);
    const body = encodeURIComponent(`Name: ${contactForm.name}\nEmail: ${contactForm.email}\n\n${contactForm.message}`);
    window.location.href = `mailto:hello@omascollection.com?subject=${subject}&body=${body}`;
  };

  const openFooterPanel = (title, text) => setFooterPanel({ title, text });

  const handleNewsletterSubmit = (event) => {
    event.preventDefault();
    if (!newsletterEmail) return;
    setNewsletterEmail('');
    setNotice('You are now subscribed to OMAS news.');
    window.setTimeout(() => setNotice(''), 3000);
  };

  const openProductDetail = (product) => {
    setSelectedProduct(product);
    setQuantity(1);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const closeProductDetail = () => {
    setSelectedProduct(null);
    setQuantity(1);
  };

  const handleAddFromDetail = async () => {
    await addToCart(selectedProduct.id, quantity);
    if (user) alert(`✓ Added ${quantity} × ${selectedProduct.name} to your bag.`);
  };

  const filteredProducts = products.filter((product) => {
    const matchesSearch = product.name.toLowerCase().includes(search.toLowerCase());
    const matchesCategory = activeCategory === 'All' || product.category === activeCategory;
    return matchesSearch && matchesCategory;
  });

  const searchResults = products.filter((product) =>
    product.name.toLowerCase().includes(search.toLowerCase()) ||
    (product.description && product.description.toLowerCase().includes(search.toLowerCase()))
  );

  const relatedProducts = selectedProduct
    ? products.filter((p) => p.category === selectedProduct.category && p.id !== selectedProduct.id).slice(0, 4)
    : [];

  const trendingSearches = ['Jacket', 'Blouse', 'Shoes', 'Bag', 'Men', 'Women', 'Accessories'];

  const dashboardOrders = Array.isArray(orders) ? orders.filter((order) => {
    const searchText = dashboardSearch.trim().toLowerCase();
    const customerText = `${order.username || order.customer_name || ''} ${order.email || order.customer_email || ''}`.toLowerCase();
    const orderText = `${order.id || ''} ${getOrderItems(order).map((item) => getOrderItemName(item)).join(' ')}`.toLowerCase();
    const matchesSearch = !searchText || customerText.includes(searchText) || orderText.includes(searchText);
    const status = String(order.status || 'Processing').toLowerCase();
    const matchesFilter = dashboardFilter === 'All' || status === dashboardFilter.toLowerCase();
    return matchesSearch && matchesFilter;
  }) : [];

  const dashboardRevenue = Number(sales.total_revenue || 0);
  const dashboardOrderCount = Number(sales.total_orders || orders.length || 0);
  const dashboardProductCount = Number(sales.total_products || products.length || 0);

  const getOrderItems = (order) => {
    const rawItems = order?.items || order?.order_items || order?.products;
    if (Array.isArray(rawItems)) return rawItems;
    if (typeof rawItems === 'string') {
      try {
        const parsedItems = JSON.parse(rawItems);
        if (Array.isArray(parsedItems)) return parsedItems;
      } catch { /* Keep the fallback item below. */ }
    }
    if (order?.product_name || order?.product) return [order];
    return [];
  };

  const getOrderItemName = (item) => item.name || item.product_name || item.product || item.title || 'Unnamed item';
  const getOrderItemQuantity = (item) => Number(item.quantity || item.qty || 1);
  const getOrderItemPrice = (item) => Number(item.price || item.unit_price || item.amount || 0);

  const categoryDetails = {
    Men: { label: 'Chapter 02 / Menswear', title: 'Form for the everyday.', description: 'Tailored essentials and considered companions for a life in motion.', image: 'https://images.pexels.com/photos/1043474/pexels-photo-1043474.jpeg?auto=compress&cs=tinysrgb&w=1800' },
    Women: { label: 'Chapter 03 / Womenswear', title: 'A softer point of view.', description: 'Expressive layers and quiet signatures selected for your own rhythm.', image: 'https://images.unsplash.com/photo-1485968579580-b6d095142e6e?auto=format&fit=crop&w=1800&q=85' },
    Accessories: { label: 'Chapter 04 / Accessories', title: 'Small things. Lasting impact.', description: 'The finishing gestures that make an everyday object feel entirely yours.', image: 'https://images.unsplash.com/photo-1492707892479-7bc8d5a4ee93?auto=format&fit=crop&w=1800&q=85' },
    Gadgets: { label: 'Chapter 05 / Gadgets', title: 'Useful, beautifully.', description: 'Thoughtful tools designed to move naturally through your day.', image: 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=1800&q=85' },
    Children: { label: 'Chapter 06 / Children', title: 'Made for small adventures.', description: 'Playful objects and easy pieces for the next generation of curious minds.', image: 'https://images.unsplash.com/photo-1503919545889-aef636e10ad4?auto=format&fit=crop&w=1800&q=85' }
  };
  const availableCategories = [...new Set([
    'Women', 'Men', 'Accessories', 'Gadgets', 'Children',
    ...products.map((product) => product.category).filter(Boolean)
  ])];
  const activeCategoryDetails = categoryDetails[activeCategory] || {
    label: `Collection / ${activeCategory}`,
    title: `${activeCategory.charAt(0).toUpperCase()}${activeCategory.slice(1)} for the everyday.`,
    description: `Explore considered pieces from our ${activeCategory} collection.`,
    image: products.find((product) => product.category === activeCategory && product.image)?.image || categoryDetails.Accessories.image
  };
  const activeCategoryIndex = Object.keys(categoryDetails).indexOf(activeCategory);

  return (
    <div className={`storefront page-enter ${activeCategory !== 'All' ? 'category-view' : ''}`}>
      {notice && (
        <div className="floating-notice">
          <span>{notice}</span>
        </div>
      )}
      {showAuth && (
        <div className="auth-overlay" onClick={() => setShowAuth(false)}>
          <div className="auth-modal" onClick={(event) => event.stopPropagation()}>
            <button className="auth-close" onClick={() => setShowAuth(false)} aria-label="Close login">×</button>
            <Login onLogin={(authenticatedUser) => { setUser(authenticatedUser); setShowAuth(false); }} />
          </div>
        </div>
      )}
      {showWelcome && (
        <div className="modal-overlay">
          <div className="modal-content">
            <div className="welcome-mark">O</div>
            <p className="eyebrow">A considered collection</p>
            <h2>Welcome to OMAS Collection</h2>
            <p>Hello, <strong>{user?.username}</strong>!</p>
            <p>Discover objects made for the everyday ritual.</p>
            <button className="primary-button" onClick={() => setShowWelcome(false)}>Enter collection</button>
          </div>
        </div>
      )}

      <header className="site-header">
        <div className="header-left">
          <button className="header-action" onClick={() => setIsMenuOpen(!isMenuOpen)}>
            <span className="menu-lines" aria-hidden="true">≡</span> Menu
          </button>
          <button className="header-action" onClick={() => setShowSearch(true)}>
            <span className="search-icon" aria-hidden="true">⌕</span> Search
          </button>
        </div>

        <div className="wordmark" onClick={() => { closeProductDetail(); setActiveCategory('All'); }} style={{ cursor: 'pointer' }}>
          OMAS <span>COLLECTION</span>
        </div>

        <div className="header-actions-right">
          <button className="icon-button" aria-label="Open wishlist" onClick={() => user ? setShowWishlist(true) : setShowAuth(true)}>
            Wishlist <sup>{wishlist.length}</sup>
          </button>
          <button className="icon-button" aria-label="Open cart" onClick={() => user ? setShowCart(true) : setShowAuth(true)}>
            Bag <sup>{cartItems.length}</sup>
          </button>
          <button className="icon-button" aria-label="Profile" onClick={() => user ? setShowProfile(true) : setShowAuth(true)}>
            Profile
          </button>
          <button className="icon-button" aria-label="Account" onClick={() => setIsMenuOpen(!isMenuOpen)}>
            Account
          </button>
        </div>
      </header>

      {isMenuOpen && (
        <div className="menu-layer" onClick={() => setIsMenuOpen(false)}>
          <aside className="menu-drawer" onClick={(e) => e.stopPropagation()}>
            <div className="drawer-header"><span className="eyebrow">OMAS Collection</span><button className="drawer-close" onClick={() => setIsMenuOpen(false)}>×</button></div>
            <p className="drawer-title">Explore the collection</p>
            <div className="drawer-links">
              <button className={activeCategory === 'All' ? 'drawer-link active' : 'drawer-link'} onClick={() => { setActiveCategory('All'); closeProductDetail(); setIsMenuOpen(false); }}>The edit <span>→</span></button>
              {availableCategories.map((category) => (
                <button key={category} className={activeCategory === category ? 'drawer-link active' : 'drawer-link'} onClick={() => { setActiveCategory(category); closeProductDetail(); setIsMenuOpen(false); }}>
                  {category.charAt(0).toUpperCase() + category.slice(1)} <span>→</span>
                </button>
              ))}
            </div>
            <div className="drawer-account">
              <p className="drawer-section-label">Your account</p>
              <button className="drawer-account-link" onClick={() => { if (user) setShowWishlist(true); else setShowAuth(true); setIsMenuOpen(false); }}>Wishlist <span>{wishlist.length}</span></button>
              <button className="drawer-account-link" onClick={() => { if (user) setShowCart(true); else setShowAuth(true); setIsMenuOpen(false); }}>Bag <span>{cartItems.length}</span></button>
              <button className="drawer-account-link" onClick={() => { if (user) setShowProfile(true); else setShowAuth(true); setIsMenuOpen(false); }}>Profile <span>→</span></button>
              {user?.role === 'admin' && <>
                <button className="drawer-account-link" onClick={() => { setShowDashboard(true); loadSales(); loadOrders(); setIsMenuOpen(false); }}>Dashboard <span>→</span></button>
                <button className="drawer-account-link" onClick={() => { setShowDashboard(true); loadSales(); loadOrders(); setIsMenuOpen(false); }}>Order history <span>→</span></button>
              </>}
              {user && <button className="drawer-account-link" onClick={() => { setShowCustomerOrders(true); loadCustomerOrders(); setIsMenuOpen(false); }}>My orders <span>{customerOrders.length || '→'}</span></button>}
              {user ? <button className="drawer-account-link" onClick={handleLogout}>Sign out <span>→</span></button> : <button className="drawer-account-link" onClick={() => { setShowAuth(true); setIsMenuOpen(false); }}>Login / Register <span>→</span></button>}
            </div>
            <div className="drawer-footer">Objects for the everyday<br /><span>© OMAS Collection</span></div>
          </aside>
        </div>
      )}

      {showSearch && (
        <div className="search-overlay">
          <div className="search-overlay-header">
            <div className="search-overlay-logo" onClick={() => setShowSearch(false)}>
              OMAS <span>COLLECTION</span>
            </div>
            <button className="search-close" onClick={() => setShowSearch(false)}>×</button>
          </div>

          <div className="search-overlay-content">
            <div className="search-input-wrap">
              <span className="search-input-icon" aria-hidden="true">⌕</span>
              <input
                type="text"
                placeholder="Search for a product"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                autoFocus
              />
            </div>

            {search.trim() === '' && (
              <div className="trending-section">
                <p className="trending-label">Trending searches</p>
                <div className="trending-links">
                  {trendingSearches.map((term) => (
                    <button key={term} onClick={() => setSearch(term)}>{term}</button>
                  ))}
                </div>
              </div>
            )}

            {search.trim() !== '' && (
              <div className="search-results-section">
                <p className="search-results-label">
                  {searchResults.length} {searchResults.length === 1 ? 'result' : 'results'} for "{search}"
                </p>
                {searchResults.length === 0 ? (
                  <p className="search-empty">No products matched your search.</p>
                ) : (
                  <div className="search-results-grid">
                    {searchResults.slice(0, 8).map((product) => (
                      <div
                        key={product.id}
                        className="search-result-card"
                        onClick={() => { setShowSearch(false); openProductDetail(product); }}
                      >
                        <div className="search-result-image">
                          {product.image ? <img src={imageSrc(product.image)} alt={product.name} /> : <div className="no-image">OMAS</div>}
                        </div>
                        <p className="search-result-name">{product.name}</p>
                        <p className="search-result-price">{formatNaira(product.price)}</p>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            )}
          </div>
        </div>
      )}

      <main>
        {selectedProduct ? (
          <section className="detail-page">
            <button className="detail-back" onClick={closeProductDetail}>← Back to collection</button>

            <div className="detail-grid">
              <div className="detail-image">
                {selectedProduct.image ? (
                  <img src={imageSrc(selectedProduct.image)} alt={selectedProduct.name} />
                ) : (
                  <div className="no-image">OMAS</div>
                )}
                <button
                  className={`wishlist-button detail-wishlist ${isInWishlist(selectedProduct.id) ? 'active' : ''}`}
                  aria-label="Save item"
                  onClick={() => toggleWishlist(selectedProduct.id)}
                >
                  {isInWishlist(selectedProduct.id) ? '♥' : '♡'}
                </button>
              </div>

              <div className="detail-info">
                <p className="detail-eyebrow">{selectedProduct.category}</p>
                <h1 className="detail-title">{selectedProduct.name}</h1>
                <p className="detail-price">{formatNaira(selectedProduct.price)}</p>

                <p className="detail-stock">
                  {parseInt(selectedProduct.stock) > 0
                    ? `In stock · ${selectedProduct.stock} available`
                    : 'Currently out of stock'}
                </p>

                <p className="detail-description">
                  {selectedProduct.description || 'No description provided for this piece.'}
                </p>

                {parseInt(selectedProduct.stock) > 0 && (
                  <>
                    <div className="detail-quantity">
                      <span className="qty-label">Quantity</span>
                      <div className="qty-selector">
                        <button onClick={() => setQuantity(Math.max(1, quantity - 1))} disabled={quantity <= 1}>-</button>
                        <span>{quantity}</span>
                        <button onClick={() => setQuantity(Math.min(parseInt(selectedProduct.stock), quantity + 1))} disabled={quantity >= parseInt(selectedProduct.stock)}>+</button>
                      </div>
                    </div>

                    <button className="detail-add-btn" onClick={handleAddFromDetail}>
                      Add {quantity} to bag · {formatNaira(parseFloat(selectedProduct.price) * quantity)} <span>→</span>
                    </button>
                  </>
                )}

                {parseInt(selectedProduct.stock) === 0 && (
                  <button className="detail-add-btn disabled" disabled>Out of stock</button>
                )}

                <div className="detail-meta">
                  <div><span>Category</span><p>{selectedProduct.category}</p></div>
                  <div><span>Reference</span><p>OMAS-{String(selectedProduct.id).padStart(4, '0')}</p></div>
                </div>
              </div>
            </div>

            {relatedProducts.length > 0 && (
              <section className="related-section">
                <div className="section-heading">
                  <span>You may also like</span>
                  <span>From {selectedProduct.category}</span>
                </div>
                <div className="product-grid">
                  {relatedProducts.map((product) => (
                    <article key={product.id} className="product-card" onClick={() => openProductDetail(product)} style={{ cursor: 'pointer' }}>
                      <div className="product-image-wrap">
                        {product.image ? <img src={imageSrc(product.image)} alt={product.name} /> : <div className="no-image">OMAS</div>}
                        <button
                          className={`wishlist-button ${isInWishlist(product.id) ? 'active' : ''}`}
                          aria-label={`Save ${product.name}`}
                          onClick={(e) => { e.stopPropagation(); toggleWishlist(product.id); }}
                        >
                          {isInWishlist(product.id) ? '♥' : '♡'}
                        </button>
                      </div>
                      <div className="product-info">
                        <div className="product-meta"><p>{product.category}</p><p className="price">{formatNaira(product.price)}</p></div>
                        <h3>{product.name}</h3>
                        <p className="stock-note">Available · {product.stock} in studio</p>
                      </div>
                    </article>
                  ))}
                </div>
              </section>
            )}
          </section>
        ) : (
          <>
        {activeCategory === 'All' ? <>
        <section className="hero">
          <div className="hero-copy">
            <p className="eyebrow">Chapter 01 / New season</p>
            <h1>Made to be<br /><em>remembered.</em></h1>
            <p className="hero-description">A collection of expressive essentials, selected for how they live with you.</p>
            <button className="text-button" onClick={() => setActiveCategory('All')}>Explore the edit <span>→</span></button>
          </div>
          <div className="hero-stamp">OMAS<br /><span>01</span></div>
        </section>

        <section className="image-chapters" aria-label="OMAS campaign chapters">
          <article className="image-chapter image-chapter-wide">
            <img src="https://images.unsplash.com/photo-1485968579580-b6d095142e6e?auto=format&fit=crop&w=1400&q=85" alt="Woman wearing an editorial fashion look" />
            <div className="image-chapter-caption"><span>02</span><strong>Quiet structure</strong><button onClick={() => setActiveCategory('Women')}>Discover women <span>→</span></button></div>
          </article>
          <article className="image-chapter">
            <img src="https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=1000&q=85" alt="Man wearing a tailored menswear look" />
            <div className="image-chapter-caption"><span>03</span><strong>Everyday form</strong><button onClick={() => setActiveCategory('Men')}>Discover men <span>→</span></button></div>
          </article>
          <article className="image-chapter image-chapter-accent">
            <img src="https://images.unsplash.com/photo-1523779917675-b6ed3a42a561?auto=format&fit=crop&w=1000&q=85" alt="Fashion accessories arranged for an editorial shoot" />
            <div className="image-chapter-caption"><span>04</span><strong>Small signatures</strong><button onClick={() => setActiveCategory('Accessories')}>Discover accessories <span>→</span></button></div>
          </article>
        </section>

        <section className="collection-intro">
          <div>
            <p className="eyebrow">The OMAS edit</p>
            <h2>Objects with<br />a point of view</h2>
          </div>
          <p>From considered accessories to daily companions, each piece is chosen for its character, utility, and lasting place in your wardrobe.</p>
        </section>
        </> : <section className="category-hero">
          <img className="category-hero-image" src={activeCategoryDetails.image} alt={`${activeCategory} collection editorial`} onError={(event) => { event.currentTarget.src = 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=1800&q=85'; }} />
          <div className="category-hero-shade" />
          <div className="category-hero-copy">
            <p className="eyebrow">{activeCategoryDetails.label}</p>
            <h1>{activeCategoryDetails.title}</h1>
            <p>{activeCategoryDetails.description}</p>
              <button className="text-button" onClick={() => setActiveCategory('All')}>Back to the edit <span>→</span></button>
          </div>
          <span className="category-number">{String(activeCategoryIndex < 0 ? 7 : activeCategoryIndex + 2).padStart(2, '0')}</span>
        </section>}

        <div className="collection-wrap">
        {user?.role === 'admin' && (
          <div className="form-section">
            <p className="eyebrow">Studio access</p>
            <h2>Admin Product Manager</h2>
            <form onSubmit={handleSubmit}>
              <div className="row">
                <div className="col-md-6"><div className="form-group"><input type="text" placeholder="Product Name" value={formData.name} onChange={(e) => setFormData({...formData, name: e.target.value})} required /></div></div>
                <div className="col-md-6"><div className="form-group"><input type="text" placeholder="Category (Men, Women, Accessories...)" value={formData.category} onChange={(e) => setFormData({...formData, category: e.target.value})} required /></div></div>
                <div className="col-md-6"><div className="form-group"><input type="number" min="0" step="1" placeholder="Price in naira (₦)" value={formData.price} onChange={(e) => setFormData({...formData, price: e.target.value})} required /></div></div>
                <div className="col-md-6"><div className="form-group"><input type="number" placeholder="Stock" value={formData.stock} onChange={(e) => setFormData({...formData, stock: e.target.value})} required /></div></div>
                <div className="col-md-12"><div className="form-group"><input type="file" onChange={(e) => setFormData({...formData, image: e.target.files[0]})} /></div></div>
                <div className="col-md-12"><div className="form-group"><textarea placeholder="Description" value={formData.description} onChange={(e) => setFormData({...formData, description: e.target.value})} required /></div></div>
              </div>
              <button type="submit" className="primary-button" disabled={isSaving}>{isSaving ? 'Saving...' : isEditing ? 'Update Product' : 'Add New Product'}</button>
            </form>
          </div>
        )}

        <div className="section-heading"><span>{activeCategory === 'All' ? 'Featured pieces' : activeCategory}</span><span>{filteredProducts.length} pieces</span></div>
        <div className="product-grid">
          {filteredProducts.length === 0 ? <p className="empty-state">No products found in this category.</p> : (
            filteredProducts.map((product) => (
              <article key={product.id} className="product-card" onClick={() => openProductDetail(product)} style={{ cursor: 'pointer' }}>
                <div className="product-image-wrap">
                  <span className="product-index">{String(filteredProducts.indexOf(product) + 1).padStart(2, '0')}</span>
                  {product.image ? <img src={imageSrc(product.image)} alt={product.name} /> : <div className="no-image">OMAS</div>}
                  <button
                    className={`wishlist-button ${isInWishlist(product.id) ? 'active' : ''}`}
                    aria-label={`Save ${product.name}`}
                    onClick={(e) => { e.stopPropagation(); toggleWishlist(product.id); }}
                  >
                    {isInWishlist(product.id) ? '♥' : '♡'}
                  </button>
                </div>
                  <div className="product-info">
                    <div className="product-meta"><p>{product.category}</p><p className="price">{formatNaira(product.price)}</p></div>
                    <h3>{product.name}</h3>
                    <p className="stock-note">Available · {product.stock} in studio</p>
                    <button onClick={(e) => { e.stopPropagation(); addToCart(product.id, 1); }} className="add-cart-btn">Add to bag <span>→</span></button>
                    
                    {user?.role === 'admin' && (
                      <div className="d-flex gap-2 mt-2" onClick={(e) => e.stopPropagation()}>
                         <button type="button" onClick={(e) => { e.stopPropagation(); handleEditClick(product); }} className="edit-btn">Edit</button>
                         <button type="button" onClick={(e) => { e.stopPropagation(); handleDelete(product.id); }} className="delete-btn">Delete</button>
                      </div>
                    )}
                  </div>
              </article>
            ))
          )}
        </div>
        </div>
          </>
        )}
      </main>

      <footer className="site-footer">
        <div className="footer-columns">
          <section>
            <p className="footer-heading">Help</p>
            <button onClick={() => setShowContact(true)}>Contact us</button>
            <button onClick={() => openFooterPanel('Frequently asked questions', 'Find answers about orders, payments, delivery, returns, and caring for your OMAS pieces by emailing our support team.')}>Frequently asked questions</button>
            <button onClick={() => openFooterPanel('Product care', 'Each OMAS piece deserves a little attention. Follow the care instructions included with your order, or contact us for product-specific guidance.')}>Product care</button>
            <button onClick={() => openFooterPanel('Find a store', 'OMAS Collection is currently online only. We will share studio and partner store locations here as they become available.')}>Find a store</button>
          </section>
          <section>
            <p className="footer-heading">Services</p>
            <button onClick={() => openFooterPanel('Personalization', 'Make your selection your own. Contact us before placing an order to ask about available personalization options.')}>Personalization</button>
            <button onClick={() => openFooterPanel('Gift services', 'We can help you prepare a considered gift. Contact us with the piece you have in mind and your preferred delivery details.')}>Gift services</button>
            <button onClick={() => openFooterPanel('Repairs', 'For repair or restoration questions, contact our team with your order reference and a short description of the issue.')}>Repairs</button>
            <button onClick={() => openFooterPanel('Shipping and returns', 'Orders are prepared with care. Contact us before returning an item so we can guide you through the current shipping and returns process.')}>Shipping and returns</button>
          </section>
          <section>
            <p className="footer-heading">About OMAS</p>
            <button onClick={() => openFooterPanel('Our story', 'OMAS Collection is a considered edit of expressive essentials and everyday objects, selected for character, utility, and lasting place.')}>Our story</button>
            <button onClick={() => openFooterPanel('Journal', 'Studio notes, new arrivals, and collection stories are on their way. Subscribe to receive the next edition.')}>Journal</button>
            <button onClick={() => openFooterPanel('Materials and craft', 'We look for thoughtful materials, practical construction, and details that become better companions with time.')}>Materials and craft</button>
            <button onClick={() => openFooterPanel('Sustainability', 'We are building the collection around considered buying, useful design, and pieces intended to stay in rotation.')}>Sustainability</button>
            <button onClick={() => openFooterPanel('Careers', 'We are not hiring at the moment, but you can contact us with your portfolio and a note about how you would like to contribute.')}>Careers</button>
          </section>
          <section className="footer-signup">
            <p className="footer-heading">Email sign-up</p>
            <p>Sign up for OMAS news and receive new arrivals, studio notes, and collection releases.</p>
            <form onSubmit={handleNewsletterSubmit}>
              <input type="email" placeholder="Your email address" aria-label="Your email address" value={newsletterEmail} onChange={(event) => setNewsletterEmail(event.target.value)} required />
              <button type="submit">Subscribe <span>→</span></button>
            </form>
            <p className="footer-follow">Follow us&nbsp;&nbsp; Instagram&nbsp;&nbsp; Pinterest</p>
          </section>
        </div>
        <div className="footer-bottom">
          <span>◎ International (English)</span>
          <div>
            <button onClick={() => openFooterPanel('Sitemap', 'Use the menu to explore the edit, category pages, your profile, wishlist, bag, and order history.')}>Sitemap</button>
            <button onClick={() => openFooterPanel('Legal and privacy', 'Your information is used only to provide this shopping experience and respond to your requests. Contact us with any privacy questions.')}>Legal and privacy</button>
            <button onClick={() => openFooterPanel('Cookies', 'This storefront uses essential browser storage for profile preferences and the shopping experience.')}>Cookies</button>
          </div>
        </div>
        <div className="footer-mark">OMAS <span>COLLECTION</span></div>
      </footer>

      {showContact && (
        <div className="modal-overlay" onClick={() => setShowContact(false)}>
          <div className="contact-modal" onClick={(event) => event.stopPropagation()}>
            <button className="drawer-close contact-close" onClick={() => setShowContact(false)} aria-label="Close contact form">x</button>
            <p className="eyebrow">Help / Contact</p>
            <h2>How can we help?</h2>
            <p className="contact-intro">Send us a message and your email app will open with the enquiry ready to send.</p>
            <form onSubmit={handleContactSubmit} className="contact-form">
              <label>Name<input type="text" value={contactForm.name} onChange={(event) => setContactForm({ ...contactForm, name: event.target.value })} required /></label>
              <label>Email<input type="email" value={contactForm.email} onChange={(event) => setContactForm({ ...contactForm, email: event.target.value })} required /></label>
              <label>Message<textarea value={contactForm.message} onChange={(event) => setContactForm({ ...contactForm, message: event.target.value })} rows="5" required /></label>
              <button className="primary-button" type="submit">Open email <span>-&gt;</span></button>
            </form>
          </div>
        </div>
      )}

      {footerPanel && (
        <div className="modal-overlay" onClick={() => setFooterPanel(null)}>
          <div className="contact-modal footer-info-modal" onClick={(event) => event.stopPropagation()}>
            <button className="drawer-close contact-close" onClick={() => setFooterPanel(null)} aria-label="Close information panel">x</button>
            <p className="eyebrow">OMAS / Information</p>
            <h2>{footerPanel.title}</h2>
            <p className="contact-intro">{footerPanel.text}</p>
            <button className="primary-button" type="button" onClick={() => { setFooterPanel(null); setShowContact(true); }}>Contact our team <span>-&gt;</span></button>
          </div>
        </div>
      )}

      {showWishlist && (
        <div className="cart-overlay" onClick={() => setShowWishlist(false)}>
          <div className="cart-sidebar wishlist-sidebar" onClick={(e) => e.stopPropagation()}>
            <button className="drawer-close" onClick={() => setShowWishlist(false)}>×</button>
            <p className="eyebrow">Saved pieces</p><h2>Your wishlist</h2>
            {wishlist.length === 0 ? (
              <p className="empty-state">Your wishlist is empty. Tap ♡ on any product to save it here.</p>
            ) : wishlist.map((item) => (
              <div key={item.wishlist_id} className="cart-item">
                <img
                  src={item.image || 'https://via.placeholder.com/50'}
                  alt={item.name}
                  style={{ cursor: 'pointer' }}
                  onClick={() => { setShowWishlist(false); openProductDetail(item); }}
                />
                <div style={{ flex: 1, cursor: 'pointer' }} onClick={() => { setShowWishlist(false); openProductDetail(item); }}>
                  <h4 style={{ margin: '0' }}>{item.name}</h4>
                  <p style={{ margin: '0', color: '#888' }}>{formatNaira(item.price)}</p>
                </div>
                <div style={{ display: 'flex', flexDirection: 'column', gap: '6px', alignItems: 'flex-end' }}>
                  <button
                    className="edit-btn"
                    onClick={() => { addToCart(item.product_id, 1); setShowWishlist(false); setShowCart(true); }}
                  >Add to bag</button>
                  <button className="delete-btn" onClick={() => toggleWishlist(item.product_id)}>Remove</button>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {showCart && (
        <div className="cart-overlay" onClick={() => setShowCart(false)}>
          <div className="cart-sidebar" onClick={(e) => e.stopPropagation()}>
            <button className="drawer-close" onClick={() => setShowCart(false)}>×</button>
            <p className="eyebrow">Your selection</p><h2>Your bag</h2>
            {cartItems.length === 0 ? <p>Cart is empty.</p> : cartItems.map((item) => (
              <div key={item.cart_id} className="cart-item">
                <img src={item.image || 'https://via.placeholder.com/50'} alt={item.name} />
                <div style={{flex:1}}>
                  <h4 style={{margin:'0'}}>{item.name}</h4>
                  <p style={{margin:'0', color:'#888'}}>Qty: {item.quantity} | {formatNaira(item.price)}</p>
                </div>
                <button className="delete-btn" onClick={() => removeFromCart(item.cart_id)}>Remove</button>
              </div>
            ))}
            {cartItems.length > 0 && (
              <div style={{marginTop:'20px'}}>
                <h3>Total: {formatNaira(getCartTotal())}</h3>
                <button className="checkout-btn" onClick={handleCheckout}>Checkout <span>→</span></button>
              </div>
            )}
          </div>
        </div>
      )}

      {showDashboard && (
        <div className="dashboard-overlay" onClick={() => setShowDashboard(false)}>
          <section className="dashboard-page" onClick={(e) => e.stopPropagation()}>
            <div className="dashboard-header">
              <div>
                <p className="eyebrow">OMAS / Studio control</p>
                <h2>Good morning, {user.username || 'Admin'}.</h2>
                <p className="dashboard-subtitle">Keep the collection moving. Every order, customer, and product in one place.</p>
              </div>
              <button className="dashboard-close" onClick={() => setShowDashboard(false)} aria-label="Close dashboard">×</button>
            </div>

            <div className="dashboard-stat-grid">
              <div className="dashboard-stat dashboard-stat-featured"><span>Revenue to date</span><strong>{formatNaira(dashboardRevenue)}</strong><small>Across all orders</small></div>
              <div className="dashboard-stat"><span>Orders</span><strong>{dashboardOrderCount}</strong><small>Customer purchases</small></div>
              <div className="dashboard-stat"><span>Products live</span><strong>{dashboardProductCount}</strong><small>Pieces in the catalogue</small></div>
              <div className="dashboard-stat"><span>Average order</span><strong>{formatNaira(dashboardOrderCount ? dashboardRevenue / dashboardOrderCount : 0)}</strong><small>Revenue per order</small></div>
            </div>

            <div className="dashboard-toolbar">
              <div>
                <p className="eyebrow">Order activity</p>
                <h3>Recent orders</h3>
              </div>
              <div className="dashboard-controls">
                <label className="dashboard-search"><span aria-hidden="true">⌕</span><input value={dashboardSearch} onChange={(e) => setDashboardSearch(e.target.value)} placeholder="Search customer or order" /></label>
                <select value={dashboardFilter} onChange={(e) => setDashboardFilter(e.target.value)} aria-label="Filter orders by status">
                  <option>All</option><option>Pending</option><option>Processing</option><option>Paid</option><option>Shipped</option><option>Completed</option><option>Declined</option><option>Cancelled</option>
                </select>
              </div>
            </div>

            <div className="dashboard-table-wrap">
              {dashboardOrders.length === 0 ? <div className="dashboard-empty"><strong>No matching orders</strong><span>New customer purchases will appear here as soon as they are placed.</span></div> : (
                <table className="dashboard-table">
                  <thead><tr><th>Order</th><th>Customer</th><th>Items</th><th>Date</th><th>Status</th><th>Total</th></tr></thead>
                  <tbody>{dashboardOrders.map((order) => {
                    const customerName = order.username || order.customer_name || 'Guest customer';
                    const customerEmail = order.email || order.customer_email || 'No email provided';
                    const orderItems = getOrderItems(order);
                    const itemName = orderItems.length > 0
                      ? orderItems.map((item) => `${getOrderItemName(item)} x${getOrderItemQuantity(item)}`).join(', ')
                      : 'Order items unavailable';
                    const status = order.status || 'Processing';
                    return <tr key={order.id} className="dashboard-order-row" tabIndex="0" onClick={() => setSelectedOrder(order)} onKeyDown={(event) => { if (event.key === 'Enter' || event.key === ' ') setSelectedOrder(order); }}>
                      <td><strong>#{String(order.id).padStart(4, '0')}</strong></td>
                      <td><div className="customer-cell"><span className="customer-avatar">{customerName.charAt(0).toUpperCase()}</span><span><strong>{customerName}</strong><small>{customerEmail}</small></span></div></td>
                      <td><span className="order-items">{itemName}</span></td>
                      <td>{order.created_at ? new Date(order.created_at).toLocaleDateString() : 'Recently'}</td>
                      <td><span className={`order-status ${String(status).toLowerCase()}`}>{status}</span></td>
                      <td><strong>{formatNaira(order.total_amount || order.total)}</strong></td>
                    </tr>;
                  })}</tbody>
                </table>
              )}
            </div>
          </section>
        </div>
      )}

      {showCustomerOrders && (
        <div className="customer-orders-overlay" onClick={() => setShowCustomerOrders(false)}>
          <section className="customer-orders-page" onClick={(event) => event.stopPropagation()}>
            <div className="customer-orders-header">
              <div><p className="eyebrow">Your OMAS account</p><h2>My orders</h2><p>Everything you have purchased, in one place.</p></div>
              <button className="dashboard-close" onClick={() => setShowCustomerOrders(false)} aria-label="Close my orders">×</button>
            </div>
            {customerOrders.length === 0 ? (
              <div className="dashboard-empty"><strong>No orders yet</strong><span>Your completed purchases will appear here.</span></div>
            ) : (
              <div className="customer-order-list">{customerOrders.map((order) => {
                const items = getOrderItems(order);
                return <button className="customer-order-card" key={order.id} onClick={() => setSelectedCustomerOrder(order)}>
                  <span className="customer-order-card-top"><strong>Order #{String(order.id).padStart(4, '0')}</strong><span className={`order-status ${String(order.status || 'Processing').toLowerCase()}`}>{order.status || 'Processing'}</span></span>
                  <span className="customer-order-card-items">{items.length ? items.map((item) => `${getOrderItemName(item)} x${getOrderItemQuantity(item)}`).join(', ') : 'Order items unavailable'}</span>
                  <span className="customer-order-card-bottom"><span>{order.created_at ? new Date(order.created_at).toLocaleDateString() : 'Recently'}</span><strong>{formatNaira(order.total_amount)}</strong></span>
                </button>;
              })}</div>
            )}
          </section>
        </div>
      )}

      {selectedCustomerOrder && (
        <div className="order-detail-overlay" onClick={() => setSelectedCustomerOrder(null)}>
          <aside className="order-detail-panel" onClick={(event) => event.stopPropagation()}>
            <button className="dashboard-close" onClick={() => setSelectedCustomerOrder(null)} aria-label="Close order details">×</button>
            <p className="eyebrow">Your order</p>
            <h2>Order #{String(selectedCustomerOrder.id).padStart(4, '0')}</h2>
            <p className="order-detail-date">Placed {selectedCustomerOrder.created_at ? new Date(selectedCustomerOrder.created_at).toLocaleString() : 'Recently'}</p>
            <div className="order-detail-section">
              <div className="order-detail-section-heading"><span>Purchased items</span><span>{getOrderItems(selectedCustomerOrder).length}</span></div>
              {getOrderItems(selectedCustomerOrder).map((item, index) => <div className="order-line-item" key={`${getOrderItemName(item)}-${index}`}><div><strong>{getOrderItemName(item)}</strong><span>Quantity {getOrderItemQuantity(item)}</span></div><strong>{formatNaira(getOrderItemPrice(item) * getOrderItemQuantity(item))}</strong></div>)}
            </div>
            <div className="order-detail-summary"><span>Fulfilment</span><strong className={`order-status ${String(selectedCustomerOrder.status || 'Processing').toLowerCase()}`}>{selectedCustomerOrder.status || 'Processing'}</strong><span>Payment</span><strong>{selectedCustomerOrder.payment_status || 'Unpaid'}</strong>{selectedCustomerOrder.tracking_number && <><span>Tracking</span><strong>{selectedCustomerOrder.tracking_number}</strong></>}<span>Order total</span><strong>{formatNaira(selectedCustomerOrder.total_amount)}</strong></div>
          </aside>
        </div>
      )}

      {selectedOrder && (
        <div className="order-detail-overlay" onClick={() => setSelectedOrder(null)}>
          <aside className="order-detail-panel" onClick={(event) => event.stopPropagation()}>
            <button className="dashboard-close" onClick={() => setSelectedOrder(null)} aria-label="Close order details">×</button>
            <p className="eyebrow">Order detail</p>
            <h2>Order #{String(selectedOrder.id).padStart(4, '0')}</h2>
            <p className="order-detail-date">Placed {selectedOrder.created_at ? new Date(selectedOrder.created_at).toLocaleString() : 'Recently'}</p>

            <div className="order-detail-customer">
              <span className="customer-avatar">{(selectedOrder.username || selectedOrder.customer_name || 'G').charAt(0).toUpperCase()}</span>
              <div>
                <strong>{selectedOrder.username || selectedOrder.customer_name || 'Guest customer'}</strong>
                <a href={`mailto:${selectedOrder.email || selectedOrder.customer_email || ''}`}>{selectedOrder.email || selectedOrder.customer_email || 'No email provided'}</a>
                {(selectedOrder.phone || selectedOrder.customer_phone) && <span>{selectedOrder.phone || selectedOrder.customer_phone}</span>}
              </div>
            </div>

            <div className="order-detail-section">
              <div className="order-detail-section-heading"><span>Purchased items</span><span>{getOrderItems(selectedOrder).length} {getOrderItems(selectedOrder).length === 1 ? 'item' : 'items'}</span></div>
              {getOrderItems(selectedOrder).length === 0 ? (
                <p className="order-detail-empty">The order was returned without item-level information. Add item details to the orders API response to show the exact products here.</p>
              ) : getOrderItems(selectedOrder).map((item, index) => (
                <div className="order-line-item" key={`${getOrderItemName(item)}-${index}`}>
                  <div><strong>{getOrderItemName(item)}</strong><span>Quantity {getOrderItemQuantity(item)}</span></div>
                  <strong>{formatNaira(getOrderItemPrice(item) * getOrderItemQuantity(item))}</strong>
                </div>
              ))}
            </div>

            <div className="order-detail-summary">
              <span>Fulfilment</span><select className="order-status-select" value={selectedOrder.status || 'Processing'} onChange={(event) => updateOrderStatus(selectedOrder.id, event.target.value)} aria-label="Update fulfilment status"><option>Pending</option><option>Processing</option><option>Shipped</option><option>Completed</option><option>Declined</option><option>Cancelled</option></select>
              <span>Payment</span><select className="order-status-select" value={selectedOrder.payment_status || 'Unpaid'} onChange={(event) => updateOrderStatus(selectedOrder.id, selectedOrder.status || 'Processing', event.target.value)} aria-label="Update payment status"><option>Unpaid</option><option>Pending</option><option>Paid</option><option>Successful</option><option>Failed</option><option>Refunded</option></select>
              <span>Tracking</span><input className="order-tracking-input" value={selectedOrder.tracking_number || ''} onChange={(event) => setSelectedOrder({ ...selectedOrder, tracking_number: event.target.value })} onBlur={() => updateOrderStatus(selectedOrder.id, selectedOrder.status || 'Processing', selectedOrder.payment_status || 'Unpaid', selectedOrder.tracking_number || '')} placeholder="Add tracking number" />
              <span>Order total</span><strong>{formatNaira(selectedOrder.total_amount || selectedOrder.total)}</strong>
            </div>
            {(selectedOrder.address || selectedOrder.shipping_address || selectedOrder.city || selectedOrder.country) && (
              <div className="order-detail-section delivery-details">
                <div className="order-detail-section-heading"><span>Delivery details</span></div>
                <p>{selectedOrder.address || selectedOrder.shipping_address}</p>
                <p>{[selectedOrder.city, selectedOrder.country].filter(Boolean).join(', ')}</p>
              </div>
            )}
          </aside>
        </div>
      )}

      {showProfile && (
        <div className="cart-overlay" onClick={() => setShowProfile(false)}>
          <div className="profile-sidebar" onClick={(e) => e.stopPropagation()}>
            <button className="drawer-close" onClick={() => setShowProfile(false)}>×</button>
            <p className="eyebrow">Your account</p>
            <h2>Profile details</h2>

            <div className="profile-summary">
              <div className="profile-avatar">{(profile.fullName || user.username || 'U').charAt(0).toUpperCase()}</div>
              <div>
                <h3>{profile.fullName || user.username || 'Customer'}</h3>
                <p>{profile.email || user.email || 'No email added yet'}</p>
              </div>
            </div>

            <div className="profile-form-grid">
              <label>
                Full name
                <input type="text" value={profileForm.fullName} onChange={(e) => setProfileForm({ ...profileForm, fullName: e.target.value })} placeholder="Your full name" />
              </label>
              <label>
                Email
                <input type="email" value={profileForm.email} onChange={(e) => setProfileForm({ ...profileForm, email: e.target.value })} placeholder="you@example.com" />
              </label>
              <label>
                Phone number
                <input type="tel" value={profileForm.phone} onChange={(e) => setProfileForm({ ...profileForm, phone: e.target.value })} placeholder="+1 (555) 123-4567" />
              </label>
              <label>
                Address
                <input type="text" value={profileForm.address} onChange={(e) => setProfileForm({ ...profileForm, address: e.target.value })} placeholder="Street address" />
              </label>
              <label>
                City
                <input type="text" value={profileForm.city} onChange={(e) => setProfileForm({ ...profileForm, city: e.target.value })} placeholder="City" />
              </label>
              <label>
                Country
                <input type="text" value={profileForm.country} onChange={(e) => setProfileForm({ ...profileForm, country: e.target.value })} placeholder="Country" />
              </label>
              <label className="profile-full-width">
                Preferred category
                <select value={profileForm.favoriteCategory} onChange={(e) => setProfileForm({ ...profileForm, favoriteCategory: e.target.value })}>
                  <option value="Women">Women</option>
                  <option value="Men">Men</option>
                  <option value="Accessories">Accessories</option>
                  <option value="Gadgets">Gadgets</option>
                  <option value="Children">Children</option>
                </select>
              </label>
            </div>

            <button className="checkout-btn" onClick={handleProfileSave}>Save profile <span>→</span></button>
          </div>
        </div>
      )}
    </div>
  );
}

export default App;

