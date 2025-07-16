import {
  ChartBarIcon,
  CheckCircleIcon,
  ClockIcon,
  CubeIcon,
  CurrencyDollarIcon,
  ExclamationTriangleIcon,
  TruckIcon,
  UsersIcon,
  WrenchScrewdriverIcon,
  XCircleIcon,
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
    recent_services?: Array<{
      id: number;
      service_number: string;
      client_name: string;
      vehicle_plate: string;
      status: string;
      total: string;
      created_at: string;
    }>;
    top_products?: Array<{
      id: number;
      name: string;
      sales_count: number;
      revenue: number;
      quantity_sold: number;
      category: string;
    }>;
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

  const calculateMetrics = () => {
    const avgRevenuePerService =
      stats.total_services > 0 ? stats.total_revenue / stats.total_services : 0;
    const avgVehiclesPerClient =
      stats.total_clients > 0 ? stats.total_vehicles / stats.total_clients : 0;
    const stockAlertPercentage =
      stats.total_products > 0
        ? (stats.low_stock_products / stats.total_products) * 100
        : 0;

    // Calcular status dos serviços recentes
    const recentServices = stats.recent_services || [];
    const completedServices = recentServices.filter(
      (s) => s.status === 'completed'
    ).length;
    const cancelledServices = recentServices.filter(
      (s) => s.status === 'cancelled'
    ).length;
    const inProgressServices = recentServices.filter(
      (s) => s.status === 'in_progress'
    ).length;

    return {
      avgRevenuePerService,
      avgVehiclesPerClient,
      stockAlertPercentage,
      completedServices,
      cancelledServices,
      inProgressServices,
    };
  };

  const metrics = calculateMetrics();

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
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
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

        <StatCard
          title="Receita Média/Serviço"
          value={formatCurrency(metrics.avgRevenuePerService)}
          icon={ChartBarIcon}
          color="indigo"
          description="Receita média por serviço realizado"
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

      {/* Service Status Overview */}
      {stats.recent_services && stats.recent_services.length > 0 && (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">
                  Serviços Completados
                </p>
                <p className="text-2xl font-bold text-green-600">
                  {metrics.completedServices}
                </p>
                <p className="text-xs text-gray-500 mt-1">Últimos serviços</p>
              </div>
              <div className="p-3 rounded-xl bg-green-100 text-green-600">
                <CheckCircleIcon className="h-6 w-6" />
              </div>
            </div>
          </div>

          <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">
                  Em Andamento
                </p>
                <p className="text-2xl font-bold text-yellow-600">
                  {metrics.inProgressServices}
                </p>
                <p className="text-xs text-gray-500 mt-1">Últimos serviços</p>
              </div>
              <div className="p-3 rounded-xl bg-yellow-100 text-yellow-600">
                <ClockIcon className="h-6 w-6" />
              </div>
            </div>
          </div>

          <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Cancelados</p>
                <p className="text-2xl font-bold text-red-600">
                  {metrics.cancelledServices}
                </p>
                <p className="text-xs text-gray-500 mt-1">Últimos serviços</p>
              </div>
              <div className="p-3 rounded-xl bg-red-100 text-red-600">
                <XCircleIcon className="h-6 w-6" />
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Insights Panel */}
      <div className="bg-gradient-to-r from-green-50 to-blue-50 rounded-xl p-6 border border-green-200">
        <h4 className="text-lg font-semibold text-gray-900 mb-4">
          Insights do Sistema
        </h4>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div className="space-y-2">
            <p className="text-sm text-gray-700">
              <span className="font-medium">
                Média de veículos por cliente:
              </span>{' '}
              {metrics.avgVehiclesPerClient.toFixed(1)}
            </p>
            <p className="text-sm text-gray-700">
              <span className="font-medium">Receita média por serviço:</span>{' '}
              {formatCurrency(metrics.avgRevenuePerService)}
            </p>
          </div>
          <div className="space-y-2">
            <p className="text-sm text-gray-700">
              <span className="font-medium">Produtos em alerta:</span>{' '}
              {metrics.stockAlertPercentage.toFixed(1)}% do total
            </p>
            <p className="text-sm text-gray-700">
              <span className="font-medium">Serviços pendentes:</span>{' '}
              {stats.pending_services} aguardando
            </p>
          </div>
          <div className="space-y-2">
            <p className="text-sm text-gray-700">
              <span className="font-medium">Receita mensal:</span>{' '}
              {formatCurrency(stats.revenue_this_month)}
            </p>
            <p className="text-sm text-gray-700">
              <span className="font-medium">Serviços este mês:</span>{' '}
              {stats.services_this_month} realizados
            </p>
          </div>
        </div>
      </div>
    </div>
  );
};
