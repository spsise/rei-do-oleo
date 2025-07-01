import React, { createContext } from 'react';

interface AuthContextType {
  initialized: boolean;
}

export const AuthContext = createContext<AuthContextType>({
  initialized: true,
});

interface AuthProviderProps {
  children: React.ReactNode;
}

/**
 * Provider simplificado para autenticação - rotas API não precisam de CSRF
 */
export const AuthProvider: React.FC<AuthProviderProps> = ({ children }) => {
  const value: AuthContextType = {
    initialized: true,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};
