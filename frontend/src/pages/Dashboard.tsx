import { DashboardAlerts } from '../components/Dashboard/DashboardAlerts';
import { DashboardStats } from '../components/Dashboard/DashboardStats';
import { RecentServices } from '../components/Dashboard/RecentServices';
import { TopProducts } from '../components/Dashboard/TopProducts';
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
        total: '450.00',
        created_at: '2024-01-15T10:30:00Z',
      },
      {
        id: 2,
        service_number: 'SRV-2024-002',
        client_name: 'Maria Santos',
        vehicle_plate: 'XYZ-5678',
        status: 'in_progress',
        total: '320.00',
        created_at: '2024-01-15T09:15:00Z',
      },
      {
        id: 3,
        service_number: 'SRV-2024-003',
        client_name: 'Pedro Costa',
        vehicle_plate: 'DEF-9012',
        status: 'pending',
        total: '280.00',
        created_at: '2024-01-15T08:45:00Z',
      },
    ],
    top_products: [
      {
        id: 1,
        name: 'Óleo Motor 5W30',
        sales_count: 45,
        revenue: 2250.0,
        quantity_sold: 90,
        category: 'Óleos Lubrificantes',
      },
      {
        id: 2,
        name: 'Filtro de Ar',
        sales_count: 32,
        revenue: 1280.0,
        quantity_sold: 64,
        category: 'Filtros',
      },
      {
        id: 3,
        name: 'Pneu 175/70R13',
        sales_count: 28,
        revenue: 4200.0,
        quantity_sold: 28,
        category: 'Pneus',
      },
    ],
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

  // Converter dados da API para o formato esperado pelos componentes
  const convertedStats = {
    ...stats,
    recent_services: stats.recent_services?.map((service) => ({
      ...service,
      total:
        typeof service.total === 'number'
          ? service.total.toString()
          : service.total,
    })),
    top_products: stats.top_products?.map((product) => {
      // Type assertion para lidar com dados da API que podem ter campos opcionais
      const apiProduct = product as {
        id: number;
        name: string;
        sales_count: number;
        revenue: number;
        quantity_sold?: number;
        category?: string;
      };

      return {
        ...product,
        quantity_sold: apiProduct.quantity_sold || 0,
        category: apiProduct.category || 'Sem categoria',
      };
    }),
  };

  return (
    <div className="space-y-6">
      {/* Page header */}
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p className="text-gray-600">Visão geral do sistema Rei do Óleo</p>
      </div>

      {/* Stats Cards */}
      <DashboardStats stats={convertedStats} loading={overviewLoading} />

      {/* Top Products and Quick Actions */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Top Products */}
        <TopProducts
          products={convertedStats.top_products || []}
          loading={overviewLoading}
        />

        {/* Quick Actions */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">
            Ações Rápidas
          </h3>
          <div className="space-y-3">
            <button className="w-full text-left p-4 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors group">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-gray-900 group-hover:text-blue-600">
                    Novo Serviço
                  </p>
                  <p className="text-xs text-gray-500">Criar um novo serviço</p>
                </div>
                <div className="p-2 rounded-lg bg-blue-100 text-blue-600 group-hover:bg-blue-200 transition-colors">
                  <svg
                    className="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                    />
                  </svg>
                </div>
              </div>
            </button>

            <button className="w-full text-left p-4 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors group">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-gray-900 group-hover:text-green-600">
                    Novo Cliente
                  </p>
                  <p className="text-xs text-gray-500">
                    Cadastrar novo cliente
                  </p>
                </div>
                <div className="p-2 rounded-lg bg-green-100 text-green-600 group-hover:bg-green-200 transition-colors">
                  <svg
                    className="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                    />
                  </svg>
                </div>
              </div>
            </button>

            <button className="w-full text-left p-4 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors group">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-gray-900 group-hover:text-purple-600">
                    Relatório
                  </p>
                  <p className="text-xs text-gray-500">
                    Gerar relatório de vendas
                  </p>
                </div>
                <div className="p-2 rounded-lg bg-purple-100 text-purple-600 group-hover:bg-purple-200 transition-colors">
                  <svg
                    className="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                    />
                  </svg>
                </div>
              </div>
            </button>
          </div>
        </div>
      </div>

      {/* Content Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Recent Services - Takes 2 columns */}
        <div className="lg:col-span-2">
          <RecentServices services={convertedStats.recent_services} />
        </div>

        {/* Alerts - Takes 1 column */}
        <div>
          <DashboardAlerts alerts={alertsData} />
        </div>
      </div>
    </div>
  );
};
