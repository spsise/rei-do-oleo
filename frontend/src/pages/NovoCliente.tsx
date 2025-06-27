
import { ClienteForm } from "../components/forms/ClienteForm";

export default function NovoCliente() {
  return (
    <div className="max-w-4xl mx-auto">
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-900">Novo Cliente</h1>
        <p className="text-gray-600">Cadastre um novo cliente no sistema</p>
      </div>
      
      <ClienteForm />
    </div>
  );
}
