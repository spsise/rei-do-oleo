import { createContext } from 'react';

interface AuthContextType {
  initialized: boolean;
}

export const AuthContext = createContext<AuthContextType>({
  initialized: true,
});
