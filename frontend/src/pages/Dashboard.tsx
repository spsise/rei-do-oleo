import {
  ChartBarIcon,
  CogIcon,
  DocumentTextIcon,
  UsersIcon,
} from '@heroicons/react/24/outline';
import React from 'react';
import { useAuth } from '../contexts/AuthContext';

const Dashboard: React.FC = () => {
  const { user } = useAuth();

  const stats = [
    {
      name: 'Total de Usuários',
      value: '1,234',
      icon: UsersIcon,
      change: '+12%',
      changeType: 'positive',
    },
    {
      name: 'Documentos',
      value: '567',
      icon: DocumentTextIcon,
      change: '+8%',
      changeType: 'positive',
    },
    {
      name: 'Relatórios',
      value: '89',
      icon: ChartBarIcon,
      change: '+15%',
      changeType: 'positive',
    },
    {
      name: 'Configurações',
      value: '12',
      icon: CogIcon,
      change: '+3%',
      changeType: 'positive',
    },
  ];

  return (
    <div className='space-y-6'>
      <div>
        <h1 className='text-2xl font-bold text-gray-900 dark:text-white'>
          Dashboard
        </h1>
        <p className='text-gray-600 dark:text-gray-400'>
          Bem-vindo de volta, {user?.name}!
        </p>
      </div>

      <div className='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6'>
        {stats.map((stat) => (
          <div key={stat.name} className='card'>
            <div className='flex items-center'>
              <div className='flex-shrink-0'>
                <stat.icon className='h-8 w-8 text-brand-500' />
              </div>
              <div className='ml-4 flex-1'>
                <p className='text-sm font-medium text-gray-600 dark:text-gray-400'>
                  {stat.name}
                </p>
                <p className='text-2xl font-semibold text-gray-900 dark:text-white'>
                  {stat.value}
                </p>
              </div>
            </div>
            <div className='mt-4'>
              <span className='text-sm text-green-600 dark:text-green-400'>
                {stat.change}
              </span>
              <span className='text-sm text-gray-500 dark:text-gray-400 ml-1'>
                desde o último mês
              </span>
            </div>
          </div>
        ))}
      </div>

      <div className='grid grid-cols-1 lg:grid-cols-2 gap-6'>
        <div className='card'>
          <h3 className='text-lg font-medium text-gray-900 dark:text-white mb-4'>
            Atividades Recentes
          </h3>
          <div className='space-y-4'>
            <div className='flex items-center space-x-3'>
              <div className='w-2 h-2 bg-green-500 rounded-full'></div>
              <span className='text-sm text-gray-600 dark:text-gray-400'>
                Novo usuário registrado
              </span>
            </div>
            <div className='flex items-center space-x-3'>
              <div className='w-2 h-2 bg-blue-500 rounded-full'></div>
              <span className='text-sm text-gray-600 dark:text-gray-400'>
                Documento atualizado
              </span>
            </div>
            <div className='flex items-center space-x-3'>
              <div className='w-2 h-2 bg-yellow-500 rounded-full'></div>
              <span className='text-sm text-gray-600 dark:text-gray-400'>
                Relatório gerado
              </span>
            </div>
          </div>
        </div>

        <div className='card'>
          <h3 className='text-lg font-medium text-gray-900 dark:text-white mb-4'>
            Resumo do Sistema
          </h3>
          <div className='space-y-4'>
            <div className='flex justify-between'>
              <span className='text-sm text-gray-600 dark:text-gray-400'>
                Status do Sistema
              </span>
              <span className='text-sm text-green-600 dark:text-green-400 font-medium'>
                Online
              </span>
            </div>
            <div className='flex justify-between'>
              <span className='text-sm text-gray-600 dark:text-gray-400'>
                Última Atualização
              </span>
              <span className='text-sm text-gray-900 dark:text-white'>
                Há 5 minutos
              </span>
            </div>
            <div className='flex justify-between'>
              <span className='text-sm text-gray-600 dark:text-gray-400'>
                Versão
              </span>
              <span className='text-sm text-gray-900 dark:text-white'>
                v1.0.0
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;
