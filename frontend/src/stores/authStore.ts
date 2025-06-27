import { create } from "zustand";
import { persist } from "zustand/middleware";
import { User, LoginCredentials } from "../types";
import { authService } from "../services/authService";

interface AuthStore {
  user: User | null;
  token: string | null;
  tokenType: string | null;
  loading: boolean;
  login: (credentials: LoginCredentials) => Promise<void>;
  logout: () => void;
  refreshToken: () => Promise<void>;
  isAuthenticated: () => boolean;
}

export const useAuthStore = create<AuthStore>()(
  persist(
    (set, get) => ({
      user: null,
      token: null,
      tokenType: null,
      loading: false,

      login: async (credentials: LoginCredentials) => {
        set({ loading: true });
        try {
          const response = await authService.login(credentials);

          if (response.status === "success" && response.data) {
            set({
              user: response.data.user,
              token: response.data.token,
              tokenType: response.data.token_type,
              loading: false,
            });
          } else {
            set({ loading: false });
            throw new Error(response.message || "Erro no login");
          }
        } catch (error: unknown) {
          set({ loading: false });

          const axiosError = error as {
            response?: {
              status: number;
              data?: { message?: string; errors?: Record<string, string[]> };
            };
          };

          // Tratamento específico para erros de validação (422)
          if (axiosError.response?.status === 422) {
            const errorMessage =
              axiosError.response.data?.message || "Erro de validação";
            const errors = axiosError.response.data?.errors;

            if (errors) {
              // Formatar erros de validação
              const firstError = Object.values(errors)[0] as string[];
              throw new Error(firstError[0] || errorMessage);
            }
            throw new Error(errorMessage);
          }

          // Tratamento para credenciais inválidas (401)
          if (axiosError.response?.status === 401) {
            throw new Error(
              axiosError.response.data?.message || "Credenciais inválidas"
            );
          }

          // Outros erros
          throw new Error(
            axiosError.response?.data?.message || "Erro ao fazer login"
          );
        }
      },

      logout: () => {
        authService.logout();
        set({ user: null, token: null, tokenType: null });
      },

      refreshToken: async () => {
        try {
          const response = await authService.refreshToken();

          if (response.status === "success" && response.data) {
            set({
              token: response.data.token,
              tokenType: response.data.token_type,
            });
          } else {
            throw new Error(response.message || "Erro ao renovar token");
          }
        } catch (error) {
          get().logout();
          throw error;
        }
      },

      isAuthenticated: () => {
        return !!get().token && !!get().user;
      },
    }),
    {
      name: "auth-storage",
      partialize: (state) => ({
        user: state.user,
        token: state.token,
        tokenType: state.tokenType,
      }),
    }
  )
);
