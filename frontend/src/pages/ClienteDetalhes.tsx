
import { useState, useEffect } from "react";
import { useParams, Link } from "react-router-dom";
import { useClienteStore } from "../stores/clienteStore";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { LoadingSpinner } from "../components/ui/LoadingSpinner";
import { ArrowLeft, Phone, Car, Calendar, MapPin, Edit } from "lucide-react";
import { toast } from "sonner";

export default function ClienteDetalhes() {
  const { id } = useParams<{ id: string }>();
  const { buscarPorId, loading } = useClienteStore();
  const [cliente, setCliente] = useState<any>(null);
  const [historico, setHistorico] = useState<any[]>([]);

  useEffect(() => {
    if (id) {
      carregarCliente();
    }
  }, [id]);

  const carregarCliente = async () => {
    try {
      const clienteData = await buscarPorId(id!);
      setCliente(clienteData);
      // Simular histórico de serviços
      setHistorico([
        {
          id: 1,
          data: "2024-01-15",
          servicos: ["Troca de Óleo", "Filtro de Óleo"],
          valor: 70.00,
          status: "Concluído",
          observacoes: "Cliente satisfeito"
        },
        {
          id: 2,
          data: "2023-12-10",
          servicos: ["Verificação de Fluidos"],
          valor: 20.00,
          status: "Concluído",
          observacoes: ""
        }
      ]);
    } catch (error) {
      toast.error("Erro ao carregar dados do cliente");
    }
  };

  const formatTelefone = (telefone: string) => {
    const clean = telefone.replace(/\D/g, '');
    if (clean.length === 11) {
      return clean.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    }
    return clean.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
  };

  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    }).format(value);
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <LoadingSpinner size="lg" />
      </div>
    );
  }

  if (!cliente) {
    return (
      <div className="text-center py-12">
        <h3 className="text-lg font-medium text-gray-900 mb-2">
          Cliente não encontrado
        </h3>
        <Button asChild>
          <Link to="/clientes">
            <ArrowLeft className="w-4 h-4 mr-2" />
            Voltar para Clientes
          </Link>
        </Button>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center gap-4">
        <Button variant="ghost" size="sm" asChild>
          <Link to="/clientes">
            <ArrowLeft className="w-4 h-4" />
          </Link>
        </Button>
        <div className="flex-1">
          <h1 className="text-2xl font-bold text-gray-900">{cliente.nome}</h1>
          <p className="text-gray-600">Detalhes do cliente</p>
        </div>
        <Button asChild>
          <Link to={`/cliente/${id}/editar`}>
            <Edit className="w-4 h-4 mr-2" />
            Editar
          </Link>
        </Button>
      </div>

      {/* Informações do Cliente */}
      <Card>
        <CardHeader>
          <CardTitle>Informações Pessoais</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="flex items-center gap-3">
              <Phone className="w-5 h-5 text-gray-400" />
              <div>
                <p className="text-sm text-gray-500">Telefone</p>
                <p className="font-medium">{formatTelefone(cliente.telefone)}</p>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <Car className="w-5 h-5 text-gray-400" />
              <div>
                <p className="text-sm text-gray-500">Veículo</p>
                <p className="font-medium">{cliente.marca} {cliente.modelo}</p>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <MapPin className="w-5 h-5 text-gray-400" />
              <div>
                <p className="text-sm text-gray-500">Placa</p>
                <Badge variant="outline" className="font-mono">
                  {cliente.placa}
                </Badge>
              </div>
            </div>
            <div className="flex items-center gap-3">
              <Calendar className="w-5 h-5 text-gray-400" />
              <div>
                <p className="text-sm text-gray-500">Quilometragem</p>
                <p className="font-medium">{cliente.quilometragem.toLocaleString()} km</p>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Histórico de Serviços */}
      <Card>
        <CardHeader>
          <div className="flex justify-between items-center">
            <CardTitle>Histórico de Serviços</CardTitle>
            <Button size="sm" asChild>
              <Link to={`/servico/novo?cliente=${id}`}>
                Novo Serviço
              </Link>
            </Button>
          </div>
        </CardHeader>
        <CardContent>
          {historico.length === 0 ? (
            <div className="text-center py-8">
              <p className="text-gray-500">Nenhum serviço registrado</p>
            </div>
          ) : (
            <div className="space-y-4">
              {historico.map((servico) => (
                <div key={servico.id} className="border rounded-lg p-4">
                  <div className="flex justify-between items-start mb-2">
                    <div>
                      <p className="font-medium text-gray-900">
                        {new Date(servico.data).toLocaleDateString('pt-BR')}
                      </p>
                      <p className="text-sm text-gray-500">
                        {servico.servicos.join(', ')}
                      </p>
                    </div>
                    <div className="text-right">
                      <p className="font-bold text-green-600">
                        {formatCurrency(servico.valor)}
                      </p>
                      <Badge variant="outline" className="mt-1">
                        {servico.status}
                      </Badge>
                    </div>
                  </div>
                  {servico.observacoes && (
                    <p className="text-sm text-gray-600 mt-2">
                      {servico.observacoes}
                    </p>
                  )}
                </div>
              ))}
            </div>
          )}
        </CardContent>
      </Card>

      {/* Ações Rápidas */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <Button className="h-12" asChild>
          <Link to={`/servico/novo?cliente=${id}`}>
            Novo Serviço
          </Link>
        </Button>
        <Button variant="outline" className="h-12" asChild>
          <Link to={`/cliente/${id}/editar`}>
            Editar Cliente
          </Link>
        </Button>
        <Button variant="outline" className="h-12">
          Gerar Relatório
        </Button>
      </div>
    </div>
  );
}
