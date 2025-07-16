import { type ReactNode } from 'react';
import { useLocation } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';
import { LayoutContext, type LayoutContextType } from '../../hooks/useLayout';
import { usePermissions } from '../../hooks/usePermissions';
import { getLayoutType } from '../../utils/route-utils';
import { DashboardLayout } from '../LayoutApp/DashboardLayout';
import { TechnicianLayout } from '../LayoutApp/TechnicianLayout';
import { LoadingSpinner } from '../ui/LoadingSpinner';

interface LayoutProviderProps {
  children: ReactNode;
}

export const LayoutProvider = ({ children }: LayoutProviderProps) => {
  const location = useLocation();
  const { isAuthenticated, isLoading } = useAuth();

  // Sempre chamar usePermissions (hooks devem ser chamados na mesma ordem)
  const { getRouteLayout } = usePermissions();

  // Durante o loading, mostrar spinner em layout minimal
  if (isLoading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <LoadingSpinner />
      </div>
    );
  }

  // Usar lógica robusta para determinar o tipo de layout
  const layoutType = getLayoutType(
    location.pathname,
    isAuthenticated,
    getRouteLayout
  );

  const layoutConfig = {
    dashboard: {
      isSidebarVisible: true,
      isHeaderVisible: true,
    },
    technician: {
      isSidebarVisible: false,
      isHeaderVisible: true,
    },
    minimal: {
      isSidebarVisible: false,
      isHeaderVisible: false,
    },
  };

  const config = layoutConfig[layoutType];

  const contextValue: LayoutContextType = {
    layoutType,
    ...config,
  };

  const renderLayout = () => {
    switch (layoutType) {
      case 'technician':
        return <TechnicianLayout>{children}</TechnicianLayout>;
      case 'minimal':
        return <div className="min-h-screen bg-gray-50">{children}</div>;
      case 'dashboard':
      default:
        return <DashboardLayout>{children}</DashboardLayout>;
    }
  };

  return (
    <LayoutContext.Provider value={contextValue}>
      {renderLayout()}
    </LayoutContext.Provider>
  );
};
