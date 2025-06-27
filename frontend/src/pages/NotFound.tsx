
import { Link } from "react-router-dom";
import { Button } from "@/components/ui/button";
import { Home, ArrowLeft } from "lucide-react";

export default function NotFound() {
  return (
    <div className="min-h-screen bg-gray-50 flex items-center justify-center px-4">
      <div className="max-w-md w-full text-center">
        <div className="mb-8">
          <h1 className="text-9xl font-bold text-gray-200">404</h1>
          <h2 className="text-2xl font-bold text-gray-900 mt-4">
            Página não encontrada
          </h2>
          <p className="text-gray-600 mt-2">
            A página que você está procurando não existe ou foi movida.
          </p>
        </div>
        
        <div className="space-y-4">
          <Button asChild className="w-full">
            <Link to="/">
              <Home className="w-4 h-4 mr-2" />
              Voltar ao Dashboard
            </Link>
          </Button>
          
          <Button variant="outline" asChild className="w-full">
            <Link to="/clientes">
              <ArrowLeft className="w-4 h-4 mr-2" />
              Ver Clientes
            </Link>
          </Button>
        </div>
        
        <div className="mt-8 text-sm text-gray-500">
          <p>Precisa de ajuda? Entre em contato com o suporte.</p>
        </div>
      </div>
    </div>
  );
}
