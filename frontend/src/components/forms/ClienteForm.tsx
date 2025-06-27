
import { useForm } from "react-hook-form";
import { useNavigate } from "react-router-dom";
import { useClienteStore } from "../../stores/clienteStore";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { LoadingSpinner } from "../ui/LoadingSpinner";
import { toast } from "sonner";
import { NovoCliente } from "../../types";

interface ClienteFormProps {
  cliente?: any;
  onSuccess?: () => void;
}

export function ClienteForm({ cliente, onSuccess }: ClienteFormProps) {
  const navigate = useNavigate();
  const { criarCliente, atualizarCliente, loading } = useClienteStore();
  
  const {
    register,
    handleSubmit,
    formState: { errors },
    setValue
  } = useForm<NovoCliente>({
    defaultValues: cliente || {}
  });

  const formatPlaca = (value: string) => {
    // Remove caracteres não alfanuméricos
    const clean = value.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
    
    // Formato ABC-1234 ou ABC1D23
    if (clean.length <= 3) {
      return clean;
    } else if (clean.length <= 7) {
      return `${clean.slice(0, 3)}-${clean.slice(3)}`;
    } else {
      return `${clean.slice(0, 3)}-${clean.slice(3, 7)}`;
    }
  };

  const formatTelefone = (value: string) => {
    const clean = value.replace(/\D/g, '');
    if (clean.length <= 10) {
      return clean.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
    }
    return clean.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
  };

  const onSubmit = async (data: NovoCliente) => {
    try {
      if (cliente) {
        await atualizarCliente(cliente.id, data);
        toast.success("Cliente atualizado com sucesso!");
      } else {
        await criarCliente(data);
        toast.success("Cliente cadastrado com sucesso!");
      }
      
      if (onSuccess) {
        onSuccess();
      } else {
        navigate('/clientes');
      }
    } catch (error: any) {
      toast.error(error.response?.data?.message || "Erro ao salvar cliente");
    }
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle>
          {cliente ? 'Editar Cliente' : 'Novo Cliente'}
        </CardTitle>
      </CardHeader>
      
      <CardContent>
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="nome">Nome Completo *</Label>
              <Input
                id="nome"
                placeholder="Nome do cliente"
                {...register("nome", {
                  required: "Nome é obrigatório"
                })}
                className={errors.nome ? "border-red-500" : ""}
              />
              {errors.nome && (
                <p className="text-sm text-red-600">{errors.nome.message}</p>
              )}
            </div>

            <div className="space-y-2">
              <Label htmlFor="telefone">Telefone *</Label>
              <Input
                id="telefone"
                placeholder="(11) 99999-9999"
                {...register("telefone", {
                  required: "Telefone é obrigatório",
                  onChange: (e) => {
                    e.target.value = formatTelefone(e.target.value);
                  }
                })}
                className={errors.telefone ? "border-red-500" : ""}
              />
              {errors.telefone && (
                <p className="text-sm text-red-600">{errors.telefone.message}</p>
              )}
            </div>

            <div className="space-y-2">
              <Label htmlFor="placa">Placa do Veículo *</Label>
              <Input
                id="placa"
                placeholder="ABC-1234"
                {...register("placa", {
                  required: "Placa é obrigatória",
                  onChange: (e) => {
                    e.target.value = formatPlaca(e.target.value);
                  },
                  pattern: {
                    value: /^[A-Z]{3}-[0-9]{4}$|^[A-Z]{3}[0-9][A-Z][0-9]{2}$/,
                    message: "Formato inválido (ABC-1234 ou ABC1D23)"
                  }
                })}
                className={errors.placa ? "border-red-500" : ""}
              />
              {errors.placa && (
                <p className="text-sm text-red-600">{errors.placa.message}</p>
              )}
            </div>

            <div className="space-y-2">
              <Label htmlFor="marca">Marca *</Label>
              <Input
                id="marca"
                placeholder="Honda, Toyota, etc."
                {...register("marca", {
                  required: "Marca é obrigatória"
                })}
                className={errors.marca ? "border-red-500" : ""}
              />
              {errors.marca && (
                <p className="text-sm text-red-600">{errors.marca.message}</p>
              )}
            </div>

            <div className="space-y-2">
              <Label htmlFor="modelo">Modelo *</Label>
              <Input
                id="modelo"
                placeholder="Civic, Corolla, etc."
                {...register("modelo", {
                  required: "Modelo é obrigatório"
                })}
                className={errors.modelo ? "border-red-500" : ""}
              />
              {errors.modelo && (
                <p className="text-sm text-red-600">{errors.modelo.message}</p>
              )}
            </div>

            <div className="space-y-2">
              <Label htmlFor="quilometragem">Quilometragem Atual</Label>
              <Input
                id="quilometragem"
                type="number"
                placeholder="50000"
                {...register("quilometragem", {
                  valueAsNumber: true,
                  min: {
                    value: 0,
                    message: "Quilometragem deve ser positiva"
                  }
                })}
                className={errors.quilometragem ? "border-red-500" : ""}
              />
              {errors.quilometragem && (
                <p className="text-sm text-red-600">{errors.quilometragem.message}</p>
              )}
            </div>
          </div>

          <div className="flex gap-3 pt-4">
            <Button
              type="submit"
              disabled={loading}
              className="flex-1"
            >
              {loading ? (
                <>
                  <LoadingSpinner size="sm" className="mr-2" />
                  Salvando...
                </>
              ) : (
                cliente ? 'Atualizar' : 'Cadastrar'
              )}
            </Button>
            
            <Button
              type="button"
              variant="outline"
              onClick={() => navigate('/clientes')}
              className="flex-1"
            >
              Cancelar
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  );
}
