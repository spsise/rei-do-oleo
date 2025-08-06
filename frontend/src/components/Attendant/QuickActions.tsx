import {
  ChartBarIcon,
  ClockIcon,
  CogIcon,
  LightBulbIcon,
  PlusIcon,
  SparklesIcon,
  TruckIcon,
  UserIcon,
} from '@heroicons/react/24/outline';
import React from 'react';

interface QuickActionsProps {
  onQuickService: () => void;
  onCompleteService: () => void;
  onTemplates: () => void;
  onSuggestions: () => void;
  selectedClientId?: number | null;
  selectedVehicleId?: number | null;
}

export const QuickActions: React.FC<QuickActionsProps> = ({
  onQuickService,
  onCompleteService,
  onTemplates,
  onSuggestions,
  selectedClientId,
  selectedVehicleId,
}) => {
  const actions = [
    {
      id: 'quick-service',
      title: 'Serviço Rápido',
      description: 'Crie um serviço básico rapidamente',
      icon: PlusIcon,
      color: 'green',
      onClick: onQuickService,
      gradient: 'from-green-500 to-emerald-600',
      hoverGradient: 'from-green-600 to-emerald-700',
    },
    {
      id: 'complete-service',
      title: 'Serviço Completo',
      description: 'Crie um serviço com todos os detalhes',
      icon: ClockIcon,
      color: 'blue',
      onClick: onCompleteService,
      gradient: 'from-blue-500 to-indigo-600',
      hoverGradient: 'from-blue-600 to-indigo-700',
    },
    {
      id: 'templates',
      title: 'Templates',
      description: 'Use modelos pré-definidos',
      icon: SparklesIcon,
      color: 'purple',
      onClick: onTemplates,
      gradient: 'from-purple-500 to-violet-600',
      hoverGradient: 'from-purple-600 to-violet-700',
    },
    {
      id: 'suggestions',
      title: 'Sugestões',
      description: 'Veja sugestões baseadas no histórico',
      icon: LightBulbIcon,
      color: 'yellow',
      onClick: onSuggestions,
      gradient: 'from-yellow-500 to-orange-600',
      hoverGradient: 'from-yellow-600 to-orange-700',
      disabled: !selectedClientId || !selectedVehicleId,
    },
  ];

  return (
    <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <div className="flex items-center justify-between mb-6">
        <div>
          <h3 className="text-lg font-semibold text-gray-900">Ações Rápidas</h3>
          <p className="text-sm text-gray-600">
            Acesse as funcionalidades mais usadas
          </p>
        </div>

        {/* Client/Vehicle Selection Status */}
        <div className="flex items-center gap-2">
          <div
            className={`p-2 rounded-lg ${
              selectedClientId
                ? 'bg-green-100 text-green-700'
                : 'bg-gray-100 text-gray-500'
            }`}
          >
            <UserIcon className="h-4 w-4" />
          </div>
          <div
            className={`p-2 rounded-lg ${
              selectedVehicleId
                ? 'bg-green-100 text-green-700'
                : 'bg-gray-100 text-gray-500'
            }`}
          >
            <TruckIcon className="h-4 w-4" />
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {actions.map((action) => {
          const Icon = action.icon;
          const isDisabled = action.disabled;

          return (
            <button
              key={action.id}
              onClick={action.onClick}
              disabled={isDisabled}
              className={`relative p-4 rounded-xl border-2 border-gray-100 hover:border-gray-200 transition-all duration-200 group ${
                isDisabled
                  ? 'opacity-50 cursor-not-allowed'
                  : 'hover:shadow-md hover:scale-105'
              }`}
            >
              {/* Background gradient */}
              <div
                className={`absolute inset-0 bg-gradient-to-r ${action.gradient} opacity-0 group-hover:opacity-5 transition-opacity duration-200 rounded-xl`}
              />

              <div className="relative flex items-start gap-3">
                <div
                  className={`p-3 bg-gradient-to-r ${action.gradient} rounded-lg shadow-lg group-hover:shadow-xl transition-shadow duration-200`}
                >
                  <Icon className="h-6 w-6 text-white" />
                </div>

                <div className="flex-1 text-left">
                  <h4 className="font-semibold text-gray-900 group-hover:text-gray-700 transition-colors">
                    {action.title}
                  </h4>
                  <p className="text-sm text-gray-600 mt-1">
                    {action.description}
                  </p>

                  {isDisabled && (
                    <p className="text-xs text-orange-600 mt-2 flex items-center gap-1">
                      <CogIcon className="h-3 w-3" />
                      Selecione cliente e veículo
                    </p>
                  )}
                </div>
              </div>

              {/* Hover effect */}
              <div
                className={`absolute inset-0 bg-gradient-to-r ${action.hoverGradient} opacity-0 group-hover:opacity-10 transition-opacity duration-200 rounded-xl`}
              />
            </button>
          );
        })}
      </div>

      {/* Quick Stats Preview */}
      <div className="mt-6 pt-6 border-t border-gray-100">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-2">
            <ChartBarIcon className="h-4 w-4 text-gray-400" />
            <span className="text-sm text-gray-600">Resumo do dia</span>
          </div>
          <div className="flex items-center gap-4 text-sm">
            <div className="flex items-center gap-1">
              <div className="w-2 h-2 bg-green-500 rounded-full"></div>
              <span className="text-gray-600">
                Criados: <strong>12</strong>
              </span>
            </div>
            <div className="flex items-center gap-1">
              <div className="w-2 h-2 bg-blue-500 rounded-full"></div>
              <span className="text-gray-600">
                Concluídos: <strong>8</strong>
              </span>
            </div>
            <div className="flex items-center gap-1">
              <div className="w-2 h-2 bg-yellow-500 rounded-full"></div>
              <span className="text-gray-600">
                Pendentes: <strong>4</strong>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
