import { createContext, useContext } from 'react';

type LayoutType = 'dashboard' | 'technician' | 'minimal';

interface LayoutContextType {
  layoutType: LayoutType;
  isSidebarVisible: boolean;
  isHeaderVisible: boolean;
}

const LayoutContext = createContext<LayoutContextType | undefined>(undefined);

export const useLayout = () => {
  const context = useContext(LayoutContext);
  if (context === undefined) {
    throw new Error('useLayout deve ser usado dentro de um LayoutProvider');
  }
  return context;
};

export { LayoutContext };
export type { LayoutContextType, LayoutType };
