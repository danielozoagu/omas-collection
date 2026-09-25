# OMAS Collection

OMAS Collection is a Vite + React storefront and management UI for catalog products, a cart, orders, and sales overview.

## Local setup

Install dependencies:

```bash
npm install
```

Start the development server:

```bash
npm run dev
```

Build for production:

```bash
npm run build
```

Lint the project:

```bash
npm run lint
```

## Project notes

The app is wired to PHP endpoints hosted locally at:

http://127.0.0.1/OMAS-COLLECTION-BACKEND/

The Vite proxy in the configuration rewrites `/api/*` requests into that backend path for local development.
