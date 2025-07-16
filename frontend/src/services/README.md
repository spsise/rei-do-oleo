# Services Architecture

## Overview

The services layer has been refactored to follow the Single Responsibility Principle and improve maintainability. Each service is now focused on a specific domain.

## Structure

```
services/
├── http-client.ts          # Base HTTP client with interceptors
├── auth.service.ts         # Authentication operations
├── client.service.ts       # Client management
├── product.service.ts      # Product management
├── service.service.ts      # Service management
├── category.service.ts     # Category management
├── vehicle.service.ts      # Vehicle management
├── health.service.ts       # Health check operations
├── index.ts               # Centralized exports
└── README.md              # This documentation
```

## Usage Examples

### Before (Monolithic API Service)

```typescript
import { apiService } from '../services/api';

// Authentication
const loginResponse = await apiService.login(loginData);
const user = await apiService.getProfile();

// Clients
const clients = await apiService.getClients(filters);
const client = await apiService.getClient(id);

// Products
const products = await apiService.getProducts(filters);
const product = await apiService.getProduct(id);
```

### After (Modular Services)

```typescript
import { authService, clientService, productService } from '../services';

// Authentication
const loginResponse = await authService.login(loginData);
const user = await authService.getProfile();

// Clients
const clients = await clientService.getClients(filters);
const client = await clientService.getClient(id);

// Products
const products = await productService.getProducts(filters);
const product = await productService.getProduct(id);
```

## Benefits

1. **Single Responsibility**: Each service handles only one domain
2. **Better Maintainability**: Easier to find and modify specific functionality
3. **Improved Testing**: Services can be tested independently
4. **Reduced Bundle Size**: Only import what you need
5. **Better Code Organization**: Clear separation of concerns
6. **Easier Refactoring**: Changes in one service don't affect others

## Migration Guide

To migrate from the old monolithic `apiService`:

1. Replace imports:

   ```typescript
   // Old
   import { apiService } from '../services/api';

   // New
   import { authService, clientService, productService } from '../services';
   ```

2. Update method calls:

   ```typescript
   // Old
   apiService.login(data);
   apiService.getClients(filters);

   // New
   authService.login(data);
   clientService.getClients(filters);
   ```

3. Update type imports:

   ```typescript
   // Old
   import type { ApiResponse, User } from '../services/api';

   // New
   import type { ApiResponse, User } from '../services';
   ```

## Available Services

- **authService**: Authentication, user management, token handling
- **clientService**: Client CRUD operations and search
- **productService**: Product management, stock operations
- **serviceService**: Service management and dashboard stats
- **categoryService**: Category CRUD operations
- **vehicleService**: Vehicle management
- **healthService**: Health check operations

## Shared Utilities

- **httpClient**: Base HTTP client with interceptors
- **apiCall**: Error handling wrapper for API calls
- **ApiResponse**: Common response interface
