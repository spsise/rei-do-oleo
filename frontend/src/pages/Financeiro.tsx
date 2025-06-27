
import { useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { DollarSign, TrendingUp, TrendingDown, Calendar, Plus, Search } from "lucide-react";
import { toast } from "sonner";

interface Transacao {
  id: string;
  tipo: 'receita' | 'despesa';
  categoria: string;
  descricao: string;
  valor: number;
  data: string;
  formaPagamento: string;
  status: 'pago' | 'pendente' | 'vencido';
}

export default function Financeiro() {
  const [filtroTipo, setFiltroTipo] = useState<string>('todos');
  const [filtroMes, setFiltroMes] = useState<string>('2024-01');
  const [busca, setBusca] = useState('');

  const transacoes: Transacao[] = [
    {
      id: '1',
      tipo: 'receita',
      categoria: 'Serviços',
      descricao: 'Troca de óleo - João Silva',
      valor: 65.00,
      data: '2024-01-15',
      formaPagamento: 'Dinheiro',
      status: 'pago'
    },
    {
      id: '2',
      tipo: 'receita',
      categoria: 'Serviços',
      descricao: 'Filtros - Maria Santos',
      valor: 85.00,
      data: '2024-01-15',
      formaPagamento: 'Cartão',
      status: 'pago'
    },
    {
      id: '3',
      tipo: 'despesa',
      categoria: 'Estoque',
      descricao: 'Compra de óleos - Distribuidora ABC',
      valor: 450.00,
      data: '2024-01-14',
      formaPagamento: 'PIX',
      status: 'pago'
    },
    {
      id: '4',
      tipo: 'despesa',
      categoria: 'Operacional',
      descricao: 'Conta de luz',
      valor: 180.00,
      data: '2024-01-10',
      formaPagamento: 'Débito',
      status: 'pago'
    },
    {
      id: '5',
      tipo: 'receita',
      categoria: 'Serviços',
      descricao: 'Serviços diversos - Pedro Costa',
      valor: 120.00,
      data: '2024-01-16',
      formaPagamento: 'PIX',
      status: 'pendente'
    }
  ];

  const receitas = transacoes.filter(t => t.tipo === 'receita');
  const despesas = transacoes.filter(t => t.tipo === 'despesa');
  
  const totalReceitas = receitas.reduce((sum, t) => sum + t.valor, 0);
  const totalDespesas = despesas.reduce((sum, t) => sum + t.valor, 0);
  const lucro = totalReceitas - totalDespesas;

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'pago': return 'bg-green-100 text-green-800';
      case 'pendente': return 'bg-yellow-100 text-yellow-800';
      case 'vencido': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getStatusLabel = (status: string) => {
    switch (status) {
      case 'pago': return 'Pago';
      case 'pendente': return 'Pendente';
      case 'vencido': return 'Vencido';
      default: return status;
    }
  };

  const transacoesFiltradas = transacoes.filter(transacao => {
    const matchTipo = filtroTipo === 'todos' || transacao.tipo === filtroTipo;
    const matchBusca = transacao.descricao.toLowerCase().includes(busca.toLowerCase()) ||
                       transacao.categoria.toLowerCase().includes(busca.toLowerCase());
    return matchTipo && matchBusca;
  });

  const adicionarTransacao = () => {
    toast.success("Transação adicionada com sucesso!");
  };

  return (
    <div className="max-w-7xl mx-auto space-y-6">
      <div className="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Financeiro</h1>
          <p className="text-gray-600">Controle de receitas e despesas</p>
        </div>
        <Button onClick={adicionarTransacao}>
          <Plus className="w-4 h-4 mr-2" />
          Nova Transação
        </Button>
      </div>

      {/* Métricas Resumo */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Card>
          <CardContent className="p-4">
            <div className="flex items-center gap-3">
              <TrendingUp className="w-8 h-8 text-green-600" />
              <div>
                <p className="text-sm text-gray-600">Receitas</p>
                <p className="text-2xl font-bold text-green-600">
                  R$ {totalReceitas.toFixed(2).replace('.', ',')}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-4">
            <div className="flex items-center gap-3">
              <TrendingDown className="w-8 h-8 text-red-600" />
              <div>
                <p className="text-sm text-gray-600">Despesas</p>
                <p className="text-2xl font-bold text-red-600">
                  R$ {totalDespesas.toFixed(2).replace('.', ',')}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-4">
            <div className="flex items-center gap-3">
              <DollarSign className="w-8 h-8 text-blue-600" />
              <div>
                <p className="text-sm text-gray-600">Lucro</p>
                <p className={`text-2xl font-bold ${lucro >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                  R$ {Math.abs(lucro).toFixed(2).replace('.', ',')}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-4">
            <div className="flex items-center gap-3">
              <Calendar className="w-8 h-8 text-purple-600" />
              <div>
                <p className="text-sm text-gray-600">Período</p>
                <p className="text-lg font-bold">Janeiro 2024</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Filtros */}
      <Card>
        <CardContent className="p-4">
          <div className="flex flex-col md:flex-row gap-4">
            <div className="flex-1 relative">
              <Search className="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
              <Input
                placeholder="Buscar transações..."
                value={busca}
                onChange={(e) => setBusca(e.target.value)}
                className="pl-10"
              />
            </div>
            
            <Select value={filtroTipo} onValueChange={setFiltroTipo}>
              <SelectTrigger className="w-40">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="todos">Todos</SelectItem>
                <SelectItem value="receita">Receitas</SelectItem>
                <SelectItem value="despesa">Despesas</SelectItem>
              </SelectContent>
            </Select>

            <input
              type="month"
              value={filtroMes}
              onChange={(e) => setFiltroMes(e.target.value)}
              className="border rounded-md px-3 py-2"
            />
          </div>
        </CardContent>
      </Card>

      {/* Lista de Transações */}
      <Card>
        <CardHeader>
          <CardTitle>Transações Recentes</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            {transacoesFiltradas.map((transacao) => (
              <div 
                key={transacao.id} 
                className="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
              >
                <div className="flex items-center gap-4">
                  <div className={`w-12 h-12 rounded-full flex items-center justify-center ${
                    transacao.tipo === 'receita' ? 'bg-green-100' : 'bg-red-100'
                  }`}>
                    {transacao.tipo === 'receita' ? (
                      <TrendingUp className="w-6 h-6 text-green-600" />
                    ) : (
                      <TrendingDown className="w-6 h-6 text-red-600" />
                    )}
                  </div>
                  
                  <div>
                    <h4 className="font-semibold">{transacao.descricao}</h4>
                    <div className="flex items-center gap-2 text-sm text-gray-600">
                      <span>{transacao.categoria}</span>
                      <span>•</span>
                      <span>{new Date(transacao.data).toLocaleDateString('pt-BR')}</span>
                      <span>•</span>
                      <span>{transacao.formaPagamento}</span>
                    </div>
                  </div>
                </div>

                <div className="text-right">
                  <p className={`text-lg font-bold ${
                    transacao.tipo === 'receita' ? 'text-green-600' : 'text-red-600'
                  }`}>
                    {transacao.tipo === 'receita' ? '+' : '-'}R$ {transacao.valor.toFixed(2).replace('.', ',')}
                  </p>
                  <Badge className={getStatusColor(transacao.status)}>
                    {getStatusLabel(transacao.status)}
                  </Badge>
                </div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>

      {/* Resumo por Categoria */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle className="text-green-600">Receitas por Categoria</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {Array.from(new Set(receitas.map(r => r.categoria))).map(categoria => {
                const totalCategoria = receitas
                  .filter(r => r.categoria === categoria)
                  .reduce((sum, r) => sum + r.valor, 0);
                const percentual = ((totalCategoria / totalReceitas) * 100).toFixed(1);
                
                return (
                  <div key={categoria} className="flex justify-between items-center">
                    <span>{categoria}</span>
                    <div className="text-right">
                      <p className="font-semibold">R$ {totalCategoria.toFixed(2).replace('.', ',')}</p>
                      <p className="text-sm text-gray-600">{percentual}%</p>
                    </div>
                  </div>
                );
              })}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="text-red-600">Despesas por Categoria</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {Array.from(new Set(despesas.map(d => d.categoria))).map(categoria => {
                const totalCategoria = despesas
                  .filter(d => d.categoria === categoria)
                  .reduce((sum, d) => sum + d.valor, 0);
                const percentual = ((totalCategoria / totalDespesas) * 100).toFixed(1);
                
                return (
                  <div key={categoria} className="flex justify-between items-center">
                    <span>{categoria}</span>
                    <div className="text-right">
                      <p className="font-semibold">R$ {totalCategoria.toFixed(2).replace('.', ',')}</p>
                      <p className="text-sm text-gray-600">{percentual}%</p>
                    </div>
                  </div>
                );
              })}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
