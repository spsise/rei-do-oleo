// ⚠️ DEPRECATED: This file is kept for backward compatibility
// Use the modular services instead: import { authService, clientService, etc. } from '../services'

import type {
  LoginData,
  LoginResponse,
  RegisterData,
  User,
} from './auth.service';
import { authService } from './auth.service';
import { categoryService } from './category.service';
import { clientService } from './client.service';
import { healthService } from './health.service';
import type { ApiResponse } from './http-client';
import { productService } from './product.service';
import { serviceService } from './service.service';
import { vehicleService } from './vehicle.service';

/**
 * @deprecated Use individual services instead:
 * - import { authService } from '../services' for authentication
 * - import { clientService } from '../services' for clients
 * - import { productService } from '../services' for products
 * - etc.
 */
class ApiService {
  // Authentication methods
  login = authService.login;
  register = authService.register;
  logout = authService.logout;
  getProfile = authService.getProfile;
  isAuthenticated = authService.isAuthenticated;
  getToken = authService.getToken;
  getUser = authService.getUser;

  // Client methods
  getClients = clientService.getClients;
  getClient = clientService.getClient;
  createClient = clientService.createClient;
  updateClient = clientService.updateClient;
  deleteClient = clientService.deleteClient;
  searchClientByDocument = clientService.searchClientByDocument;
  searchClientByPhone = clientService.searchClientByPhone;

  // Product methods
  getProducts = productService.getProducts;
  getProduct = productService.getProduct;
  createProduct = productService.createProduct;
  updateProduct = productService.updateProduct;
  deleteProduct = productService.deleteProduct;
  searchProduct = productService.searchProduct;
  getActiveProducts = productService.getActiveProducts;
  getLowStockProducts = productService.getLowStockProducts;
  updateProductStock = productService.updateProductStock;
  getProductsByCategory = productService.getProductsByCategory;

  // Service methods
  getServices = serviceService.getServices;
  getService = serviceService.getService;
  createService = serviceService.createService;
  updateService = serviceService.updateService;
  deleteService = serviceService.deleteService;
  searchService = serviceService.searchService;
  getDashboardStats = serviceService.getDashboardStats;

  // Category methods
  getCategories = categoryService.getCategories;
  getCategory = categoryService.getCategory;
  createCategory = categoryService.createCategory;
  updateCategory = categoryService.updateCategory;
  deleteCategory = categoryService.deleteCategory;

  // Vehicle methods
  getVehicles = vehicleService.getVehicles;

  // Health check
  healthCheck = healthService.healthCheck;
}

export const apiService = new ApiService();

// Re-export types for backward compatibility
export type { ApiResponse, LoginData, LoginResponse, RegisterData, User };
