
import { useState, useEffect } from "react";
import { Link } from "react-router-dom";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { LoadingSpinner } from "../components/ui/LoadingSpinner";
import { Plus, Calendar, Clock, User, Car, CheckCircle } from "lucide-react";
import { toast } from "sonner";

const SERVICOS_MOCK = [
  {
    id: 1,
    cliente: { nome: "José Silva", placa: "ABC-1234", telefone: "11999999999" },
    servicos: ["Troca de Óleo", "Filtro de Óleo"],
    valor: 70.00,
    status: "Agendado",
    dataHora: "2024-01-15T09:00:00",
    tempoEstimado: 25,
    observacoes: "Cliente prefere óleo sintético"
  },
  {
    id: 2,
    cliente: { nome: "Maria Santos", placa: "XYZ-5678", telefone: "11888888888" },
    servicos: ["Verificação de Fluidos"],
    valor: 20.00,
    status: "Em Andamento",
    dataHora: "2024-01-15T10:30:00",
    tempoEstimado: 10,
    observacoes: ""
  },
  {
    id: 3,
    cliente: { nome: "Carlos Oliveira", placa: "DEF-9012", telefone: "11777777777" },
    servicos: ["Troca de Filtro de Ar", "Troca de Filtro de Combustível"],
    valor: 90.00,
    status: "Concluído",
    dataHora: "2024-01-15T08:00:00",
    tempoEstimado: 25,
    observacoes: "Filtros muito sujos, recomendado retorno em 6 meses"
  }
];

export default function Servicos() {
  const [servicos, setServicos] = useState(SERVICOS_MOCK);
  const [filtroStatus, setFiltroStatus] = useState("todos");
  const [loading, setLoading] = useState(false);

  const servicosFiltrados = servicos.filter(servico => {
    if (filtroStatus === "todos") return true;
    return servico.status.toLowerCase() === filtroStatus.toLowerCase();
  });

  const handleChangeStatus = async (servicoId: number, novoStatus: string) => {
    try {
      setLoading(true);
      
      // Simular chamada API
      await new Promise(resolve => setTimeout(resolve, 500));
      
      setServicos(prev => prev.map(s => 
        s.id === servicoId ? { ...s, status: novoStatus } : s
      ));
      
      toast.success(`Status alterado para ${novoStatus}!`);
    } catch (error) {
      toast.error("Erro ao alterar status");
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

  const formatDateTime = (dateTime: string) => {
    return new Date(dateTime).toLocaleString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case "Agendado":
        return "bg-blue-100 text-blue-700";
      case "Em Andamento":
        return "bg-yellow-100 text-yellow-700";
      case "Concluído":
        return "bg-green-100 text-green-700";
      case "Cancelado":
        return "bg-red-100 text-red-700";
      default:
        return "bg-gray-100 text-gray-700";
    }
  };

  const estatisticas = {
    total: servicos.length,
    agendados: servicos.filter(s => s.status === "Agendado").length,
    emAndamento: servicos.filter(s => s.status === "Em Andamento").length,
    concluidos: servicos.filter(s => s.status === "Concluído").length,
    faturamento: servicos.filter(s => s.status === "Concluído").reduce((acc, s) => acc + s.valor, 0)
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Serviços</h1>
          <p className="text-gray-600">Gerencie os atendimentos do dia</p>
        </div>
        <Button asChild>
          <Link to="/servico/novo">
            <Plus className="w-4 h-4 mr-2" />
            Novo Serviço
          </Link>
        </Button>
      </div>

      {/* Estatísticas */}
      <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
        <Card>
          <CardContent className="p-4">
            <div className="text-center">
              <p className="text-2xl font-bold text-gray-900">
                {estatisticas.total}
              </p>
              <p className="text-sm text-gray-600">Total</p>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="p-4">
            <div className="text-center">
              <p className="text-2xl font-bold text-blue-600">
                {estatisticas.agendados}
              </p>
              <p className="text-sm text-gray-600">Agendados</p>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="p-4">
            <div className="text-center">
              <p className="text-2xl font-bold text-yellow-600">
                {estatisticas.emAndamento}
              </p>
              <p className="text-sm text-gray-600">Em Andamento</p>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="p-4">
            <div className="text-center">
              <p className="text-2xl font-bold text-green-600">
                {estatisticas.concluidos}
              </p>
              <p className="text-sm text-gray-600">Concluídos</p>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="p-4">
            <div className="text-center">
              <p className="text-2xl font-bold text-green-600">
                {formatCurrency(estatisticas.faturamento)}
              </p>
              <p className="text-sm text-gray-600">Faturamento</p>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Filtros */}
      <Card>
        <CardHeader>
          <CardTitle>Filtrar Serviços</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex gap-4">
            <Select value={filtroStatus} onValueChange={setFiltroStatus}>
              <SelectTrigger className="w-48">
                <SelectValue placeholder="Filtrar por status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="todos">Todos os Status</SelectItem>
                <SelectItem value="agendado">Agendado</SelectItem>
                <SelectItem value="em andamento">Em Andamento</SelectItem>
                <SelectItem value="concluído">Concluído</SelectItem>
                <SelectItem value="cancelado">Cancelado</SelectItem>
              </SelectContent>
            </Select>
            <Button 
              variant="outline" 
              onClick={() => setFiltroStatus("todos")}
            >
              Limpar Filtros
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* Lista de Serviços */}
      <div className="space-y-4">
        {servicosFiltrados.map((servico) => (
          <Card key={servico.id} className="hover:shadow-lg transition-shadow">
            <CardContent className="p-4">
              <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div className="flex-1">
                  <div className="flex items-center gap-3 mb-2">
                    <div className="flex items-center gap-2">
                      <User className="w-4 h-4 text-gray-400" />
                      <span className="font-semibold text-gray-900">
                        {servico.cliente.nome}
                      </span>
                    </div>
                    <Badge variant="outline" className="font-mono">
                      {servico.cliente.placa}
                    </Badge>
                    <Badge className={getStatusColor(servico.status)}>
                      {servico.status}
                    </Badge>
                  </div>
                  
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-600 mb-2">
                    <div className="flex items-center gap-2">
                      <Calendar className="w-4 h-4" />
                      <span>{formatDateTime(servico.dataHora)}</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <Clock className="w-4 h-4" />
                      <span>{servico.tempoEstimado} min estimado</span>
                    </div>
                  </div>
                  
                  <div className="flex items-center gap-2 text-sm text-gray-600 mb-2">
                    <Car className="w-4 h-4" />
                    <span>{servico.servicos.join(", ")}</span>
                  </div>
                  
                  {servico.observacoes && (
                    <p className="text-sm text-gray-600 italic">
                      {servico.observacoes}
                    </p>
                  )}
                </div>
                
                <div className="flex flex-col items-end gap-2">
                  <p className="text-2xl font-bold text-green-600">
                    {formatCurrency(servico.valor)}
                  </p>
                  
                  <div className="flex gap-2">
                    {servico.status === "Agendado" && (
                      <Button
                        size="sm"
                        onClick={() => handleChangeStatus(servico.id, "Em Andamento")}
                        disabled={loading}
                      >
                        Iniciar
                      </Button>
                    )}
                    {servico.status === "Em Andamento" && (
                      <Button
                        size="sm"
                        onClick={() => handleChangeStatus(servico.id, "Concluído")}
                        disabled={loading}
                        className="bg-green-600 hover:bg-green-700"
                      >
                        <CheckCircle className="w-4 h-4 mr-1" />
                        Concluir
                      </Button>
                    )}
                    <Button
                      variant="outline"
                      size="sm"
                      asChild
                    >
                      <Link to={`/servico/${servico.id}`}>
                        Ver Detalhes
                      </Link>
                    </Button>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>

      {servicosFiltrados.length === 0 && (
        <div className="text-center py-12">
          <Calendar className="w-12 h-12 text-gray-400 mx-auto mb-4" />
          <h3 className="text-lg font-medium text-gray-900 mb-2">
            Nenhum serviço encontrado
          </h3>
          <p className="text-gray-600 mb-4">
            {filtroStatus === "todos" 
              ? "Não há serviços cadastrados ainda"
              : `Não há serviços com status "${filtroStatus}"`
            }
          </p>
          <Button asChild>
            <Link to="/servico/novo">
              <Plus className="w-4 h-4 mr-2" />
              Registrar Primeiro Serviço
            </Link>
          </Button>
        </div>
      )}
    </div>
  );
}
