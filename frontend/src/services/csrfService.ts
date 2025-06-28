import { csrfApi } from './api';

/**
 * Serviço para gerenciar cookies CSRF do Laravel Sanctum
 */
export const csrfService = {
  /**
   * Obtém o cookie CSRF necessário para autenticação SPA
   * Este endpoint é fornecido automaticamente pelo Laravel Sanctum
   */
  async getCsrfCookie(): Promise<void> {
    try {
      await csrfApi.get('/sanctum/csrf-cookie');
    } catch (error) {
      throw new Error(
        'Falha ao obter token CSRF. Verifique se a API está rodando.'
      );
    }
  },

  /**
   * Verifica se o cookie CSRF foi obtido
   * (útil para debug)
   */
  hasXsrfToken(): boolean {
    return document.cookie.includes('XSRF-TOKEN');
  },

  /**
   * Força a obtenção de um novo cookie CSRF
   * Útil quando o token expira ou há problemas de autenticação
   */
  async refreshCsrfCookie(): Promise<void> {
    await this.getCsrfCookie();
  },
};
