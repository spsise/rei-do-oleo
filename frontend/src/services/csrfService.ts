import { csrfApi } from './api';

/**
 * Serviço para gerenciar cookies CSRF do Laravel Sanctum
 *
 * ⚠️ IMPORTANTE: Este serviço é apenas para rotas WEB (/web/*),
 * NÃO para rotas API (/api/*) que não precisam de CSRF!
 */
export const csrfService = {
  /**
   * Obtém o cookie CSRF necessário para autenticação SPA em rotas WEB
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
   */
  hasXsrfToken(): boolean {
    const hasCookie = document.cookie.includes('XSRF-TOKEN');
    return hasCookie;
  },

  /**
   * Força a obtenção de um novo cookie CSRF
   * Útil apenas para rotas WEB que precisam de CSRF
   */
  async refreshCsrfCookie(): Promise<void> {
    await this.getCsrfCookie();
  },
};
