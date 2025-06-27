
import { useState, useEffect } from "react";
import { Link } from "react-router-dom";
import { useAuthStore } from "../stores/authStore";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { LoadingSpinner } from "../components/ui/LoadingSpinner";
import { 
  Calendar, 
  Clock, 
  Users, 
  TrendingUp, 
  Car, 
  CheckCircle, 
  AlertCircle,
  Plus,
  ArrowRight
} from "lucide-react";
import { toast } from "sonner";

interface DashboardData {
  servicosHoje: {
    total: number;
    concluidos: number;
    emAndamento: number;
    agendados: number;
  };
  clientesAtendidos: number;
  faturamentoDia: number;
  tempoMedio: number;
  servicosRecentes: Array<{
    id: number;
    cliente: string;
    placa: string;
    servico: string;
    valor: number;
    status: string;
    horario: string;
  }>;
  alertas: Array<{
    id: number;
    tipo: 'info' | 'warning' | 'error';
    titulo: string;
    mensagem: string;
  }>;
}

export default function Dashboard() {
  const { user } = useAuthStore();
  const [data, setData] = useState<DashboardData | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    carregarDashboard();
  }, []);

  const carregarDashboard = async () => {
    try {
      setLoading(true);
      
      // Simular carregamento de dados
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      const dashboardData: DashboardData = {
        servicosHoje: {
          total: 15,
          concluidos: 8,
          emAndamento: 2,
          agendados: 5
        },
        clientesAtendidos: 12,
        faturamentoDia: 1580.00,
        tempoMedio: 28,
        servicosRecentes: [
          {
            id: 1,
            cliente: "José Silva",
            placa: "ABC-1234",
            servico: "Troca de Óleo",
            valor: 45.00,
            status: "Concluído",
            horario: "14:30"
          },
          {
            id: 2,
            cliente: "Maria Santos",
            placa: "XYZ-5678",
            servico: "Filtro de Ar",
            valor: 35.00,
            status: "Em Andamento",
            horario: "15:15"
          },
          {
            id: 3,
            cliente: "Carlos Oliveira",
            placa: "DEF-9012",
            servico: "Verificação de Fluidos",
            valor: 20.00,
            status: "Agendado",
            horario: "16:00"
          }
        ],
        alertas: [
          {
            id: 1,
            tipo: 'warning',
            titulo: 'Estoque Baixo',
            mensagem: 'Óleo sintético com apenas 2 unidades restantes'
          },
          {
            id: 2,
            tipo: 'info',
            titulo: 'Meta Mensal',
            mensagem: 'Faltam R$ 1.420,00 para atingir a meta de janeiro'
          }
        ]
      };
      
      setData(dashboardData);
    } catch (error) {
      toast.error("Erro ao carregar dados do dashboard");
    } finally {
      setLoading(false);
    }
  };

  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    }).format(value);
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case "Agendado":
        return "bg-blue-100 text-blue-700";
      case "Em Andamento":
        return "bg-yellow-100 text-yellow-700";
      case "Concluído":
        return "bg-green-100 text-green-700";
      default:
        return "bg-gray-100 text-gray-700";
    }
  };

  const getAlertIcon = (tipo: string) => {
    switch (tipo) {
      case 'warning':
        return <AlertCircle className="w-4 h-4 text-yellow-600" />;
      case 'error':
        return <AlertCircle className="w-4 h-4 text-red-600" />;
      default:
        return <AlertCircle className="w-4 h-4 text-blue-600" />;
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <LoadingSpinner size="lg" />
      </div>
    );
  }

  if (!data) {
    return (
      <div className="text-center py-12">
        <h3 className="text-lg font-medium text-gray-900 mb-2">
          Erro ao carregar dashboard
        </h3>
        <Button onClick={carregarDashboard}>
          Tentar Novamente
        </Button>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-900">
          Bem-vindo, {user?.nome}!
        </h1>
        <p className="text-gray-600">
          Aqui está o resumo do seu negócio hoje - {new Date().toLocaleDateString('pt-BR')}
        </p>
      </div>

      {/* Métricas Principais */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <Card className="bg-gradient-to-r from-blue-500 to-blue-600 text-white">
          <CardContent className="p-4">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-blue-100 text-sm">Serviços Hoje</p>
                <p className="text-3xl font-bold">{data.servicosHoje.total}</p>
                <p className="text-blue-100 text-xs">
                  {data.servicosHoje.concluidos} concluídos
                </p>
              </div>
              <Calendar className="w-8 h-8 text-blue-200" />
            </div>
          </CardContent>
        </Card>

        <Card className="bg-gradient-to-r from-green-500 to-green-600 text-white">
          <CardContent className="p-4">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-green-100 text-sm">Clientes Atendidos</p>
                <p className="text-3xl font-bold">{data.clientesAtendidos}</p>
                <p className="text-green-100 text-xs">
                  clientes únicos
                </p>
              </div>
              <Users className="w-8 h-8 text-green-200" />
            </div>
          </CardContent>
        </Card>

        <Card className="bg-gradient-to-r from-purple-500 to-purple-600 text-white">
          <CardContent className="p-4">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-purple-100 text-sm">Faturamento</p>
                <p className="text-3xl font-bold">
                  {formatCurrency(data.faturamentoDia).replace('R$', '').trim()}
                </p>
                <p className="text-purple-100 text-xs">
                  receita do dia
                </p>
              </div>
              <TrendingUp className="w-8 h-8 text-purple-200" />
            </div>
          </CardContent>
        </Card>

        <Card className="bg-gradient-to-r from-orange-500 to-orange-600 text-white">
          <CardContent className="p-4">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-orange-100 text-sm">Tempo Médio</p>
                <p className="text-3xl font-bold">{data.tempoMedio}</p>
                <p className="text-orange-100 text-xs">
                  minutos por serviço
                </p>
              </div>
              <Clock className="w-8 h-8 text-orange-200" />
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Status dos Serviços */}
      <Card>
        <CardHeader>
          <CardTitle>Status dos Serviços Hoje</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div className="text-center p-4 bg-blue-50 rounded-lg">
              <p className="text-2xl font-bold text-blue-600">
                {data.servicosHoje.agendados}
              </p>
              <p className="text-sm text-blue-600">Agendados</p>
            </div>
            <div className="text-center p-4 bg-yellow-50 rounded-lg">
              <p className="text-2xl font-bold text-yellow-600">
                {data.servicosHoje.emAndamento}
              </p>
              <p className="text-sm text-yellow-600">Em Andamento</p>
            </div>
            <div className="text-center p-4 bg-green-50 rounded-lg">
              <p className="text-2xl font-bold text-green-600">
                {data.servicosHoje.concluidos}
              </p>
              <p className="text-sm text-green-600">Concluídos</p>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Alertas */}
      {data.alertas.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle>Alertas Importantes</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {data.alertas.map((alerta) => (
                <div key={alerta.id} className="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                  {getAlertIcon(alerta.tipo)}
                  <div className="flex-1">
                    <h4 className="font-medium text-gray-900">{alerta.titulo}</h4>
                    <p className="text-sm text-gray-600">{alerta.mensagem}</p>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      )}

      {/* Ações Rápidas */}
      <Card>
        <CardHeader>
          <CardTitle>Ações Rápidas</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <Button asChild className="h-16 flex-col">
              <Link to="/servico/novo">
                <Plus className="w-6 h-6 mb-1" />
                Novo Serviço
              </Link>
            </Button>
            <Button variant="outline" asChild className="h-16 flex-col">
              <Link to="/cliente/novo">
                <Users className="w-6 h-6 mb-1" />
                Novo Cliente
              </Link>
            </Button>
            <Button variant="outline" asChild className="h-16 flex-col">
              <Link to="/servicos">
                <Calendar className="w-6 h-6 mb-1" />
                Ver Agenda
              </Link>
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* Serviços Recentes */}
      <Card>
        <CardHeader>
          <div className="flex justify-between items-center">
            <CardTitle>Serviços Recentes</CardTitle>
            <Button variant="outline" size="sm" asChild>
              <Link to="/servicos">
                Ver Todos
                <ArrowRight className="w-4 h-4 ml-1" />
              </Link>
            </Button>
          </div>
        </CardHeader>
        <CardContent>
          <div className="space-y-3">
            {data.servicosRecentes.map((servico) => (
              <div key={servico.id} className="flex items-center justify-between p-3 border rounded-lg">
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <Car className="w-5 h-5 text-blue-600" />
                  </div>
                  <div>
                    <p className="font-medium text-gray-900">{servico.cliente}</p>
                    <div className="flex items-center gap-2 text-sm text-gray-600">
                      <span>{servico.placa}</span>
                      <span>•</span>
                      <span>{servico.servico}</span>
                      <span>•</span>
                      <span>{servico.horario}</span>
                    </div>
                  </div>
                </div>
                <div className="flex items-center gap-3">
                  <span className="font-bold text-green-600">
                    {formatCurrency(servico.valor)}
                  </span>
                  <Badge className={getStatusColor(servico.status)}>
                    {servico.status}
                  </Badge>
                </div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
