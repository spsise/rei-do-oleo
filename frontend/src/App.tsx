import { Toaster as Sonner } from '@/components/ui/sonner';
import { Toaster } from '@/components/ui/toaster';
import { TooltipProvider } from '@/components/ui/tooltip';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { useEffect } from 'react';
import { BrowserRouter, Route, Routes } from 'react-router-dom';
import { MainLayout } from './components/layout/MainLayout';
import { ProtectedRoute } from './components/ProtectedRoute';
import { AuthProvider } from './components/providers/AuthProvider';
import { pwaInstaller } from './utils/pwaInstaller';

// Importar interceptors para configurar CSRF automaticamente
import './services/interceptors';

// Pages
import Agenda from './pages/Agenda';
import ClienteDetalhes from './pages/ClienteDetalhes';
import Clientes from './pages/Clientes';
import Configuracoes from './pages/Configuracoes';
import Dashboard from './pages/Dashboard';
import Estoque from './pages/Estoque';
import Financeiro from './pages/Financeiro';
import Login from './pages/Login';
import NotFound from './pages/NotFound';
import NovoCliente from './pages/NovoCliente';
import NovoServico from './pages/NovoServico';
import Relatorios from './pages/Relatorios';
import Servicos from './pages/Servicos';

// Zustand persist

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
});

const App = () => {
  useEffect(() => {
    // Register service worker
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker
        .register('/sw.js')
        .then(() => console.log('SW registered'))
        .catch(() => console.log('SW registration failed'));
    }

    // Initialize PWA installer
    pwaInstaller.init();

    // Update manifest link
    const manifestLink = document.querySelector('link[rel="manifest"]');
    if (!manifestLink) {
      const link = document.createElement('link');
      link.rel = 'manifest';
      link.href = '/manifest.json';
      document.head.appendChild(link);
    }
  }, []);

  return (
    <QueryClientProvider client={queryClient}>
      <TooltipProvider>
        <AuthProvider>
          <Toaster />
          <Sonner />
          <BrowserRouter>
            <Routes>
              <Route path='/login' element={<Login />} />
              <Route
                path='/'
                element={
                  <ProtectedRoute>
                    <MainLayout />
                  </ProtectedRoute>
                }
              >
                <Route
                  index
                  element={
                    <ProtectedRoute requiredRoles={['admin', 'manager']}>
                      <Dashboard />
                    </ProtectedRoute>
                  }
                />
                <Route path='clientes' element={<Clientes />} />
                <Route path='cliente/novo' element={<NovoCliente />} />
                <Route path='cliente/:id' element={<ClienteDetalhes />} />
                <Route path='servicos' element={<Servicos />} />
                <Route path='servico/novo' element={<NovoServico />} />
                <Route path='agenda' element={<Agenda />} />

                {/* Manager-only routes */}
                <Route
                  path='configuracoes'
                  element={
                    <ProtectedRoute requiredRoles={['admin', 'manager']}>
                      <Configuracoes />
                    </ProtectedRoute>
                  }
                />
                <Route
                  path='relatorios'
                  element={
                    <ProtectedRoute requiredRoles={['admin', 'manager']}>
                      <Relatorios />
                    </ProtectedRoute>
                  }
                />
                <Route
                  path='estoque'
                  element={
                    <ProtectedRoute requiredRoles={['admin', 'manager']}>
                      <Estoque />
                    </ProtectedRoute>
                  }
                />
                <Route
                  path='financeiro'
                  element={
                    <ProtectedRoute requiredRoles={['admin', 'manager']}>
                      <Financeiro />
                    </ProtectedRoute>
                  }
                />
              </Route>
              <Route path='*' element={<NotFound />} />
            </Routes>
          </BrowserRouter>
        </AuthProvider>
      </TooltipProvider>
    </QueryClientProvider>
  );
};

export default App;
