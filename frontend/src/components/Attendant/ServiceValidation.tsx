import {
  CheckCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  SparklesIcon,
  XMarkIcon,
} from '@heroicons/react/24/outline';
import React, { useState } from 'react';
import { useServiceValidation } from '../../hooks/useAttendantServices';
import { type ServiceValidationData } from '../../types/attendant';

export const ServiceValidation: React.FC = () => {
  const { validationResult, validateService, clearValidation, isValidating } =
    useServiceValidation();
  const [formData, setFormData] = useState<ServiceValidationData>({
    client_id: 0,
    vehicle_id: 0,
    description: '',
    estimated_duration: 60,
    priority: 'medium',
  });

  const handleValidation = async () => {
    if (
      !formData.client_id ||
      !formData.vehicle_id ||
      !formData.description.trim()
    ) {
      return;
    }

    try {
      await validateService(formData);
    } catch (error) {
      console.error('Validation error:', error);
    }
  };

  const handleClear = () => {
    clearValidation();
    setFormData({
      client_id: 0,
      vehicle_id: 0,
      description: '',
      estimated_duration: 60,
      priority: 'medium',
    });
  };

  const getWarnings = () => {
    return validationResult?.warnings &&
      Array.isArray(validationResult.warnings)
      ? (validationResult.warnings as string[])
      : [];
  };

  const getSuggestions = () => {
    return validationResult?.suggestions &&
      Array.isArray(validationResult.suggestions)
      ? (validationResult.suggestions as string[])
      : [];
  };

  const getValidationStatus = () => {
    if (!validationResult) return 'idle';
    return validationResult.is_valid ? 'valid' : 'invalid';
  };

  const status = getValidationStatus();

  return (
    <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <div className="flex items-center justify-between mb-4">
        <h3 className="text-lg font-semibold text-gray-900 flex items-center gap-2">
          <SparklesIcon className="h-5 w-5 text-purple-600" />
          Validação de Serviço
        </h3>

        {validationResult && (
          <button
            onClick={handleClear}
            className="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded transition-colors"
          >
            <XMarkIcon className="h-4 w-4" />
          </button>
        )}
      </div>

      {/* Validation Form */}
      <div className="space-y-3 mb-4">
        <div>
          <label className="block text-xs font-medium text-gray-700 mb-1">
            Cliente
          </label>
          <select
            value={formData.client_id}
            onChange={(e) =>
              setFormData((prev) => ({
                ...prev,
                client_id: Number(e.target.value),
              }))
            }
            className="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
          >
            <option value={0}>Selecione...</option>
            <option value={1}>Cliente 1</option>
            <option value={2}>Cliente 2</option>
          </select>
        </div>

        <div>
          <label className="block text-xs font-medium text-gray-700 mb-1">
            Veículo
          </label>
          <select
            value={formData.vehicle_id}
            onChange={(e) =>
              setFormData((prev) => ({
                ...prev,
                vehicle_id: Number(e.target.value),
              }))
            }
            className="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
          >
            <option value={0}>Selecione...</option>
            <option value={1}>Veículo 1</option>
            <option value={2}>Veículo 2</option>
          </select>
        </div>

        <div>
          <label className="block text-xs font-medium text-gray-700 mb-1">
            Descrição
          </label>
          <textarea
            value={formData.description}
            onChange={(e) =>
              setFormData((prev) => ({ ...prev, description: e.target.value }))
            }
            placeholder="Descreva o serviço..."
            className="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 resize-none"
            rows={2}
          />
        </div>

        <div className="grid grid-cols-2 gap-2">
          <div>
            <label className="block text-xs font-medium text-gray-700 mb-1">
              Duração (min)
            </label>
            <input
              type="number"
              value={formData.estimated_duration}
              onChange={(e) =>
                setFormData((prev) => ({
                  ...prev,
                  estimated_duration: Number(e.target.value),
                }))
              }
              className="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
              min="15"
              step="15"
            />
          </div>

          <div>
            <label className="block text-xs font-medium text-gray-700 mb-1">
              Prioridade
            </label>
            <select
              value={formData.priority}
              onChange={(e) =>
                setFormData((prev) => ({
                  ...prev,
                  priority: e.target.value as 'low' | 'medium' | 'high',
                }))
              }
              className="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
            >
              <option value="low">Baixa</option>
              <option value="medium">Média</option>
              <option value="high">Alta</option>
            </select>
          </div>
        </div>
      </div>

      {/* Validation Button */}
      <button
        onClick={handleValidation}
        disabled={
          isValidating ||
          !formData.client_id ||
          !formData.vehicle_id ||
          !formData.description.trim()
        }
        className="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
      >
        {isValidating ? (
          <>
            <div className="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></div>
            <span>Validando...</span>
          </>
        ) : (
          <>
            <SparklesIcon className="h-4 w-4" />
            <span>Validar Serviço</span>
          </>
        )}
      </button>

      {/* Validation Results */}
      {validationResult && (
        <div
          className={`mt-4 p-4 rounded-lg border ${
            validationResult.is_valid
              ? 'bg-green-50 border-green-200'
              : 'bg-red-50 border-red-200'
          }`}
        >
          <div className="flex items-start gap-3">
            {validationResult.is_valid ? (
              <CheckCircleIcon className="h-5 w-5 text-green-600 mt-0.5" />
            ) : (
              <ExclamationTriangleIcon className="h-5 w-5 text-red-600 mt-0.5" />
            )}

            <div className="flex-1">
              <h4
                className={`font-medium ${
                  validationResult.is_valid ? 'text-green-800' : 'text-red-800'
                }`}
              >
                {validationResult.is_valid
                  ? 'Serviço Válido'
                  : 'Serviço Inválido'}
              </h4>

              {getWarnings().length > 0 && (
                <div className="mt-2 space-y-1">
                  {getWarnings().map((warning: string, index: number) => (
                    <div
                      key={index}
                      className="text-sm text-red-700 flex items-start gap-1"
                    >
                      <ExclamationTriangleIcon className="h-3 w-3 mt-0.5 flex-shrink-0" />
                      {warning}
                    </div>
                  ))}
                </div>
              )}

              {getSuggestions().length > 0 && (
                <div className="mt-2 space-y-1">
                  {getSuggestions().map((suggestion: string, index: number) => (
                    <div
                      key={index}
                      className="text-sm text-blue-700 flex items-start gap-1"
                    >
                      <InformationCircleIcon className="h-3 w-3 mt-0.5 flex-shrink-0" />
                      {suggestion}
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        </div>
      )}

      {/* Status Indicator */}
      <div className="mt-4 flex items-center justify-between text-xs text-gray-500">
        <span>
          Status:{' '}
          {(status === 'idle' && 'Aguardando validação') ||
            (status === 'valid' && 'Válido') ||
            (status === 'invalid' && 'Inválido')}
        </span>

        <div className="flex items-center gap-1">
          <div
            className={`w-2 h-2 rounded-full ${
              status === 'idle'
                ? 'bg-gray-300'
                : status === 'valid'
                  ? 'bg-green-500'
                  : 'bg-red-500'
            }`}
          ></div>
          <span className="capitalize">{status}</span>
        </div>
      </div>
    </div>
  );
};
