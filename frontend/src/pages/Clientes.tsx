
import { useState, useEffect } from "react";
import { Link } from "react-router-dom";
import { useClienteStore } from "../stores/clienteStore";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { SearchInput } from "../components/ui/SearchInput";
import { LoadingSpinner } from "../components/ui/LoadingSpinner";
import { Plus, Car, Phone, Clock } from "lucide-react";
import { toast } from "sonner";

export default function Clientes() {
  const { clientes, loading, buscarTodos, buscarPorPlaca } = useClienteStore();
  const [searchPlaca, setSearchPlaca] = useState("");
  const [filteredClientes, setFilteredClientes] = useState(clientes);

  useEffect(() => {
    buscarTodos().catch(() => {
      toast.error("Erro ao carregar clientes");
    });
  }, [buscarTodos]);

  useEffect(() => {
    setFilteredClientes(clientes);
  }, [clientes]);

  const handleSearch = async () => {
    if (!searchPlaca.trim()) {
      setFilteredClientes(clientes);
      return;
    }

    try {
      const cliente = await buscarPorPlaca(searchPlaca);
      if (cliente) {
        setFilteredClientes([cliente]);
        toast.success("Cliente encontrado!");
      } else {
        setFilteredClientes([]);
        toast.info("Nenhum cliente encontrado com esta placa");
      }
    } catch (error) {
      toast.error("Erro ao buscar cliente");
    }
  };

  const formatTelefone = (telefone: string) => {
    const clean = telefone.replace(/\D/g, '');
    if (clean.length === 11) {
      return clean.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    }
    return clean.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
  };

  if (loading && filteredClientes.length === 0) {
    return (
      <div className="flex items-center justify-center h-64">
        <LoadingSpinner size="lg" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Clientes</h1>
          <p className="text-gray-600">Gerencie seus clientes</p>
        </div>
        <Button asChild>
          <Link to="/cliente/novo">
            <Plus className="w-4 h-4 mr-2" />
            Novo Cliente
          </Link>
        </Button>
      </div>

      {/* Busca por Placa */}
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
              onSearch={handleSearch}
            />
            <Button onClick={handleSearch} disabled={loading}>
              {loading ? <LoadingSpinner size="sm" /> : "Buscar"}
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* Lista de Clientes */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {filteredClientes.map((cliente) => (
          <Card key={cliente.id} className="hover:shadow-lg transition-shadow">
            <CardContent className="p-4">
              <div className="flex items-start justify-between mb-3">
                <div className="flex-1">
                  <h3 className="font-semibold text-gray-900 mb-1">
                    {cliente.nome}
                  </h3>
                  <div className="flex items-center gap-2 text-sm text-gray-600 mb-2">
                    <Car className="w-4 h-4" />
                    <span>{cliente.marca} {cliente.modelo}</span>
                  </div>
                </div>
                <span className="bg-blue-100 text-blue-700 px-2 py-1 rounded text-sm font-medium">
                  {cliente.placa}
                </span>
              </div>

              <div className="space-y-2 text-sm">
                <div className="flex items-center gap-2 text-gray-600">
                  <Phone className="w-4 h-4" />
                  <span>{formatTelefone(cliente.telefone)}</span>
                </div>
                {cliente.quilometragem > 0 && (
                  <div className="flex items-center gap-2 text-gray-600">
                    <Clock className="w-4 h-4" />
                    <span>{cliente.quilometragem.toLocaleString()} km</span>
                  </div>
                )}
              </div>

              <div className="flex gap-2 mt-4">
                <Button variant="outline" size="sm" className="flex-1" asChild>
                  <Link to={`/cliente/${cliente.id}`}>
                    Ver Detalhes
                  </Link>
                </Button>
                <Button size="sm" className="flex-1" asChild>
                  <Link to={`/servico/novo?cliente=${cliente.id}`}>
                    Novo Serviço
                  </Link>
                </Button>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>

      {filteredClientes.length === 0 && !loading && (
        <div className="text-center py-12">
          <Car className="w-12 h-12 text-gray-400 mx-auto mb-4" />
          <h3 className="text-lg font-medium text-gray-900 mb-2">
            Nenhum cliente encontrado
          </h3>
          <p className="text-gray-600 mb-4">
            {searchPlaca 
              ? "Tente buscar com uma placa diferente ou cadastre um novo cliente"
              : "Comece cadastrando seu primeiro cliente"
            }
          </p>
          <Button asChild>
            <Link to="/cliente/novo">
              <Plus className="w-4 h-4 mr-2" />
              Cadastrar Cliente
            </Link>
          </Button>
        </div>
      )}
    </div>
  );
}
