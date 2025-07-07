import {
  ArrowDownIcon,
  ArrowUpIcon,
  CubeIcon,
  CurrencyDollarIcon,
  ExclamationTriangleIcon,
  TruckIcon,
  UsersIcon,
  WrenchScrewdriverIcon,
} from '@heroicons/react/24/outline';
import React from 'react';

interface StatCardProps {
  title: string;
  value: string | number;
  icon: React.ComponentType<{ className?: string }>;
  change?: string;
  changeType?: 'up' | 'down' | 'neutral';
  color?: 'blue' | 'green' | 'red' | 'yellow' | 'purple';
}

const StatCard: React.FC<StatCardProps> = ({
  title,
  value,
  icon: Icon,
  change,
  changeType = 'neutral',
  color = 'blue',
}) => {
  const colorClasses = {
    blue: 'bg-blue-50 text-blue-600',
    green: 'bg-green-50 text-green-600',
    red: 'bg-red-50 text-red-600',
    yellow: 'bg-yellow-50 text-yellow-600',
    purple: 'bg-purple-50 text-purple-600',
  };

  return (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div className="flex items-center justify-between">
        <div>
          <p className="text-sm font-medium text-gray-600">{title}</p>
          <p className="text-2xl font-bold text-gray-900">{value}</p>
          {change && (
            <div className="flex items-center mt-2">
              {changeType === 'up' ? (
                <ArrowUpIcon className="h-4 w-4 text-green-500" />
              ) : changeType === 'down' ? (
                <ArrowDownIcon className="h-4 w-4 text-red-500" />
              ) : null}
              <span
                className={`text-sm font-medium ml-1 ${
                  changeType === 'up'
                    ? 'text-green-600'
                    : changeType === 'down'
                      ? 'text-red-600'
                      : 'text-gray-600'
                }`}
              >
                {change}
              </span>
            </div>
          )}
        </div>
        <div className={`p-3 rounded-lg ${colorClasses[color]}`}>
          <Icon className="h-6 w-6" />
        </div>
      </div>
    </div>
  );
};

interface DashboardStatsProps {
  stats: {
    total_clients: number;
    total_vehicles: number;
    total_services: number;
    total_products: number;
    total_revenue: number;
    services_this_month: number;
    revenue_this_month: number;
    low_stock_products: number;
    pending_services: number;
    completed_services_today: number;
  };
}

export const DashboardStats: React.FC<DashboardStatsProps> = ({ stats }) => {
  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL',
    }).format(value);
  };

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <StatCard
        title="Total de Clientes"
        value={stats.total_clients.toLocaleString('pt-BR')}
        icon={UsersIcon}
        color="blue"
        change="+12% este mês"
        changeType="up"
      />

      <StatCard
        title="Total de Veículos"
        value={stats.total_vehicles.toLocaleString('pt-BR')}
        icon={TruckIcon}
        color="green"
        change="+8% este mês"
        changeType="up"
      />

      <StatCard
        title="Total de Serviços"
        value={stats.total_services.toLocaleString('pt-BR')}
        icon={WrenchScrewdriverIcon}
        color="purple"
        change="+15% este mês"
        changeType="up"
      />

      <StatCard
        title="Receita Total"
        value={formatCurrency(stats.total_revenue)}
        icon={CurrencyDollarIcon}
        color="green"
        change="+18% este mês"
        changeType="up"
      />

      <StatCard
        title="Serviços Este Mês"
        value={stats.services_this_month.toLocaleString('pt-BR')}
        icon={WrenchScrewdriverIcon}
        color="blue"
      />

      <StatCard
        title="Receita Este Mês"
        value={formatCurrency(stats.revenue_this_month)}
        icon={CurrencyDollarIcon}
        color="green"
      />

      <StatCard
        title="Produtos em Estoque Baixo"
        value={stats.low_stock_products}
        icon={ExclamationTriangleIcon}
        color="red"
        change="Atenção necessária"
        changeType="down"
      />

      <StatCard
        title="Serviços Pendentes"
        value={stats.pending_services}
        icon={CubeIcon}
        color="yellow"
      />
    </div>
  );
};
