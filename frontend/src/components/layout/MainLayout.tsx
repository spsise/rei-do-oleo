import { SidebarProvider } from '@/contexts/SidebarContext';
import { Outlet } from 'react-router-dom';
import { BottomNavigation } from './BottomNavigation';
import { Header } from './Header';
import { Sidebar } from './Sidebar';

export function MainLayout() {
  return (
    <SidebarProvider>
      <div className='min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex'>
        <Sidebar />

        <div className='flex-1 flex flex-col'>
          <Header />

          <main className='flex-1 p-4 md:p-8 pb-20 md:pb-8 overflow-auto'>
            <div className='max-w-7xl mx-auto'>
              <Outlet />
            </div>
          </main>
        </div>

        <BottomNavigation />
      </div>
    </SidebarProvider>
  );
}
