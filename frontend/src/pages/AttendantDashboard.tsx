import {
  ChartBarIcon,
  CheckCircleIcon,
  ClockIcon,
  CogIcon,
  ExclamationTriangleIcon,
  PlusIcon,
  UserGroupIcon,
} from '@heroicons/react/24/outline';
import React, { useState } from 'react';
import { CompleteServiceModal } from '../components/Attendant/CompleteServiceModal';
import { QuickActions } from '../components/Attendant/QuickActions';
import { QuickServiceModal } from '../components/Attendant/QuickServiceModal';
import { RecentServices } from '../components/Attendant/RecentServices';
import { ServiceList } from '../components/Attendant/ServiceList';
import { ServiceSuggestionsModal } from '../components/Attendant/ServiceSuggestionsModal';
import { ServiceTemplatesModal } from '../components/Attendant/ServiceTemplatesModal';
import { ServiceValidation } from '../components/Attendant/ServiceValidation';
import { StatsCard } from '../components/Attendant/StatsCard';
import { useServiceStats } from '../hooks/useAttendantServices';

export const AttendantDashboard: React.FC = () => {
  const { stats, isLoading: isLoadingStats } = useServiceStats();
  const [activeModal, setActiveModal] = useState<
    'quick' | 'complete' | 'templates' | 'suggestions' | null
  >(null);
  const [selectedClientId] = useState<number | null>(null);
  const [selectedVehicleId] = useState<number | null>(null);

  const handleQuickService = () => {
    setActiveModal('quick');
  };

  const handleCompleteService = () => {
    setActiveModal('complete');
  };

  const handleTemplates = () => {
    setActiveModal('templates');
  };

  const handleSuggestions = () => {
    if (selectedClientId && selectedVehicleId) {
      setActiveModal('suggestions');
    } else {
      // Mostrar modal de seleção de cliente/veículo
    }
  };

  const closeModal = () => {
    setActiveModal(null);
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <div className="bg-white shadow-sm border-b border-gray-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center py-6">
            <div className="flex items-center space-x-4">
              <div className="p-3 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl shadow-lg">
                <UserGroupIcon className="h-8 w-8 text-white" />
              </div>
              <div>
                <h1 className="text-2xl font-bold text-gray-900">
                  Dashboard do Atendente
                </h1>
                <p className="text-gray-600">
                  Gerencie serviços e atenda clientes de forma eficiente
                </p>
              </div>
            </div>

            <div className="flex items-center space-x-3">
              <button
                onClick={handleTemplates}
                className="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center space-x-2"
              >
                <CogIcon className="h-5 w-5" />
                <span>Templates</span>
              </button>

              <button
                onClick={handleCompleteService}
                className="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center space-x-2"
              >
                <PlusIcon className="h-5 w-5" />
                <span>Serviço Completo</span>
              </button>

              <button
                onClick={handleQuickService}
                className="px-6 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700 transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:scale-105"
              >
                <PlusIcon className="h-5 w-5" />
                <span>Novo Serviço</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <StatsCard
            title="Serviços Hoje"
            value={stats?.services_created_today || 0}
            icon={PlusIcon}
            color="green"
            isLoading={isLoadingStats}
          />
          <StatsCard
            title="Pendentes"
            value={stats?.pending_services || 0}
            icon={ClockIcon}
            color="yellow"
            isLoading={isLoadingStats}
          />
          <StatsCard
            title="Concluídos Hoje"
            value={stats?.completed_today || 0}
            icon={CheckCircleIcon}
            color="blue"
            isLoading={isLoadingStats}
          />
          <StatsCard
            title="Tempo Médio"
            value={`${stats?.average_creation_time || 0}min`}
            icon={ChartBarIcon}
            color="purple"
            isLoading={isLoadingStats}
          />
        </div>

        {/* Quick Actions */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
          <div className="lg:col-span-2">
            <QuickActions
              onQuickService={handleQuickService}
              onCompleteService={handleCompleteService}
              onTemplates={handleTemplates}
              onSuggestions={handleSuggestions}
              selectedClientId={selectedClientId}
              selectedVehicleId={selectedVehicleId}
            />
          </div>

          <div>
            <ServiceValidation />
          </div>
        </div>

        {/* Recent Services */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <div className="lg:col-span-2">
            <RecentServices />
          </div>

          <div>
            <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center space-x-2">
                <ExclamationTriangleIcon className="h-5 w-5 text-orange-500" />
                <span>Alertas</span>
              </h3>

              <div className="space-y-3">
                <div className="p-3 bg-orange-50 border border-orange-200 rounded-lg">
                  <p className="text-sm text-orange-800">
                    <strong>3 serviços</strong> com prioridade alta aguardando
                  </p>
                </div>

                <div className="p-3 bg-red-50 border border-red-200 rounded-lg">
                  <p className="text-sm text-red-800">
                    <strong>1 veículo</strong> com manutenção atrasada
                  </p>
                </div>

                <div className="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                  <p className="text-sm text-blue-800">
                    <strong>5 clientes</strong> aguardando confirmação
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Service List */}
        <div className="mt-8">
          <ServiceList />
        </div>
      </div>

      {/* Modals */}
      {activeModal === 'quick' && (
        <QuickServiceModal
          isOpen={true}
          onClose={closeModal}
          clientId={selectedClientId}
          vehicleId={selectedVehicleId}
        />
      )}

      {activeModal === 'complete' && (
        <CompleteServiceModal
          isOpen={true}
          onClose={closeModal}
          clientId={selectedClientId}
          vehicleId={selectedVehicleId}
        />
      )}

      {activeModal === 'templates' && (
        <ServiceTemplatesModal
          isOpen={true}
          onClose={closeModal}
          onSelectTemplate={() => {
            // Lógica para aplicar template
            closeModal();
          }}
        />
      )}

      {activeModal === 'suggestions' &&
        selectedClientId &&
        selectedVehicleId && (
          <ServiceSuggestionsModal
            isOpen={true}
            onClose={closeModal}
            clientId={selectedClientId}
            vehicleId={selectedVehicleId}
            onSelectSuggestion={() => {
              // Lógica para aplicar sugestão
              closeModal();
            }}
          />
        )}
    </div>
  );
};
