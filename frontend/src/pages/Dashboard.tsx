import {
    ArrowDownIcon,
    ArrowUpIcon,
    CurrencyDollarIcon,
    TruckIcon,
    UsersIcon,
    WrenchScrewdriverIcon,
} from '@heroicons/react/24/outline';
import { useQuery } from '@tanstack/react-query';
import { serviceService } from '../services';

interface DashboardStats {
  totalClients: number;
  totalVehicles: number;
  totalServices: number;
  totalRevenue: number;
  servicesThisMonth: number;
  revenueThisMonth: number;
  recentServices: Array<{
    id: number;
    service_number: string;
    client_name: string;
    vehicle_plate: string;
    status: string;
    total: number;
    created_at: string;
  }>;
}

const StatCard = ({
  title,
  value,
  icon: Icon,
  change,
  changeType = 'neutral',
}: {
  title: string;
  value: string | number;
  icon: React.ComponentType<{ className?: string }>;
  change?: string;
  changeType?: 'up' | 'down' | 'neutral';
}) => (
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
      <div className="p-3 bg-brand-50 rounded-lg">
        <Icon className="h-6 w-6 text-brand-600" />
      </div>
    </div>
  </div>
);

const RecentServicesCard = ({
  services,
}: {
  services: DashboardStats['recentServices'];
}) => (
  <div className="bg-white rounded-lg shadow-sm border border-gray-200">
    <div className="px-6 py-4 border-b border-gray-200">
      <h3 className="text-lg font-medium text-gray-900">Serviços Recentes</h3>
    </div>
    <div className="divide-y divide-gray-200">
      {services.map((service) => (
        <div key={service.id} className="px-6 py-4">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-900">
                #{service.service_number}
              </p>
              <p className="text-sm text-gray-600">{service.client_name}</p>
              <p className="text-xs text-gray-500">{service.vehicle_plate}</p>
            </div>
            <div className="text-right">
              <p className="text-sm font-medium text-gray-900">
                R$ {service.total.toFixed(2)}
              </p>
              <span
                className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${
                  service.status === 'completed'
                    ? 'bg-green-100 text-green-800'
                    : service.status === 'in_progress'
                      ? 'bg-yellow-100 text-yellow-800'
                      : 'bg-gray-100 text-gray-800'
                }`}
              >
                {service.status === 'completed'
                  ? 'Concluído'
                  : service.status === 'in_progress'
                    ? 'Em Andamento'
                    : 'Pendente'}
              </span>
            </div>
          </div>
        </div>
      ))}
    </div>
  </div>
);

export const Dashboard = () => {
  const {
    data: _stats,
    isLoading,
    error,
  } = useQuery({
    queryKey: ['dashboard-stats'],
    queryFn: async () => {
      const response = await serviceService.getDashboardStats();
      if (response.status === 'success' && response.data) {
        return response.data;
      }
      throw new Error(response.message || 'Erro ao carregar estatísticas');
    },
  });

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-600"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="text-center py-12">
        <p className="text-gray-500">Erro ao carregar dashboard</p>
      </div>
    );
  }

  const mockStats: DashboardStats = {
    totalClients: 1250,
    totalVehicles: 1890,
    totalServices: 3420,
    totalRevenue: 125000,
    servicesThisMonth: 156,
    revenueThisMonth: 18500,
    recentServices: [
      {
        id: 1,
        service_number: 'SRV-2024-001',
        client_name: 'João Silva',
        vehicle_plate: 'ABC-1234',
        status: 'completed',
        total: 450.0,
        created_at: '2024-01-15T10:30:00Z',
      },
      {
        id: 2,
        service_number: 'SRV-2024-002',
        client_name: 'Maria Santos',
        vehicle_plate: 'XYZ-5678',
        status: 'in_progress',
        total: 320.0,
        created_at: '2024-01-15T09:15:00Z',
      },
      {
        id: 3,
        service_number: 'SRV-2024-003',
        client_name: 'Pedro Costa',
        vehicle_plate: 'DEF-9012',
        status: 'pending',
        total: 280.0,
        created_at: '2024-01-15T08:45:00Z',
      },
    ],
  };

  return (
    <div className="space-y-6">
      {/* Page header */}
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p className="text-gray-600">Visão geral do sistema Rei do Óleo</p>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard
          title="Total de Clientes"
          value={mockStats.totalClients}
          icon={UsersIcon}
          change="+12%"
          changeType="up"
        />
        <StatCard
          title="Total de Veículos"
          value={mockStats.totalVehicles}
          icon={TruckIcon}
          change="+8%"
          changeType="up"
        />
        <StatCard
          title="Total de Serviços"
          value={mockStats.totalServices}
          icon={WrenchScrewdriverIcon}
          change="+15%"
          changeType="up"
        />
        <StatCard
          title="Receita Total"
          value={`R$ ${mockStats.totalRevenue.toLocaleString()}`}
          icon={CurrencyDollarIcon}
          change="+22%"
          changeType="up"
        />
      </div>

      {/* Monthly Stats */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <StatCard
          title="Serviços Este Mês"
          value={mockStats.servicesThisMonth}
          icon={WrenchScrewdriverIcon}
          change="+5%"
          changeType="up"
        />
        <StatCard
          title="Receita Este Mês"
          value={`R$ ${mockStats.revenueThisMonth.toLocaleString()}`}
          icon={CurrencyDollarIcon}
          change="+18%"
          changeType="up"
        />
      </div>

      {/* Recent Services */}
      <RecentServicesCard services={mockStats.recentServices} />
    </div>
  );
};
