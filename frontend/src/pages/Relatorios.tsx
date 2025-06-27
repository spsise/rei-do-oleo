
import { useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Badge } from "@/components/ui/badge";
import { FileText, Download, Calendar, DollarSign, Users, Wrench } from "lucide-react";
import { toast } from "sonner";

export default function Relatorios() {
  const [periodo, setPeriodo] = useState("mensal");
  const [dataInicio, setDataInicio] = useState("");
  const [dataFim, setDataFim] = useState("");
  const [tipoRelatorio, setTipoRelatorio] = useState("faturamento");

  const gerarRelatorio = () => {
    if (!dataInicio || !dataFim) {
      toast.error("Selecione o período para gerar o relatório");
      return;
    }
    toast.success("Relatório gerado com sucesso!");
  };

  const relatoriosDisponiveis = [
    {
      id: "faturamento",
      titulo: "Faturamento",
      descricao: "Receitas por período",
      icon: DollarSign,
      cor: "text-green-600"
    },
    {
      id: "servicos",
      titulo: "Serviços Realizados",
      descricao: "Tipos e quantidade de serviços",
      icon: Wrench,
      cor: "text-blue-600"
    },
    {
      id: "clientes",
      titulo: "Relatório de Clientes",
      descricao: "Novos clientes e frequência",
      icon: Users,
      cor: "text-purple-600"
    },
    {
      id: "performance",
      titulo: "Performance da Equipe",
      descricao: "Tempo médio e produtividade",
      icon: FileText,
      cor: "text-orange-600"
    }
  ];

  const dadosExemplo = {
    faturamento: {
      total: "R$ 12.450,00",
      crescimento: "+18%",
      servicos: 156,
      ticketMedio: "R$ 79,80"
    },
    topServicos: [
      { servico: "Troca de Óleo", quantidade: 45, valor: "R$ 2.025,00" },
      { servico: "Troca de Filtros", quantidade: 38, valor: "R$ 1.330,00" },
      { servico: "Verificação Fluidos", quantidade: 73, valor: "R$ 1.460,00" }
    ]
  };

  return (
    <div className="max-w-7xl mx-auto space-y-6">
      <div className="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Relatórios</h1>
          <p className="text-gray-600">Analise o desempenho do seu negócio</p>
        </div>
        <Button onClick={gerarRelatorio} className="mt-4 md:mt-0">
          <Download className="w-4 h-4 mr-2" />
          Exportar PDF
        </Button>
      </div>

      {/* Filtros */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Calendar className="w-5 h-5" />
            Filtros do Relatório
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div className="space-y-2">
              <Label htmlFor="tipo">Tipo de Relatório</Label>
              <Select value={tipoRelatorio} onValueChange={setTipoRelatorio}>
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  {relatoriosDisponiveis.map(rel => (
                    <SelectItem key={rel.id} value={rel.id}>
                      {rel.titulo}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            <div className="space-y-2">
              <Label htmlFor="periodo">Período</Label>
              <Select value={periodo} onValueChange={setPeriodo}>
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="diario">Diário</SelectItem>
                  <SelectItem value="semanal">Semanal</SelectItem>
                  <SelectItem value="mensal">Mensal</SelectItem>
                  <SelectItem value="personalizado">Personalizado</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div className="space-y-2">
              <Label htmlFor="inicio">Data Início</Label>
              <Input
                id="inicio"
                type="date"
                value={dataInicio}
                onChange={(e) => setDataInicio(e.target.value)}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="fim">Data Fim</Label>
              <Input
                id="fim"
                type="date"
                value={dataFim}
                onChange={(e) => setDataFim(e.target.value)}
              />
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Tipos de Relatórios */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {relatoriosDisponiveis.map((relatorio) => {
          const Icon = relatorio.icon;
          return (
            <Card 
              key={relatorio.id}
              className={`cursor-pointer transition-all hover:shadow-md ${
                tipoRelatorio === relatorio.id ? 'ring-2 ring-blue-500' : ''
              }`}
              onClick={() => setTipoRelatorio(relatorio.id)}
            >
              <CardContent className="p-4">
                <div className="flex items-center gap-3">
                  <Icon className={`w-8 h-8 ${relatorio.cor}`} />
                  <div>
                    <h3 className="font-semibold">{relatorio.titulo}</h3>
                    <p className="text-sm text-gray-600">{relatorio.descricao}</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          );
        })}
      </div>

      {/* Prévia dos Dados */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Resumo Financeiro</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex justify-between items-center">
              <span className="text-gray-600">Faturamento Total</span>
              <span className="text-2xl font-bold text-green-600">
                {dadosExemplo.faturamento.total}
              </span>
            </div>
            <div className="flex justify-between items-center">
              <span className="text-gray-600">Crescimento</span>
              <Badge variant="secondary" className="bg-green-100 text-green-800">
                {dadosExemplo.faturamento.crescimento}
              </Badge>
            </div>
            <div className="flex justify-between items-center">
              <span className="text-gray-600">Serviços Realizados</span>
              <span className="font-semibold">{dadosExemplo.faturamento.servicos}</span>
            </div>
            <div className="flex justify-between items-center">
              <span className="text-gray-600">Ticket Médio</span>
              <span className="font-semibold">{dadosExemplo.faturamento.ticketMedio}</span>
            </div>
          </CardContent>
        </Card>

        <Card className="lg:col-span-2">
          <CardHeader>
            <CardTitle>Top Serviços do Período</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {dadosExemplo.topServicos.map((item, index) => (
                <div key={index} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <div>
                    <h4 className="font-semibold">{item.servico}</h4>
                    <p className="text-sm text-gray-600">{item.quantidade} serviços</p>
                  </div>
                  <div className="text-right">
                    <p className="font-semibold text-green-600">{item.valor}</p>
                    <p className="text-sm text-gray-600">
                      {((item.quantidade / dadosExemplo.faturamento.servicos) * 100).toFixed(1)}%
                    </p>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
