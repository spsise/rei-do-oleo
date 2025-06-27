import { useEffect } from "react";
import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import { MainLayout } from "./components/layout/MainLayout";
import { ProtectedRoute } from "./components/ProtectedRoute";
import { pwaInstaller } from "./utils/pwaInstaller";

// Pages
import Login from "./pages/Login";
import Dashboard from "./pages/Dashboard";
import Clientes from "./pages/Clientes";
import NovoCliente from "./pages/NovoCliente";
import ClienteDetalhes from "./pages/ClienteDetalhes";
import Servicos from "./pages/Servicos";
import NovoServico from "./pages/NovoServico";
import Configuracoes from "./pages/Configuracoes";
import Relatorios from "./pages/Relatorios";
import Agenda from "./pages/Agenda";
import Estoque from "./pages/Estoque";
import Financeiro from "./pages/Financeiro";
import NotFound from "./pages/NotFound";

// Zustand persist
import { create } from "zustand";

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
});

const App = () => {
  useEffect(() => {
    // Register service worker
    if ("serviceWorker" in navigator) {
      navigator.serviceWorker
        .register("/sw.js")
        .then(() => console.log("SW registered"))
        .catch(() => console.log("SW registration failed"));
    }

    // Initialize PWA installer
    pwaInstaller.init();

    // Update manifest link
    const manifestLink = document.querySelector('link[rel="manifest"]');
    if (!manifestLink) {
      const link = document.createElement("link");
      link.rel = "manifest";
      link.href = "/manifest.json";
      document.head.appendChild(link);
    }
  }, []);

  return (
    <QueryClientProvider client={queryClient}>
      <TooltipProvider>
        <Toaster />
        <Sonner />
        <BrowserRouter>
          <Routes>
            <Route path="/login" element={<Login />} />
            <Route
              path="/"
              element={
                <ProtectedRoute>
                  <MainLayout />
                </ProtectedRoute>
              }
            >
              <Route
                index
                element={
                  <ProtectedRoute requiredRoles={["admin", "manager"]}>
                    <Dashboard />
                  </ProtectedRoute>
                }
              />
              <Route path="clientes" element={<Clientes />} />
              <Route path="cliente/novo" element={<NovoCliente />} />
              <Route path="cliente/:id" element={<ClienteDetalhes />} />
              <Route path="servicos" element={<Servicos />} />
              <Route path="servico/novo" element={<NovoServico />} />
              <Route path="agenda" element={<Agenda />} />

              {/* Manager-only routes */}
              <Route
                path="configuracoes"
                element={
                  <ProtectedRoute requiredRoles={["admin", "manager"]}>
                    <Configuracoes />
                  </ProtectedRoute>
                }
              />
              <Route
                path="relatorios"
                element={
                  <ProtectedRoute requiredRoles={["admin", "manager"]}>
                    <Relatorios />
                  </ProtectedRoute>
                }
              />
              <Route
                path="estoque"
                element={
                  <ProtectedRoute requiredRoles={["admin", "manager"]}>
                    <Estoque />
                  </ProtectedRoute>
                }
              />
              <Route
                path="financeiro"
                element={
                  <ProtectedRoute requiredRoles={["admin", "manager"]}>
                    <Financeiro />
                  </ProtectedRoute>
                }
              />
            </Route>
            <Route path="*" element={<NotFound />} />
          </Routes>
        </BrowserRouter>
      </TooltipProvider>
    </QueryClientProvider>
  );
};

export default App;
