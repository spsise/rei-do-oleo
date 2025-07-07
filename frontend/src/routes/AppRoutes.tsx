import { Navigate, Route, Routes } from 'react-router-dom';
import { NotFoundPage } from '../components/routing/NotFoundPage';
import { ProtectedRoute } from '../components/routing/ProtectedRoute';
import { PublicRoute } from '../components/routing/PublicRoute';
import { CategoriesPage } from '../pages/Categories';
import { ClientsPage } from '../pages/Clients';
import { Dashboard } from '../pages/Dashboard';
import { Login } from '../pages/Login';
import { ProductsPage } from '../pages/Products';
import { ServicesPage } from '../pages/Services';

export const AppRoutes = () => {
  return (
    <Routes>
      {/* Rotas públicas */}
      <Route
        path="/login"
        element={
          <PublicRoute>
            <Login />
          </PublicRoute>
        }
      />

      {/* Rotas protegidas */}
      <Route
        path="/home"
        element={
          <ProtectedRoute>
            <Dashboard />
          </ProtectedRoute>
        }
      />

      <Route
        path="/clients"
        element={
          <ProtectedRoute>
            <ClientsPage />
          </ProtectedRoute>
        }
      />

      <Route
        path="/products"
        element={
          <ProtectedRoute>
            <ProductsPage />
          </ProtectedRoute>
        }
      />

      <Route
        path="/services"
        element={
          <ProtectedRoute>
            <ServicesPage />
          </ProtectedRoute>
        }
      />

      <Route
        path="/categories"
        element={
          <ProtectedRoute>
            <CategoriesPage />
          </ProtectedRoute>
        }
      />

      {/* Redirecionar raiz para dashboard */}
      <Route path="/" element={<Navigate to="/home" replace />} />

      {/* Página 404 */}
      <Route path="*" element={<NotFoundPage />} />
    </Routes>
  );
};
