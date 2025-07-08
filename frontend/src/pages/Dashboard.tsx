import { DashboardAlerts } from '../components/Dashboard/DashboardAlerts';
import { DashboardStats } from '../components/Dashboard/DashboardStats';
import { RecentServices } from '../components/Dashboard/RecentServices';
import {
  useDashboardAlerts,
  useDashboardOverview,
} from '../hooks/useDashboard';

export const Dashboard = () => {
  const {
    data: overview,
    isLoading: overviewLoading,
    error: overviewError,
  } = useDashboardOverview();

  const { data: alerts } = useDashboardAlerts();

  if (overviewLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  if (overviewError) {
    return (
      <div className="text-center py-12">
        <p className="text-gray-500">Erro ao carregar dashboard</p>
      </div>
    );
  }

  // Dados mockados para demonstração (serão substituídos pelos dados reais da API)
  const mockOverview = {
    total_clients: 1250,
    total_vehicles: 1890,
    total_services: 3420,
    total_products: 156,
    total_revenue: 125000,
    services_this_month: 156,
    revenue_this_month: 18500,
    low_stock_products: 12,
    pending_services: 8,
    completed_services_today: 15,
    recent_services: [
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
    top_products: [],
    service_trends: [],
    revenue_trends: [],
  };

  const mockAlerts = [
    {
      type: 'low_stock',
      title: 'Produto com estoque baixo',
      message: 'Óleo Motor 5W30 está com apenas 5 unidades em estoque',
      severity: 'warning' as const,
      created_at: '2024-01-15T10:30:00Z',
    },
    {
      type: 'pending_service',
      title: 'Serviço pendente há muito tempo',
      message: 'Serviço #SRV-2024-001 está pendente há 3 dias',
      severity: 'info' as const,
      created_at: '2024-01-15T09:15:00Z',
    },
  ];

  // Usar dados reais se disponíveis, senão usar mockados
  const stats = overview || mockOverview;
  const alertsData = alerts || mockAlerts;

  return (
    <div className="space-y-6">
      {/* Page header */}
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p className="text-gray-600">Visão geral do sistema Rei do Óleo</p>
      </div>

      {/* Stats Cards */}
      <DashboardStats stats={stats} loading={overviewLoading} />

      {/* Additional sections can be added here */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Top Products */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 className="text-lg font-medium text-gray-900 mb-4">
            Produtos Mais Vendidos
          </h3>
          {stats.top_products && stats.top_products.length > 0 ? (
            <div className="space-y-3">
              {stats.top_products.map(
                (product: {
                  id: number;
                  name: string;
                  sales_count: number;
                  revenue: number;
                }) => (
                  <div
                    key={product.id}
                    className="flex items-center justify-between"
                  >
                    <div>
                      <p className="text-sm font-medium text-gray-900">
                        {product.name}
                      </p>
                      <p className="text-xs text-gray-500">
                        {product.sales_count} vendas
                      </p>
                    </div>
                    <p className="text-sm font-medium text-gray-900">
                      R$ {product.revenue.toFixed(2)}
                    </p>
                  </div>
                )
              )}
            </div>
          ) : (
            <p className="text-gray-500 text-sm">
              Nenhum produto vendido ainda
            </p>
          )}
        </div>

        {/* Quick Actions */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 className="text-lg font-medium text-gray-900 mb-4">
            Ações Rápidas
          </h3>
          <div className="space-y-3">
            <button className="w-full text-left p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
              <p className="text-sm font-medium text-gray-900">Novo Serviço</p>
              <p className="text-xs text-gray-500">Criar um novo serviço</p>
            </button>
            <button className="w-full text-left p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
              <p className="text-sm font-medium text-gray-900">Novo Cliente</p>
              <p className="text-xs text-gray-500">Cadastrar novo cliente</p>
            </button>
            <button className="w-full text-left p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">
              <p className="text-sm font-medium text-gray-900">Relatório</p>
              <p className="text-xs text-gray-500">Gerar relatório de vendas</p>
            </button>
          </div>
        </div>
      </div>

      {/* Content Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Recent Services - Takes 2 columns */}
        <div className="lg:col-span-2">
          <RecentServices services={stats.recent_services} />
        </div>

        {/* Alerts - Takes 1 column */}
        <div>
          <DashboardAlerts alerts={alertsData} />
        </div>
      </div>
    </div>
  );
};
