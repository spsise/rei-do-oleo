
import { useState, useEffect } from "react";
import { useNavigate, useSearchParams } from "react-router-dom";
import { useClienteStore } from "../stores/clienteStore";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Checkbox } from "@/components/ui/checkbox";
import { Textarea } from "@/components/ui/textarea";
import { SearchInput } from "../components/ui/SearchInput";
import { LoadingSpinner } from "../components/ui/LoadingSpinner";
import { Badge } from "@/components/ui/badge";
import { Car, Calculator, Save } from "lucide-react";
import { toast } from "sonner";

const TIPOS_SERVICOS = [
  { id: 1, nome: "Troca de Óleo", preco: 45.00, tempo: 15, categoria: "Lubrificação" },
  { id: 2, nome: "Troca de Filtro de Óleo", preco: 25.00, tempo: 10, categoria: "Filtros" },
  { id: 3, nome: "Troca de Filtro de Ar", preco: 35.00, tempo: 5, categoria: "Filtros" },
  { id: 4, nome: "Troca de Filtro de Combustível", preco: 55.00, tempo: 20, categoria: "Filtros" },
  { id: 5, nome: "Verificação de Fluidos", preco: 20.00, tempo: 10, categoria: "Fluidos" }
];

export default function NovoServico() {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const clienteIdParam = searchParams.get('cliente');
  
  const { buscarPorPlaca, buscarPorId } = useClienteStore();
  
  const [searchPlaca, setSearchPlaca] = useState("");
  const [clienteSelecionado, setClienteSelecionado] = useState<any>(null);
  const [servicosSelecionados, setServicosSelecionados] = useState<number[]>([]);
  const [quilometragem, setQuilometragem] = useState("");
  const [observacoes, setObservacoes] = useState("");
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (clienteIdParam) {
      carregarClientePorId(clienteIdParam);
    }
  }, [clienteIdParam]);

  const carregarClientePorId = async (id: string) => {
    try {
      setLoading(true);
      const cliente = await buscarPorId(id);
      setClienteSelecionado(cliente);
      setQuilometragem(cliente.quilometragem.toString());
    } catch (error) {
      toast.error("Erro ao carregar dados do cliente");
    } finally {
      setLoading(false);
    }
  };

  const handleBuscarCliente = async () => {
    if (!searchPlaca.trim()) {
      toast.error("Digite uma placa para buscar");
      return;
    }

    try {
      setLoading(true);
      const cliente = await buscarPorPlaca(searchPlaca);
      if (cliente) {
        setClienteSelecionado(cliente);
        setQuilometragem(cliente.quilometragem.toString());
        toast.success("Cliente encontrado!");
      } else {
        toast.error("Cliente não encontrado com esta placa");
      }
    } catch (error) {
      toast.error("Erro ao buscar cliente");
    } finally {
      setLoading(false);
    }
  };

  const handleServicoChange = (servicoId: number, checked: boolean) => {
    if (checked) {
      setServicosSelecionados([...servicosSelecionados, servicoId]);
    } else {
      setServicosSelecionados(servicosSelecionados.filter(id => id !== servicoId));
    }
  };

  const calcularTotal = () => {
    return TIPOS_SERVICOS
      .filter(servico => servicosSelecionados.includes(servico.id))
      .reduce((total, servico) => total + servico.preco, 0);
  };

  const calcularTempoTotal = () => {
    return TIPOS_SERVICOS
      .filter(servico => servicosSelecionados.includes(servico.id))
      .reduce((total, servico) => total + servico.tempo, 0);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!clienteSelecionado) {
      toast.error("Selecione um cliente");
      return;
    }

    if (servicosSelecionados.length === 0) {
      toast.error("Selecione pelo menos um serviço");
      return;
    }

    if (!quilometragem.trim()) {
      toast.error("Informe a quilometragem atual");
      return;
    }

    try {
      setLoading(true);
      
      const servicoData = {
        clienteId: clienteSelecionado.id,
        servicos: servicosSelecionados,
        quilometragem: parseInt(quilometragem),
        observacoes,
        valor: calcularTotal(),
        tempoEstimado: calcularTempoTotal(),
        status: 'Agendado'
      };

      // Simular salvamento
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      toast.success("Serviço registrado com sucesso!");
      navigate('/servicos');
    } catch (error) {
      toast.error("Erro ao registrar serviço");
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

  return (
    <div className="max-w-4xl mx-auto space-y-6">
      {/* Header */}
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Novo Serviço</h1>
        <p className="text-gray-600">Registre um novo atendimento</p>
      </div>

      <form onSubmit={handleSubmit} className="space-y-6">
        {/* Busca de Cliente */}
        {!clienteSelecionado && (
          <Card>
            <CardHeader>
              <CardTitle>Buscar Cliente</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex gap-3">
                <SearchInput
                  value={searchPlaca}
                  onChange={setSearchPlaca}
                  placeholder="Digite a placa (ABC-1234)"
                  className="flex-1"
                  onSearch={handleBuscarCliente}
                />
                <Button 
                  type="button" 
                  onClick={handleBuscarCliente} 
                  disabled={loading}
                >
                  {loading ? <LoadingSpinner size="sm" /> : "Buscar"}
                </Button>
              </div>
            </CardContent>
          </Card>
        )}

        {/* Cliente Selecionado */}
        {clienteSelecionado && (
          <Card>
            <CardHeader>
              <div className="flex justify-between items-center">
                <CardTitle>Cliente Selecionado</CardTitle>
                <Button 
                  type="button" 
                  variant="outline" 
                  size="sm"
                  onClick={() => setClienteSelecionado(null)}
                >
                  Trocar Cliente
                </Button>
              </div>
            </CardHeader>
            <CardContent>
              <div className="flex items-center gap-4">
                <div className="flex-1">
                  <h3 className="font-semibold text-gray-900">
                    {clienteSelecionado.nome}
                  </h3>
                  <div className="flex items-center gap-2 text-sm text-gray-600 mt-1">
                    <Car className="w-4 h-4" />
                    <span>{clienteSelecionado.marca} {clienteSelecionado.modelo}</span>
                  </div>
                </div>
                <Badge variant="outline" className="font-mono">
                  {clienteSelecionado.placa}
                </Badge>
              </div>
            </CardContent>
          </Card>
        )}

        {/* Seleção de Serviços */}
        {clienteSelecionado && (
          <Card>
            <CardHeader>
              <CardTitle>Selecionar Serviços</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {TIPOS_SERVICOS.map((servico) => (
                <div key={servico.id} className="flex items-center space-x-3 p-3 border rounded-lg">
                  <Checkbox
                    id={`servico-${servico.id}`}
                    checked={servicosSelecionados.includes(servico.id)}
                    onCheckedChange={(checked) => 
                      handleServicoChange(servico.id, checked as boolean)
                    }
                  />
                  <div className="flex-1">
                    <Label 
                      htmlFor={`servico-${servico.id}`}
                      className="font-medium cursor-pointer"
                    >
                      {servico.nome}
                    </Label>
                    <div className="flex items-center gap-4 text-sm text-gray-600 mt-1">
                      <span>{formatCurrency(servico.preco)}</span>
                      <span>• {servico.tempo} min</span>
                      <Badge variant="secondary" className="text-xs">
                        {servico.categoria}
                      </Badge>
                    </div>
                  </div>
                </div>
              ))}
            </CardContent>
          </Card>
        )}

        {/* Resumo e Detalhes */}
        {clienteSelecionado && servicosSelecionados.length > 0 && (
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Calculator className="w-5 h-5" />
                Resumo do Serviço
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg">
                <div className="text-center">
                  <p className="text-sm text-gray-600">Total</p>
                  <p className="text-2xl font-bold text-green-600">
                    {formatCurrency(calcularTotal())}
                  </p>
                </div>
                <div className="text-center">
                  <p className="text-sm text-gray-600">Tempo Estimado</p>
                  <p className="text-2xl font-bold text-blue-600">
                    {calcularTempoTotal()} min
                  </p>
                </div>
                <div className="text-center">
                  <p className="text-sm text-gray-600">Serviços</p>
                  <p className="text-2xl font-bold text-gray-900">
                    {servicosSelecionados.length}
                  </p>
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="quilometragem">Quilometragem Atual</Label>
                  <Input
                    id="quilometragem"
                    type="number"
                    value={quilometragem}
                    onChange={(e) => setQuilometragem(e.target.value)}
                    placeholder="Ex: 50000"
                    required
                  />
                </div>
                <div>
                  <Label htmlFor="observacoes">Observações</Label>
                  <Textarea
                    id="observacoes"
                    value={observacoes}
                    onChange={(e) => setObservacoes(e.target.value)}
                    placeholder="Observações adicionais..."
                    rows={3}
                  />
                </div>
              </div>
            </CardContent>
          </Card>
        )}

        {/* Botões de Ação */}
        {clienteSelecionado && (
          <div className="flex gap-4">
            <Button
              type="button"
              variant="outline"
              onClick={() => navigate('/servicos')}
              className="flex-1"
            >
              Cancelar
            </Button>
            <Button
              type="submit"
              disabled={loading || servicosSelecionados.length === 0}
              className="flex-1"
            >
              {loading ? (
                <LoadingSpinner size="sm" />
              ) : (
                <>
                  <Save className="w-4 h-4 mr-2" />
                  Registrar Serviço
                </>
              )}
            </Button>
          </div>
        )}
      </form>
    </div>
  );
}
