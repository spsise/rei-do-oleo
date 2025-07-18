import { QueryClientProvider } from '@tanstack/react-query';
import { Toaster } from 'react-hot-toast';
import { BrowserRouter as Router } from 'react-router-dom';
import { LayoutProvider } from './components/layout/LayoutProvider';
import { queryClient } from './config/query-client';
import { toastConfig } from './config/toast-config';
import { AuthProvider } from './contexts/AuthProvider';
import { AppRoutes } from './routes/AppRoutes';

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <AuthProvider>
        <Router
          future={{
            v7_startTransition: true,
            v7_relativeSplatPath: true,
          }}
        >
          <LayoutProvider>
            <AppRoutes />
            <Toaster {...toastConfig} />
          </LayoutProvider>
        </Router>
      </AuthProvider>
    </QueryClientProvider>
  );
}

export default App;
