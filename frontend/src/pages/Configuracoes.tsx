import { useState } from "react";
import { useAuthStore } from "../stores/authStore";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Badge } from "@/components/ui/badge";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Plus, Edit, Trash2, Settings, Save, X } from "lucide-react";
import { toast } from "sonner";
import { usePermissions } from "@/hooks/use-permissions";

const CATEGORIAS = ["Lubrificação", "Filtros", "Fluidos", "Outros"];

interface TipoServico {
  id: number;
  nome: string;
  preco: number;
  tempo: number;
  categoria: string;
  ativo: boolean;
}

export default function Configuracoes() {
  const { hasManagerAccess } = usePermissions();

  const [tiposServicos, setTiposServicos] = useState<TipoServico[]>([
    {
      id: 1,
      nome: "Troca de Óleo",
      preco: 45.0,
      tempo: 15,
      categoria: "Lubrificação",
      ativo: true,
    },
    {
      id: 2,
      nome: "Troca de Filtro de Óleo",
      preco: 25.0,
      tempo: 10,
      categoria: "Filtros",
      ativo: true,
    },
    {
      id: 3,
      nome: "Troca de Filtro de Ar",
      preco: 35.0,
      tempo: 5,
      categoria: "Filtros",
      ativo: true,
    },
    {
      id: 4,
      nome: "Troca de Filtro de Combustível",
      preco: 55.0,
      tempo: 20,
      categoria: "Filtros",
      ativo: true,
    },
    {
      id: 5,
      nome: "Verificação de Fluidos",
      preco: 20.0,
      tempo: 10,
      categoria: "Fluidos",
      ativo: true,
    },
  ]);

  const [dialogOpen, setDialogOpen] = useState(false);
  const [editingServico, setEditingServico] = useState<TipoServico | null>(
    null
  );
  const [formData, setFormData] = useState({
    nome: "",
    preco: "",
    tempo: "",
    categoria: "",
  });

  // Verificar se o usuário é gerente
  if (!hasManagerAccess()) {
    return (
      <div className="text-center py-12">
        <Settings className="w-12 h-12 text-gray-400 mx-auto mb-4" />
        <h3 className="text-lg font-medium text-gray-900 mb-2">
          Acesso Restrito
        </h3>
        <p className="text-gray-600">
          Apenas gerentes podem acessar as configurações
        </p>
      </div>
    );
  }

  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat("pt-BR", {
      style: "currency",
      currency: "BRL",
    }).format(value);
  };

  const handleEdit = (servico: TipoServico) => {
    setEditingServico(servico);
    setFormData({
      nome: servico.nome,
      preco: servico.preco.toString(),
      tempo: servico.tempo.toString(),
      categoria: servico.categoria,
    });
    setDialogOpen(true);
  };

  const handleAdd = () => {
    setEditingServico(null);
    setFormData({
      nome: "",
      preco: "",
      tempo: "",
      categoria: "",
    });
    setDialogOpen(true);
  };

  const handleSave = () => {
    if (
      !formData.nome ||
      !formData.preco ||
      !formData.tempo ||
      !formData.categoria
    ) {
      toast.error("Preencha todos os campos");
      return;
    }

    const preco = parseFloat(formData.preco);
    const tempo = parseInt(formData.tempo);

    if (isNaN(preco) || preco <= 0) {
      toast.error("Preço deve ser um valor válido");
      return;
    }

    if (isNaN(tempo) || tempo <= 0) {
      toast.error("Tempo deve ser um valor válido");
      return;
    }

    if (editingServico) {
      // Editar existente
      setTiposServicos((prev) =>
        prev.map((s) =>
          s.id === editingServico.id ? { ...s, ...formData, preco, tempo } : s
        )
      );
      toast.success("Serviço atualizado com sucesso!");
    } else {
      // Adicionar novo
      const novoServico = {
        id: Date.now(),
        nome: formData.nome,
        preco,
        tempo,
        categoria: formData.categoria,
        ativo: true,
      };
      setTiposServicos((prev) => [...prev, novoServico]);
      toast.success("Serviço adicionado com sucesso!");
    }

    setDialogOpen(false);
  };

  const handleToggleStatus = (id: number) => {
    setTiposServicos((prev) =>
      prev.map((s) => (s.id === id ? { ...s, ativo: !s.ativo } : s))
    );
    toast.success("Status atualizado!");
  };

  const handleDelete = (id: number) => {
    setTiposServicos((prev) => prev.filter((s) => s.id !== id));
    toast.success("Serviço removido!");
  };

  const servicosPorCategoria = tiposServicos.reduce((acc, servico) => {
    if (!acc[servico.categoria]) {
      acc[servico.categoria] = [];
    }
    acc[servico.categoria].push(servico);
    return acc;
  }, {} as Record<string, typeof tiposServicos>);

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Configurações</h1>
          <p className="text-gray-600">
            Gerencie os tipos de serviços disponíveis
          </p>
        </div>
        <Button onClick={handleAdd}>
          <Plus className="w-4 h-4 mr-2" />
          Novo Serviço
        </Button>
      </div>

      {/* Estatísticas Rápidas */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Card>
          <CardContent className="p-4">
            <div className="text-center">
              <p className="text-2xl font-bold text-blue-600">
                {tiposServicos.length}
              </p>
              <p className="text-sm text-gray-600">Total de Serviços</p>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="p-4">
            <div className="text-center">
              <p className="text-2xl font-bold text-green-600">
                {tiposServicos.filter((s) => s.ativo).length}
              </p>
              <p className="text-sm text-gray-600">Serviços Ativos</p>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="p-4">
            <div className="text-center">
              <p className="text-2xl font-bold text-orange-600">
                {Object.keys(servicosPorCategoria).length}
              </p>
              <p className="text-sm text-gray-600">Categorias</p>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="p-4">
            <div className="text-center">
              <p className="text-2xl font-bold text-purple-600">
                {formatCurrency(
                  tiposServicos.reduce((acc, s) => acc + s.preco, 0) /
                    tiposServicos.length
                )}
              </p>
              <p className="text-sm text-gray-600">Preço Médio</p>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Lista de Serviços por Categoria */}
      <div className="space-y-6">
        {Object.entries(servicosPorCategoria).map(([categoria, servicos]) => (
          <Card key={categoria}>
            <CardHeader>
              <CardTitle className="flex items-center justify-between">
                <span>{categoria}</span>
                <Badge variant="secondary">{servicos.length} serviços</Badge>
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                {servicos.map((servico) => (
                  <div
                    key={servico.id}
                    className="flex items-center justify-between p-3 border rounded-lg"
                  >
                    <div className="flex-1">
                      <div className="flex items-center gap-2">
                        <h4 className="font-medium text-gray-900">
                          {servico.nome}
                        </h4>
                        <Badge
                          variant={servico.ativo ? "default" : "secondary"}
                        >
                          {servico.ativo ? "Ativo" : "Inativo"}
                        </Badge>
                      </div>
                      <div className="flex items-center gap-4 text-sm text-gray-600 mt-1">
                        <span>{formatCurrency(servico.preco)}</span>
                        <span>• {servico.tempo} min</span>
                      </div>
                    </div>
                    <div className="flex items-center gap-2">
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => handleToggleStatus(servico.id)}
                      >
                        {servico.ativo ? "Desativar" : "Ativar"}
                      </Button>
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => handleEdit(servico)}
                      >
                        <Edit className="w-4 h-4" />
                      </Button>
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => handleDelete(servico.id)}
                        className="text-red-600 hover:text-red-700"
                      >
                        <Trash2 className="w-4 h-4" />
                      </Button>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        ))}
      </div>

      {/* Dialog para Adicionar/Editar Serviço */}
      <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>
              {editingServico ? "Editar Serviço" : "Novo Serviço"}
            </DialogTitle>
          </DialogHeader>
          <div className="space-y-4">
            <div>
              <Label htmlFor="nome">Nome do Serviço</Label>
              <Input
                id="nome"
                value={formData.nome}
                onChange={(e) =>
                  setFormData({ ...formData, nome: e.target.value })
                }
                placeholder="Ex: Troca de Óleo"
              />
            </div>
            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label htmlFor="preco">Preço (R$)</Label>
                <Input
                  id="preco"
                  type="number"
                  step="0.01"
                  value={formData.preco}
                  onChange={(e) =>
                    setFormData({ ...formData, preco: e.target.value })
                  }
                  placeholder="0,00"
                />
              </div>
              <div>
                <Label htmlFor="tempo">Tempo (min)</Label>
                <Input
                  id="tempo"
                  type="number"
                  value={formData.tempo}
                  onChange={(e) =>
                    setFormData({ ...formData, tempo: e.target.value })
                  }
                  placeholder="15"
                />
              </div>
            </div>
            <div>
              <Label htmlFor="categoria">Categoria</Label>
              <Select
                value={formData.categoria}
                onValueChange={(value) =>
                  setFormData({ ...formData, categoria: value })
                }
              >
                <SelectTrigger>
                  <SelectValue placeholder="Selecione uma categoria" />
                </SelectTrigger>
                <SelectContent>
                  {CATEGORIAS.map((cat) => (
                    <SelectItem key={cat} value={cat}>
                      {cat}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
            <div className="flex gap-3 pt-4">
              <Button
                variant="outline"
                onClick={() => setDialogOpen(false)}
                className="flex-1"
              >
                <X className="w-4 h-4 mr-2" />
                Cancelar
              </Button>
              <Button onClick={handleSave} className="flex-1">
                <Save className="w-4 h-4 mr-2" />
                Salvar
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  );
}
