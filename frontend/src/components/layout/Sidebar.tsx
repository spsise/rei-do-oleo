import { Button } from '@/components/ui/button';
import { useSidebar } from '@/contexts/SidebarContext';
import { cn } from '@/lib/utils';
import {
  Calendar,
  ChevronLeft,
  DollarSign,
  FileText,
  Home,
  LogOut,
  Menu,
  Package,
  Settings,
  Users,
  Wrench,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { usePermissions } from '../../hooks/use-permissions';
import { useAuthStore } from '../../stores/authStore';
import { NavigationItem } from '../../types/navigation';

const allNavItems: NavigationItem[] = [
  {
    path: '/',
    icon: Home,
    label: 'Dashboard',
    requiredRoles: ['admin', 'manager'],
  },
  { path: '/clientes', icon: Users, label: 'Clientes' },
  { path: '/agenda', icon: Calendar, label: 'Agenda' },
  { path: '/servicos', icon: Wrench, label: 'Serviços' },
  { path: '/estoque', icon: Package, label: 'Estoque', requiresManager: true },
  {
    path: '/financeiro',
    icon: DollarSign,
    label: 'Financeiro',
    requiresManager: true,
  },
  {
    path: '/relatorios',
    icon: FileText,
    label: 'Relatórios',
    requiresManager: true,
  },
  {
    path: '/configuracoes',
    icon: Settings,
    label: 'Configurações',
    requiresManager: true,
  },
];

export function Sidebar() {
  const location = useLocation();
  const { logout } = useAuthStore();
  const { user, filterNavigationItems } = usePermissions();
  const { isCollapsed, toggleSidebar } = useSidebar();
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  // Fecha o menu mobile quando mudar de rota
  useEffect(() => {
    setIsMobileMenuOpen(false);
  }, [location.pathname]);

  const navItems = filterNavigationItems(allNavItems);

  const sidebarContent = (
    <>
      {/* Header */}
      <div
        className={cn(
          'p-4 border-b border-gray-200 bg-white flex items-center',
          isCollapsed ? 'justify-center' : 'justify-between'
        )}
      >
        <div
          className={cn(
            'flex items-center gap-3',
            isCollapsed && 'justify-center'
          )}
        >
          <div className='w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm'>
            <span className='text-lg font-bold text-white'>RO</span>
          </div>
          {!isCollapsed && (
            <div>
              <h1 className='text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent'>
                Rei do Óleo
              </h1>
              <p className='text-sm text-gray-500 mt-0.5'>Sistema MVP</p>
            </div>
          )}
        </div>
        <Button
          variant='ghost'
          size='sm'
          className={cn(
            'hover:bg-gray-100 transition-colors rounded-lg',
            isCollapsed ? 'hidden md:flex' : 'hidden md:flex'
          )}
          onClick={toggleSidebar}
        >
          <ChevronLeft
            className={cn(
              'h-5 w-5 transition-transform duration-200',
              isCollapsed && 'rotate-180'
            )}
          />
        </Button>
      </div>

      {/* Navigation */}
      <nav className='flex-1 px-3 py-4 overflow-y-auto'>
        <ul className='space-y-1'>
          {navItems.map(({ path, icon: Icon, label }) => {
            const isActive = location.pathname === path;
            return (
              <li key={path}>
                <Link
                  to={path}
                  className={cn(
                    'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group relative',
                    isActive
                      ? 'bg-blue-50 text-blue-700 shadow-sm shadow-blue-100/50 border border-blue-100'
                      : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                  )}
                >
                  <Icon
                    className={cn(
                      'w-5 h-5 flex-shrink-0 transition-colors',
                      isActive ? 'text-blue-600' : 'group-hover:text-blue-600'
                    )}
                  />
                  {!isCollapsed && <span className='font-medium'>{label}</span>}
                  {isCollapsed && (
                    <div className='absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none'>
                      {label}
                    </div>
                  )}
                </Link>
              </li>
            );
          })}
        </ul>
      </nav>

      {/* Footer */}
      <div
        className={cn(
          'p-3 border-t border-gray-200 bg-white',
          isCollapsed ? 'flex flex-col items-center' : ''
        )}
      >
        <div
          className={cn(
            'flex items-center gap-3 mb-3',
            isCollapsed && 'justify-center'
          )}
        >
          <div className='w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-sm flex-shrink-0 relative group'>
            <span className='text-sm font-medium text-white'>
              {user?.name?.charAt(0).toUpperCase()}
            </span>
            {isCollapsed && (
              <div className='absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none'>
                {user?.name}
                <div className='text-xs opacity-75'>{user?.highest_role}</div>
              </div>
            )}
          </div>
          {!isCollapsed && (
            <div className='flex-1 min-w-0'>
              <p className='text-sm font-medium text-gray-900 truncate'>
                {user?.name}
              </p>
              <p className='text-xs text-gray-500 capitalize font-medium'>
                {user?.highest_role}
              </p>
            </div>
          )}
        </div>
        <Button
          variant='ghost'
          size='sm'
          onClick={logout}
          className={cn(
            'w-full gap-2 text-gray-700 hover:text-red-600 hover:bg-red-50 transition-colors duration-200 rounded-xl relative group',
            isCollapsed ? 'justify-center px-3' : 'justify-start'
          )}
        >
          <LogOut className='w-4 h-4' />
          {!isCollapsed && 'Sair'}
          {isCollapsed && (
            <div className='absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-xs rounded-md opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none'>
              Sair
            </div>
          )}
        </Button>
      </div>
    </>
  );

  return (
    <>
      {/* Desktop Sidebar */}
      <aside
        className={cn(
          'hidden md:flex flex-col border-r border-gray-200 h-screen bg-white transition-all duration-300',
          isCollapsed ? 'w-20' : 'w-72'
        )}
      >
        {sidebarContent}
      </aside>

      {/* Mobile Menu Button */}
      <Button
        variant='ghost'
        size='sm'
        className='fixed top-4 left-4 md:hidden z-50'
        onClick={() => setIsMobileMenuOpen(true)}
      >
        <Menu className='w-5 h-5' />
      </Button>

      {/* Mobile Sidebar */}
      {isMobileMenuOpen && (
        <div className='fixed inset-0 z-50 md:hidden'>
          {/* Backdrop */}
          <div
            className='fixed inset-0 bg-gray-900/50 backdrop-blur-sm'
            onClick={() => setIsMobileMenuOpen(false)}
          />

          {/* Sidebar */}
          <aside className='fixed top-0 left-0 h-screen w-72 bg-white shadow-xl'>
            {sidebarContent}
          </aside>
        </div>
      )}
    </>
  );
}
