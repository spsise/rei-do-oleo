import { Navigate, Route, Routes } from 'react-router-dom';
import { NotFoundPage } from '../components/routing/NotFoundPage';
import { ProtectedRoute } from '../components/routing/ProtectedRoute';
import { PublicRoute } from '../components/routing/PublicRoute';
import { VoiceRecognitionProvider } from '../components/VoiceRecognition';
import { VoiceModalProvider } from '../components/VoiceRecognition/VoiceModalContext';
import { AttendantDashboard } from '../pages/AttendantDashboard';
import { CategoriesPage } from '../pages/Categories';
import { ClientsPage } from '../pages/Clients';
import { Dashboard } from '../pages/Dashboard';
import { Login } from '../pages/Login';
import { ProductsPage } from '../pages/Products';
import { ServicesPage } from '../pages/Services';
import { TechnicianPage } from '../pages/Technician';

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

      {/* Rotas protegidas com verificação de permissões */}
      <Route
        path="/home"
        element={
          <ProtectedRoute path="/home">
            <Dashboard />
          </ProtectedRoute>
        }
      />

      <Route
        path="/servicos"
        element={
          <ProtectedRoute path="/servicos">
            <VoiceRecognitionProvider>
              <VoiceModalProvider>
                <TechnicianPage />
              </VoiceModalProvider>
            </VoiceRecognitionProvider>
          </ProtectedRoute>
        }
      />

      <Route
        path="/attendant"
        element={
          <ProtectedRoute path="/attendant">
            <AttendantDashboard />
          </ProtectedRoute>
        }
      />

      <Route
        path="/clients"
        element={
          <ProtectedRoute path="/clients">
            <ClientsPage />
          </ProtectedRoute>
        }
      />

      <Route
        path="/products"
        element={
          <ProtectedRoute path="/products">
            <ProductsPage />
          </ProtectedRoute>
        }
      />

      <Route
        path="/services"
        element={
          <ProtectedRoute path="/services">
            <ServicesPage />
          </ProtectedRoute>
        }
      />

      <Route
        path="/categories"
        element={
          <ProtectedRoute path="/categories">
            <CategoriesPage />
          </ProtectedRoute>
        }
      />

      {/* Redirecionar raiz para rota padrão baseada em permissões */}
      <Route path="/" element={<Navigate to="/home" replace />} />

      {/* Página 404 */}
      <Route path="*" element={<NotFoundPage />} />
    </Routes>
  );
};
