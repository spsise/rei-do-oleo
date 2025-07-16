import type { User } from '../services/auth.service';

/**
 * Storage utility for managing authentication data
 * Handles localStorage vs sessionStorage based on remember me preference
 */

export class StorageManager {
  private static readonly AUTH_TOKEN_KEY = 'auth_token';
  private static readonly USER_KEY = 'user';
  private static readonly REMEMBER_ME_KEY = 'remember_me';
  private static readonly REMEMBERED_EMAIL_KEY = 'remembered_email';

  /**
   * Get the appropriate storage based on remember me setting
   */
  private static getStorage(): Storage {
    const rememberMe =
      localStorage.getItem(this.REMEMBER_ME_KEY) ||
      sessionStorage.getItem(this.REMEMBER_ME_KEY);

    // If remember_me is not set, check which storage has the token
    if (!rememberMe) {
      const localToken = localStorage.getItem(this.AUTH_TOKEN_KEY);
      const sessionToken = sessionStorage.getItem(this.AUTH_TOKEN_KEY);

      if (localToken && !sessionToken) {
        return localStorage;
      } else if (sessionToken && !localToken) {
        return sessionStorage;
      } else if (localToken && sessionToken) {
        // Both have tokens, prefer localStorage (legacy behavior)
        return localStorage;
      }
    }

    return rememberMe === 'true' ? localStorage : sessionStorage;
  }

  /**
   * Set authentication data
   */
  static setAuthData(token: string, user: User, rememberMe: boolean): void {
    const storage = rememberMe ? localStorage : sessionStorage;

    storage.setItem(this.AUTH_TOKEN_KEY, token);
    storage.setItem(this.USER_KEY, JSON.stringify(user));
    storage.setItem(this.REMEMBER_ME_KEY, rememberMe ? 'true' : 'false');

    // If remember me is enabled, save the email for future logins
    if (rememberMe) {
      localStorage.setItem(this.REMEMBERED_EMAIL_KEY, user.email);
    }
  }

  /**
   * Get authentication token
   */
  static getAuthToken(): string | null {
    return this.getStorage().getItem(this.AUTH_TOKEN_KEY);
  }

  /**
   * Get user data
   */
  static getUser(): User | null {
    const userStr = this.getStorage().getItem(this.USER_KEY);
    if (userStr) {
      try {
        return JSON.parse(userStr);
      } catch {
        return null;
      }
    }
    return null;
  }

  /**
   * Get remembered email (only available if remember me was enabled)
   */
  static getRememberedEmail(): string | null {
    return localStorage.getItem(this.REMEMBERED_EMAIL_KEY);
  }

  /**
   * Check if remember me is enabled
   */
  static isRememberMeEnabled(): boolean {
    return (
      localStorage.getItem(this.REMEMBER_ME_KEY) === 'true' ||
      sessionStorage.getItem(this.REMEMBER_ME_KEY) === 'true'
    );
  }

  /**
   * Check if user is authenticated
   */
  static isAuthenticated(): boolean {
    return !!this.getAuthToken();
  }

  /**
   * Clear all authentication data
   */
  static clearAuthData(): void {
    // Clear from both storages to be safe
    localStorage.removeItem(this.AUTH_TOKEN_KEY);
    localStorage.removeItem(this.USER_KEY);
    localStorage.removeItem(this.REMEMBER_ME_KEY);

    sessionStorage.removeItem(this.AUTH_TOKEN_KEY);
    sessionStorage.removeItem(this.USER_KEY);
    sessionStorage.removeItem(this.REMEMBER_ME_KEY);
  }

  /**
   * Clear authentication data but preserve remembered email if remember me was enabled
   */
  static clearAuthDataPreserveEmail(): void {
    const wasRememberMeEnabled = this.isRememberMeEnabled();

    // Clear authentication data
    this.clearAuthData();

    // If remember me was enabled, keep the email
    if (wasRememberMeEnabled) {
      const rememberedEmail = this.getRememberedEmail();
      if (rememberedEmail) {
        // Keep the email in localStorage for future logins
        localStorage.setItem(this.REMEMBERED_EMAIL_KEY, rememberedEmail);
      }
    } else {
      // If remember me was not enabled, clear the remembered email too
      localStorage.removeItem(this.REMEMBERED_EMAIL_KEY);
    }
  }

  /**
   * Get storage type currently being used
   */
  static getStorageType(): 'localStorage' | 'sessionStorage' {
    return this.isRememberMeEnabled() ? 'localStorage' : 'sessionStorage';
  }
}
