import { useState } from 'react';
import './login.css';

function Login({ onLogin }) {
  const [isRegister, setIsRegister] = useState(false);
  const [formData, setFormData] = useState({ username: '', email: '', password: '' });
  const [error, setError] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    const url = isRegister
      ? '/api/register_customer.php'
      : '/api/login.php';
    
    try {
      const res = await fetch(url, { method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(formData) });
      const data = await res.json();
      if (data.user) {
        try { localStorage.setItem('omas_user', JSON.stringify(data.user)); } catch (e) {}
        onLogin(data.user);
      }
      else if (data.message) { alert(data.message); setIsRegister(false); }
      else setError(data.error);
    } catch { setError("Failed to connect."); }
  };

  return (
    <div className="login-container">
      <section className="login-brand-panel">
        <div className="wordmark">OMAS <span>COLLECTION</span></div>
        <div><p>Chapter 01 / A considered collection</p><h1>Objects with<br /><em>a point of view.</em></h1></div>
        <p>Objects for the everyday</p>
      </section>
      <div className="login-card">
        <p className="eyebrow">Your OMAS account</p>
        <h2>{isRegister ? 'Register' : 'Login'}</h2>
        <p>{isRegister ? 'Create your account and begin your collection.' : 'Enter your details to continue to the collection.'}</p>
        <form onSubmit={handleSubmit}>
          {isRegister && <input className="login-input" type="text" placeholder="Username" value={formData.username} onChange={(e) => setFormData({...formData, username: e.target.value})} required />}
          <input className="login-input" type="email" placeholder="Email" value={formData.email} onChange={(e) => setFormData({...formData, email: e.target.value})} required />
          <input className="login-input" type="password" placeholder="Password" value={formData.password} onChange={(e) => setFormData({...formData, password: e.target.value})} required />
          <button type="submit" className="login-btn">{isRegister ? 'Register' : 'Login'}</button>
        </form>
        <span className="toggle-link" onClick={() => setIsRegister(!isRegister)}>
          {isRegister ? 'Already have an account? Login' : 'New customer? Register'}
        </span>
        {error && <p className="error-msg">{error}</p>}
      </div>
    </div>
  );
}

export default Login;