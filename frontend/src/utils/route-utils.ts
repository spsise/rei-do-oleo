// Lista de rotas públicas que não precisam de autenticação
export const PUBLIC_ROUTES = [
  '/login',
  '/register',
  '/forgot-password',
  '/reset-password',
  '/verify-email',
];

// Lista de rotas que devem usar layout minimal
export const MINIMAL_LAYOUT_ROUTES = [
  ...PUBLIC_ROUTES,
  '/404',
  '/500',
  '/maintenance',
];

/**
 * Verifica se uma rota é pública (não requer autenticação)
 */
export const isPublicRoute = (pathname: string): boolean => {
  return PUBLIC_ROUTES.includes(pathname);
};

/**
 * Verifica se uma rota deve usar layout minimal
 */
export const shouldUseMinimalLayout = (pathname: string): boolean => {
  return MINIMAL_LAYOUT_ROUTES.includes(pathname);
};

/**
 * Obtém o tipo de layout baseado na rota e estado de autenticação
 */
export const getLayoutType = (
  pathname: string,
  isAuthenticated: boolean,
  getRouteLayout: (path: string) => 'dashboard' | 'technician' | 'minimal'
): 'dashboard' | 'technician' | 'minimal' => {
  // Se não está autenticado ou é rota pública, usar layout minimal
  if (!isAuthenticated || isPublicRoute(pathname)) {
    return 'minimal';
  }

  // Se é rota que deve usar layout minimal, usar minimal
  if (shouldUseMinimalLayout(pathname)) {
    return 'minimal';
  }

  // Caso contrário, usar o layout configurado para a rota
  return getRouteLayout(pathname);
};
