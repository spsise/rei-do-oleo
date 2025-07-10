import {
  ChartBarIcon,
  CheckCircleIcon,
  ClockIcon,
  CurrencyDollarIcon,
  ExclamationTriangleIcon,
} from '@heroicons/react/24/outline';
import React from 'react';

interface AdditionalMetricsProps {
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

export const AdditionalMetrics: React.FC<AdditionalMetricsProps> = ({
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
    const completionRate =
      stats.total_services > 0
        ? (stats.completed_services_today / stats.total_services) * 100
        : 0;
    const stockAlertPercentage =
      stats.total_products > 0
        ? (stats.low_stock_products / stats.total_products) * 100
        : 0;

    return {
      avgRevenuePerService,
      avgVehiclesPerClient,
      completionRate,
      stockAlertPercentage,
    };
  };

  const metrics = calculateMetrics();

  if (loading) {
    return (
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {[1, 2, 3, 4].map((i) => (
          <div
            key={i}
            className="bg-white rounded-xl shadow-sm border border-gray-200 p-6 animate-pulse"
          >
            <div className="space-y-3">
              <div className="h-4 bg-gray-200 rounded w-3/4"></div>
              <div className="h-8 bg-gray-200 rounded w-1/2"></div>
            </div>
          </div>
        ))}
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <h3 className="text-lg font-semibold text-gray-900">
        Métricas Adicionais
      </h3>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {/* Receita Média por Serviço */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">
                Receita Média/Serviço
              </p>
              <p className="text-2xl font-bold text-gray-900">
                {formatCurrency(metrics.avgRevenuePerService)}
              </p>
            </div>
            <div className="p-3 rounded-xl bg-blue-100 text-blue-600">
              <CurrencyDollarIcon className="h-6 w-6" />
            </div>
          </div>
        </div>

        {/* Média de Veículos por Cliente */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">
                Veículos/Cliente
              </p>
              <p className="text-2xl font-bold text-gray-900">
                {metrics.avgVehiclesPerClient.toFixed(1)}
              </p>
            </div>
            <div className="p-3 rounded-xl bg-green-100 text-green-600">
              <ChartBarIcon className="h-6 w-6" />
            </div>
          </div>
        </div>

        {/* Taxa de Conclusão */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">
                Taxa de Conclusão
              </p>
              <p className="text-2xl font-bold text-gray-900">
                {metrics.completionRate.toFixed(1)}%
              </p>
            </div>
            <div className="p-3 rounded-xl bg-purple-100 text-purple-600">
              <CheckCircleIcon className="h-6 w-6" />
            </div>
          </div>
        </div>

        {/* Alerta de Estoque */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">
                Alerta de Estoque
              </p>
              <p className="text-2xl font-bold text-gray-900">
                {metrics.stockAlertPercentage.toFixed(1)}%
              </p>
            </div>
            <div className="p-3 rounded-xl bg-red-100 text-red-600">
              <ExclamationTriangleIcon className="h-6 w-6" />
            </div>
          </div>
        </div>
      </div>

      {/* Status dos Serviços */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">
                Serviços Completados
              </p>
              <p className="text-2xl font-bold text-green-600">
                {stats.completed_services_today}
              </p>
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
                Serviços Pendentes
              </p>
              <p className="text-2xl font-bold text-yellow-600">
                {stats.pending_services}
              </p>
            </div>
            <div className="p-3 rounded-xl bg-yellow-100 text-yellow-600">
              <ClockIcon className="h-6 w-6" />
            </div>
          </div>
        </div>

        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">
                Produtos em Baixa
              </p>
              <p className="text-2xl font-bold text-red-600">
                {stats.low_stock_products}
              </p>
            </div>
            <div className="p-3 rounded-xl bg-red-100 text-red-600">
              <ExclamationTriangleIcon className="h-6 w-6" />
            </div>
          </div>
        </div>
      </div>

      {/* Insights */}
      <div className="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
        <h4 className="text-lg font-semibold text-gray-900 mb-4">
          Insights do Sistema
        </h4>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div className="space-y-2">
            <p className="text-sm text-gray-700">
              <span className="font-medium">Clientes ativos:</span>{' '}
              {stats.total_clients} clientes cadastrados
            </p>
            <p className="text-sm text-gray-700">
              <span className="font-medium">Veículos registrados:</span>{' '}
              {stats.total_vehicles} veículos
            </p>
            <p className="text-sm text-gray-700">
              <span className="font-medium">Produtos cadastrados:</span>{' '}
              {stats.total_products} produtos
            </p>
          </div>
          <div className="space-y-2">
            <p className="text-sm text-gray-700">
              <span className="font-medium">Receita total:</span>{' '}
              {formatCurrency(stats.total_revenue)}
            </p>
            <p className="text-sm text-gray-700">
              <span className="font-medium">Serviços realizados:</span>{' '}
              {stats.total_services} serviços
            </p>
            <p className="text-sm text-gray-700">
              <span className="font-medium">Receita mensal:</span>{' '}
              {formatCurrency(stats.revenue_this_month)}
            </p>
          </div>
        </div>
      </div>
    </div>
  );
};
