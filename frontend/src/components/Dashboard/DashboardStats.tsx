import {
  CheckCircleIcon,
  ClockIcon,
  CubeIcon,
  CurrencyDollarIcon,
  ExclamationTriangleIcon,
  TruckIcon,
  UsersIcon,
  WrenchScrewdriverIcon,
} from '@heroicons/react/24/outline';
import React from 'react';
import StatCard from '../ui/StatCard';

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
  loading?: boolean;
}

export const DashboardStats: React.FC<DashboardStatsProps> = ({
  stats,
  loading = false,
}) => {
  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL',
    }).format(value);
  };

  return (
    <div className="space-y-6">
      {/* Main Stats Row */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard
          title="Total de Clientes"
          value={stats.total_clients.toLocaleString('pt-BR')}
          icon={UsersIcon}
          color="blue"
          change="+12% este mês"
          changeType="up"
          description="Número total de clientes cadastrados no sistema"
          loading={loading}
        />

        <StatCard
          title="Total de Veículos"
          value={stats.total_vehicles.toLocaleString('pt-BR')}
          icon={TruckIcon}
          color="green"
          change="+8% este mês"
          changeType="up"
          description="Total de veículos registrados no sistema"
          loading={loading}
        />

        <StatCard
          title="Serviços Hoje"
          value={stats.completed_services_today.toLocaleString('pt-BR')}
          icon={CheckCircleIcon}
          color="purple"
          change="+15% vs ontem"
          changeType="up"
          description="Serviços completados hoje"
          loading={loading}
        />

        <StatCard
          title="Receita Hoje"
          value={formatCurrency(stats.total_revenue)}
          icon={CurrencyDollarIcon}
          color="green"
          change="+18% vs ontem"
          changeType="up"
          description="Receita total gerada hoje"
          loading={loading}
        />
      </div>

      {/* Secondary Stats Row */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard
          title="Serviços Este Mês"
          value={stats.services_this_month.toLocaleString('pt-BR')}
          icon={WrenchScrewdriverIcon}
          color="indigo"
          description="Total de serviços realizados este mês"
          loading={loading}
        />

        <StatCard
          title="Receita Este Mês"
          value={formatCurrency(stats.revenue_this_month)}
          icon={CurrencyDollarIcon}
          color="green"
          description="Receita total gerada este mês"
          loading={loading}
        />

        <StatCard
          title="Produtos em Estoque Baixo"
          value={stats.low_stock_products}
          icon={ExclamationTriangleIcon}
          color="red"
          change="Atenção necessária"
          changeType="down"
          description="Produtos com estoque abaixo do mínimo"
          loading={loading}
        />

        <StatCard
          title="Serviços Pendentes"
          value={stats.pending_services}
          icon={ClockIcon}
          color="yellow"
          description="Serviços aguardando execução"
          loading={loading}
        />
      </div>

      {/* Additional Metrics Row */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <StatCard
          title="Total de Produtos"
          value={stats.total_products.toLocaleString('pt-BR')}
          icon={CubeIcon}
          color="pink"
          description="Total de produtos cadastrados no sistema"
          loading={loading}
        />

        <StatCard
          title="Total de Serviços"
          value={stats.total_services.toLocaleString('pt-BR')}
          icon={WrenchScrewdriverIcon}
          color="blue"
          description="Total de serviços realizados no sistema"
          loading={loading}
        />

        <div className="bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl p-6 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-blue-100">
                Taxa de Conversão
              </p>
              <p className="text-3xl font-bold">94.2%</p>
              <p className="text-sm text-blue-100 mt-1">
                +2.1% vs mês anterior
              </p>
            </div>
            <div className="p-3 rounded-xl bg-white/20">
              <CheckCircleIcon className="h-7 w-7 text-white" />
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
