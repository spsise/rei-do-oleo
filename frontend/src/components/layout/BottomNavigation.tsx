import { usePermissions } from '@/hooks/use-permissions';
import { cn } from '@/lib/utils';
import {
  Calendar,
  DollarSign,
  Home,
  Package,
  Settings,
  Users,
} from 'lucide-react';
import { Link, useLocation } from 'react-router-dom';

const allNavItems = [
  { path: '/', icon: Home, label: 'Dashboard' },
  { path: '/clientes', icon: Users, label: 'Clientes' },
  { path: '/agenda', icon: Calendar, label: 'Agenda' },
  { path: '/servicos', icon: Settings, label: 'Serviços' },
  { path: '/estoque', icon: Package, label: 'Estoque', requiresManager: true },
  {
    path: '/financeiro',
    icon: DollarSign,
    label: 'Financeiro',
    requiresManager: true,
  },
];

export function BottomNavigation() {
  const location = useLocation();
  const { hasManagerAccess } = usePermissions();

  const navItems = allNavItems.filter((item) => {
    if (item.requiresManager && !hasManagerAccess()) {
      return false;
    }
    return true;
  });

  return (
    <nav className='fixed bottom-0 left-0 right-0 md:hidden z-50'>
      <div className='mx-4 mb-4'>
        <div className='bg-white/80 backdrop-blur-lg border border-gray-200 rounded-2xl shadow-lg py-2 px-4'>
          <div className='flex justify-around'>
            {navItems.map(({ path, icon: Icon, label }) => {
              const isActive = location.pathname === path;
              return (
                <Link
                  key={path}
                  to={path}
                  className={cn(
                    'flex flex-col items-center py-2 px-3 rounded-xl transition-all duration-200',
                    isActive
                      ? 'text-blue-600 bg-blue-50/80 scale-110'
                      : 'text-gray-600 hover:text-blue-600 hover:bg-gray-50/80'
                  )}
                >
                  <Icon
                    className={cn('w-5 h-5 mb-1', isActive && 'text-blue-600')}
                  />
                  <span className='text-xs font-medium'>{label}</span>
                </Link>
              );
            })}
          </div>
        </div>
      </div>
    </nav>
  );
}
