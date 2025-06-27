
import { useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Calendar, Clock, User, Phone, Car, Plus, Filter } from "lucide-react";
import { toast } from "sonner";

interface AgendamentoProps {
  id: string;
  cliente: string;
  telefone: string;
  veiculo: string;
  servicos: string[];
  horario: string;
  status: 'agendado' | 'confirmado' | 'em_andamento' | 'concluido';
  observacoes?: string;
}

export default function Agenda() {
  const [filtroStatus, setFiltroStatus] = useState<string>('todos');
  const [dataSelecionada, setDataSelecionada] = useState(new Date().toISOString().split('T')[0]);

  const agendamentos: AgendamentoProps[] = [
    {
      id: '1',
      cliente: 'João Silva',
      telefone: '(11) 99999-1234',
      veiculo: 'Honda Civic - ABC-1234',
      servicos: ['Troca de Óleo', 'Filtro de Ar'],
      horario: '08:00',
      status: 'confirmado',
      observacoes: 'Cliente prefere óleo sintético'
    },
    {
      id: '2', 
      cliente: 'Maria Santos',
      telefone: '(11) 88888-5678',
      veiculo: 'Toyota Corolla - XYZ-5678',
      servicos: ['Verificação de Fluidos'],
      horario: '10:30',
      status: 'agendado'
    },
    {
      id: '3',
      cliente: 'Pedro Costa',
      telefone: '(11) 77777-9101',
      veiculo: 'Ford Ka - DEF-9101',
      servicos: ['Troca de Óleo', 'Filtro de Óleo', 'Filtro de Combustível'],
      horario: '14:00',
      status: 'em_andamento'
    }
  ];

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'agendado': return 'bg-yellow-100 text-yellow-800';
      case 'confirmado': return 'bg-blue-100 text-blue-800';
      case 'em_andamento': return 'bg-orange-100 text-orange-800';
      case 'concluido': return 'bg-green-100 text-green-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getStatusLabel = (status: string) => {
    switch (status) {
      case 'agendado': return 'Agendado';
      case 'confirmado': return 'Confirmado';
      case 'em_andamento': return 'Em Andamento';
      case 'concluido': return 'Concluído';
      default: return status;
    }
  };

  const atualizarStatus = (id: string, novoStatus: AgendamentoProps['status']) => {
    toast.success(`Status atualizado para ${getStatusLabel(novoStatus)}`);
  };

  const agendamentosFiltrados = agendamentos.filter(ag => 
    filtroStatus === 'todos' || ag.status === filtroStatus
  );

  const proximosHorarios = ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00'];
  const horariosOcupados = agendamentos.map(ag => ag.horario);
  const horariosLivres = proximosHorarios.filter(h => !horariosOcupados.includes(h));

  return (
    <div className="max-w-7xl mx-auto space-y-6">
      <div className="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Agenda</h1>
          <p className="text-gray-600">Gerencie os agendamentos do dia</p>
        </div>
        <Button>
          <Plus className="w-4 h-4 mr-2" />
          Novo Agendamento
        </Button>
      </div>

      {/* Filtros e Data */}
      <div className="flex flex-col md:flex-row gap-4">
        <Card className="flex-1">
          <CardContent className="p-4">
            <div className="flex items-center gap-4">
              <div className="flex items-center gap-2">
                <Calendar className="w-5 h-5 text-gray-500" />
                <input
                  type="date"
                  value={dataSelecionada}
                  onChange={(e) => setDataSelecionada(e.target.value)}
                  className="border rounded-md px-3 py-2"
                />
              </div>
              
              <div className="flex items-center gap-2">
                <Filter className="w-5 h-5 text-gray-500" />
                <select
                  value={filtroStatus}
                  onChange={(e) => setFiltroStatus(e.target.value)}
                  className="border rounded-md px-3 py-2"
                >
                  <option value="todos">Todos</option>
                  <option value="agendado">Agendados</option>
                  <option value="confirmado">Confirmados</option>
                  <option value="em_andamento">Em Andamento</option>
                  <option value="concluido">Concluídos</option>
                </select>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-4">
            <div className="flex items-center gap-4 text-sm">
              <span className="text-gray-600">Total: {agendamentosFiltrados.length}</span>
              <span className="text-green-600">Livres: {horariosLivres.length}</span>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Lista de Agendamentos */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="space-y-4">
          <h2 className="text-lg font-semibold">Agendamentos do Dia</h2>
          {agendamentosFiltrados.map((agendamento) => (
            <Card key={agendamento.id} className="hover:shadow-md transition-shadow">
              <CardContent className="p-4">
                <div className="flex items-start justify-between mb-3">
                  <div className="flex items-center gap-2">
                    <Clock className="w-4 h-4 text-gray-500" />
                    <span className="font-semibold">{agendamento.horario}</span>
                  </div>
                  <Badge className={getStatusColor(agendamento.status)}>
                    {getStatusLabel(agendamento.status)}
                  </Badge>
                </div>

                <div className="space-y-2">
                  <div className="flex items-center gap-2">
                    <User className="w-4 h-4 text-gray-500" />
                    <span className="font-medium">{agendamento.cliente}</span>
                  </div>
                  
                  <div className="flex items-center gap-2">
                    <Phone className="w-4 h-4 text-gray-500" />
                    <span className="text-sm text-gray-600">{agendamento.telefone}</span>
                  </div>

                  <div className="flex items-center gap-2">
                    <Car className="w-4 h-4 text-gray-500" />
                    <span className="text-sm text-gray-600">{agendamento.veiculo}</span>
                  </div>

                  <div className="mt-3">
                    <p className="text-sm font-medium text-gray-700 mb-1">Serviços:</p>
                    <div className="flex flex-wrap gap-1">
                      {agendamento.servicos.map((servico, index) => (
                        <Badge key={index} variant="outline" className="text-xs">
                          {servico}
                        </Badge>
                      ))}
                    </div>
                  </div>

                  {agendamento.observacoes && (
                    <div className="mt-2 p-2 bg-gray-50 rounded text-sm">
                      <strong>Obs:</strong> {agendamento.observacoes}
                    </div>
                  )}

                  <div className="flex gap-2 mt-3">
                    {agendamento.status === 'agendado' && (
                      <Button 
                        size="sm" 
                        variant="outline"
                        onClick={() => atualizarStatus(agendamento.id, 'confirmado')}
                      >
                        Confirmar
                      </Button>
                    )}
                    {agendamento.status === 'confirmado' && (
                      <Button 
                        size="sm"
                        onClick={() => atualizarStatus(agendamento.id, 'em_andamento')}
                      >
                        Iniciar
                      </Button>
                    )}
                    {agendamento.status === 'em_andamento' && (
                      <Button 
                        size="sm" 
                        variant="outline"
                        onClick={() => atualizarStatus(agendamento.id, 'concluido')}
                      >
                        Finalizar
                      </Button>
                    )}
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>

        {/* Horários Disponíveis */}
        <div>
          <h2 className="text-lg font-semibold mb-4">Horários Disponíveis</h2>
          <Card>
            <CardContent className="p-4">
              <div className="grid grid-cols-3 gap-2">
                {proximosHorarios.map((horario) => {
                  const isOcupado = horariosOcupados.includes(horario);
                  return (
                    <Button
                      key={horario}
                      variant={isOcupado ? "secondary" : "outline"}
                      size="sm"
                      disabled={isOcupado}
                      className={isOcupado ? "opacity-50" : "hover:bg-blue-50"}
                    >
                      {horario}
                    </Button>
                  );
                })}
              </div>
              <p className="text-xs text-gray-500 mt-3">
                Clique em um horário livre para criar novo agendamento
              </p>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
