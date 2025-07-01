import { Button } from '@/components/ui/button';
import { useSidebar } from '@/contexts/SidebarContext';
import { cn } from '@/lib/utils';
import { Bell, Search } from 'lucide-react';
import { useAuthStore } from '../../stores/authStore';

export function Header() {
  const { user } = useAuthStore();
  const { isCollapsed } = useSidebar();

  return (
    <header className='bg-white border-b border-gray-200 sticky top-0 z-40'>
      <div
        className={cn(
          'flex items-center justify-between h-[73px] px-4 md:px-6',
          'transition-all duration-300',
          isCollapsed ? 'md:ml-20' : 'md:ml-72'
        )}
      >
        <div className='md:hidden'>
          <h1 className='text-lg font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent'>
            Rei do Óleo
          </h1>
        </div>

        <div className='hidden md:flex items-center gap-3 flex-1 max-w-xl'>
          <div className='relative w-full'>
            <Search className='w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2' />
            <input
              type='text'
              placeholder='Buscar...'
              className='w-full pl-10 pr-4 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all bg-white'
            />
          </div>
        </div>

        <div className='flex items-center gap-4'>
          <Button
            variant='ghost'
            size='sm'
            className='relative hover:bg-gray-100 transition-colors rounded-xl'
          >
            <Bell className='w-5 h-5 text-gray-600' />
            <span className='absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white'></span>
          </Button>

          <div className='hidden md:flex items-center gap-3'>
            <div className='text-right'>
              <p className='text-sm font-medium text-gray-900'>{user?.name}</p>
              <p className='text-xs text-gray-500 capitalize font-medium'>
                {user?.highest_role}
              </p>
            </div>
            <div className='w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-sm'>
              <span className='text-sm font-medium text-white'>
                {user?.name?.charAt(0).toUpperCase()}
              </span>
            </div>
          </div>
        </div>
      </div>
    </header>
  );
}
