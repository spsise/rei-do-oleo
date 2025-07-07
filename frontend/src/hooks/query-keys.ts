// Query Keys centralizadas para React Query
export const QUERY_KEYS = {
  // Products
  PRODUCTS: 'products',
  PRODUCT: 'product',
  ACTIVE_PRODUCTS: 'active-products',
  LOW_STOCK_PRODUCTS: 'low-stock-products',
  PRODUCTS_BY_CATEGORY: 'products-by-category',

  // Clients
  CLIENTS: 'clients',
  CLIENT: 'client',

  // Services
  SERVICES: 'services',
  SERVICE: 'service',
  DASHBOARD_STATS: 'dashboard-stats',

  // Categories
  CATEGORIES: 'categories',
  CATEGORY: 'category',

  // Vehicles
  VEHICLES: 'vehicles',

  // Auth
  USER_PROFILE: 'user-profile',
} as const;
