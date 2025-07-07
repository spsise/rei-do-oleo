// Export all services for easy importing
export { authService } from './auth.service';
export { categoryService } from './category.service';
export { clientService } from './client.service';
export { healthService } from './health.service';
export { productService } from './product.service';
export { serviceService } from './service.service';
export { vehicleService } from './vehicle.service';

// Export types and utilities
export type {
  LoginData,
  LoginResponse,
  RegisterData,
  User,
} from './auth.service';
export { apiCall, httpClient } from './http-client';
export type { ApiResponse } from './http-client';
