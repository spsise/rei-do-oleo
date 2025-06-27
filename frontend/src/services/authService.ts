import { api } from "./api";
import { LoginCredentials, LoginResponse, RefreshResponse } from "../types";

export const authService = {
  async login(credentials: LoginCredentials): Promise<LoginResponse> {
    const response = await api.post<LoginResponse>(
      "/v1/auth/login",
      credentials
    );
    return response.data;
  },

  async logout(): Promise<void> {
    try {
      await api.post("/v1/auth/logout");
    } catch (error) {
      // Ignore errors on logout
      console.warn("Erro ao fazer logout:", error);
    }
  },

  async refreshToken(): Promise<RefreshResponse> {
    const response = await api.post<RefreshResponse>("/v1/auth/refresh");
    return response.data;
  },

  async forgotPassword(email: string): Promise<void> {
    await api.post("/v1/auth/forgot-password", { email });
  },
};
