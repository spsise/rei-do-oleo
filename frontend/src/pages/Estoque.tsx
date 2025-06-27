
import { useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Package, AlertTriangle, Plus, Search, Filter } from "lucide-react";
import { toast } from "sonner";

interface ItemEstoque {
  id: string;
  nome: string;
  categoria: 'oleo' | 'filtros' | 'aditivos' | 'outros';
  quantidade: number;
  quantidadeMinima: number;
  preco: number;
  fornecedor: string;
  ultimaCompra: string;
}

export default function Estoque() {
  const [filtroCategoria, setFiltroCategoria] = useState<string>('todos');
  const [busca, setBusca] = useState('');

  const itensEstoque: ItemEstoque[] = [
    {
      id: '1',
      nome: 'Óleo Motor 5W30 Sintético',
      categoria: 'oleo',
      quantidade: 24,
      quantidadeMinima: 10,
      preco: 35.90,
      fornecedor: 'Distribuidora ABC',
      ultimaCompra: '2024-01-15'
    },
    {
      id: '2',
      nome: 'Filtro de Óleo Universal',
      categoria: 'filtros', 
      quantidade: 8,
      quantidadeMinima: 15,
      preco: 12.50,
      fornecedor: 'Auto Peças XYZ',
      ultimaCompra: '2024-01-10'
    },
    {
      id: '3',
      nome: 'Filtro de Ar Civic',
      categoria: 'filtros',
      quantidade: 5,
      quantidadeMinima: 8,
      preco: 25.00,
      fornecedor: 'Honda Parts',
      ultimaCompra: '2024-01-08'
    },
    {
      id: '4',
      nome: 'Aditivo Radiador',
      categoria: 'aditivos',
      quantidade: 15,
      quantidadeMinima: 5,
      preco: 18.90,
      fornecedor: 'Química Total',
      ultimaCompra: '2024-01-12'
    }
  ];

  const getCategoriaLabel = (categoria: string) => {
    switch (categoria) {
      case 'oleo': return 'Óleos';
      case 'filtros': return 'Filtros';
      case 'aditivos': return 'Aditivos';
      case 'outros': return 'Outros';
      default: return categoria;
    }
  };

  const getCategoriaColor = (categoria: string) => {
    switch (categoria) {
      case 'oleo': return 'bg-blue-100 text-blue-800';
      case 'filtros': return 'bg-green-100 text-green-800';
      case 'aditivos': return 'bg-purple-100 text-purple-800';
      case 'outros': return 'bg-gray-100 text-gray-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getStatusEstoque = (quantidade: number, minimo: number) => {
    if (quantidade === 0) return { label: 'Sem Estoque', color: 'bg-red-100 text-red-800' };
    if (quantidade <= minimo) return { label: 'Estoque Baixo', color: 'bg-yellow-100 text-yellow-800' };
    return { label: 'Em Estoque', color: 'bg-green-100 text-green-800' };
  };

  const itensFiltrados = itensEstoque.filter(item => {
    const matchCategoria = filtroCategoria === 'todos' || item.categoria === filtroCategoria;
    const matchBusca = item.nome.toLowerCase().includes(busca.toLowerCase()) ||
                       item.fornecedor.toLowerCase().includes(busca.toLowerCase());
    return matchCategoria && matchBusca;
  });

  const itensEstoqueBaixo = itensEstoque.filter(item => 
    item.quantidade <= item.quantidadeMinima || item.quantidade === 0
  );

  const valorTotalEstoque = itensEstoque.reduce((total, item) => 
    total + (item.quantidade * item.preco), 0
  );

  const adicionarEstoque = (itemId: string) => {
    toast.success("Estoque atualizado com sucesso!");
  };

  return (
    <div className="max-w-7xl mx-auto space-y-6">
      <div className="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Controle de Estoque</h1>
          <p className="text-gray-600">Gerencie seus produtos e insumos</p>
        </div>
        <Button>
          <Plus className="w-4 h-4 mr-2" />
          Adicionar Item
        </Button>
      </div>

      {/* Métricas Resumo */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Card>
          <CardContent className="p-4">
            <div className="flex items-center gap-3">
              <Package className="w-8 h-8 text-blue-600" />
              <div>
                <p className="text-sm text-gray-600">Total de Itens</p>
                <p className="text-2xl font-bold">{itensEstoque.length}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-4">
            <div className="flex items-center gap-3">
              <AlertTriangle className="w-8 h-8 text-red-600" />
              <div>
                <p className="text-sm text-gray-600">Estoque Baixo</p>
                <p className="text-2xl font-bold text-red-600">{itensEstoqueBaixo.length}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-4">
            <div>
              <p className="text-sm text-gray-600">Valor Total</p>
              <p className="text-2xl font-bold text-green-600">
                R$ {valorTotalEstoque.toFixed(2).replace('.', ',')}
              </p>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-4">
            <div>
              <p className="text-sm text-gray-600">Última Atualização</p>
              <p className="text-sm font-medium">Hoje, 14:30</p>
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
                placeholder="Buscar por nome ou fornecedor..."
                value={busca}
                onChange={(e) => setBusca(e.target.value)}
                className="pl-10"
              />
            </div>
            
            <div className="flex items-center gap-2">
              <Filter className="w-4 h-4 text-gray-500" />
              <select
                value={filtroCategoria}
                onChange={(e) => setFiltroCategoria(e.target.value)}
                className="border rounded-md px-3 py-2"
              >
                <option value="todos">Todas Categorias</option>
                <option value="oleo">Óleos</option>
                <option value="filtros">Filtros</option>
                <option value="aditivos">Aditivos</option>
                <option value="outros">Outros</option>
              </select>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Alertas de Estoque Baixo */}
      {itensEstoqueBaixo.length > 0 && (
        <Card className="border-red-200 bg-red-50">
          <CardHeader>
            <CardTitle className="text-red-800 flex items-center gap-2">
              <AlertTriangle className="w-5 h-5" />
              Atenção: Itens com Estoque Baixo
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-2">
              {itensEstoqueBaixo.map((item) => (
                <div key={item.id} className="flex items-center justify-between p-2 bg-white rounded border">
                  <span className="font-medium">{item.nome}</span>
                  <Badge className="bg-red-100 text-red-800">
                    {item.quantidade} un.
                  </Badge>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      )}

      {/* Lista de Itens */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {itensFiltrados.map((item) => {
          const statusEstoque = getStatusEstoque(item.quantidade, item.quantidadeMinima);
          
          return (
            <Card key={item.id} className="hover:shadow-md transition-shadow">
              <CardContent className="p-4">
                <div className="flex justify-between items-start mb-3">
                  <h3 className="font-semibold text-gray-900 line-clamp-2">{item.nome}</h3>
                  <Badge className={getCategoriaColor(item.categoria)}>
                    {getCategoriaLabel(item.categoria)}
                  </Badge>
                </div>

                <div className="space-y-2">
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Quantidade:</span>
                    <span className="font-medium">{item.quantidade} un.</span>
                  </div>

                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Mínimo:</span>
                    <span className="text-sm">{item.quantidadeMinima} un.</span>
                  </div>

                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Preço:</span>
                    <span className="font-medium">R$ {item.preco.toFixed(2).replace('.', ',')}</span>
                  </div>

                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Fornecedor:</span>
                    <span className="text-sm">{item.fornecedor}</span>
                  </div>

                  <div className="pt-2">
                    <Badge className={statusEstoque.color}>
                      {statusEstoque.label}
                    </Badge>
                  </div>

                  <div className="flex gap-2 pt-3">
                    <Button 
                      size="sm" 
                      variant="outline" 
                      className="flex-1"
                      onClick={() => adicionarEstoque(item.id)}
                    >
                      + Adicionar
                    </Button>
                    <Button size="sm" variant="outline">
                      Editar
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>
          );
        })}
      </div>
    </div>
  );
}
