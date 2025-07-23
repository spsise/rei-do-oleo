// Export all services for easy importing
export { authService } from './auth.service';
export { categoryService } from './category.service';
export { clientService } from './client.service';
export { dashboardService } from './dashboard.service';
export { healthService } from './health.service';
export { productService } from './product.service';
export { serviceService } from './service.service';
export { serviceItemService } from './serviceItem.service';
export { technicianService } from './technician.service';
export { vehicleService } from './vehicle.service';

// Export types and utilities
export type {
  LoginData,
  LoginResponse,
  RegisterData,
  User,
} from './auth.service';
export type {
  DashboardAlert,
  DashboardCharts,
  DashboardOverview,
} from './dashboard.service';
export { apiCall, httpClient } from './http-client';
export type { ApiResponse } from './http-client';
export type {
  CreateServiceData,
  TechnicianDashboard,
  TechnicianSearchData,
  TechnicianSearchResult,
  UpdateServiceStatusData,
} from './technician.service';
