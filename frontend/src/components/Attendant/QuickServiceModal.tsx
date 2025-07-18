import {
  CheckCircleIcon,
  ClockIcon,
  ExclamationTriangleIcon,
  PlusIcon,
  SparklesIcon,
  TruckIcon,
  UserIcon,
  XMarkIcon,
} from '@heroicons/react/24/outline';
import React, { useEffect, useState } from 'react';
import {
  useAttendantServices,
  useServiceForm,
  useServiceValidation,
} from '../../hooks/useAttendantServices';
import { type ServiceTemplate } from '../../types/attendant';

interface QuickServiceModalProps {
  isOpen: boolean;
  onClose: () => void;
  clientId?: number | null;
  vehicleId?: number | null;
}

export const QuickServiceModal: React.FC<QuickServiceModalProps> = ({
  isOpen,
  onClose,
  clientId,
  vehicleId,
}) => {
  const { createQuickService, templates } = useAttendantServices();
  const { formData, updateFormData, resetForm, isFormValid } = useServiceForm();
  const { validationResult, validateService, clearValidation } =
    useServiceValidation();

  const [selectedTemplate, setSelectedTemplate] =
    useState<ServiceTemplate | null>(null);
  const [showTemplates, setShowTemplates] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [filteredTemplates, setFilteredTemplates] = useState<ServiceTemplate[]>(
    []
  );

  // Initialize form with client and vehicle if provided
  useEffect(() => {
    if (clientId && vehicleId) {
      updateFormData({ client_id: clientId, vehicle_id: vehicleId });
    }
  }, [clientId, vehicleId, updateFormData]);

  // Filter templates based on search
  useEffect(() => {
    if (searchTerm) {
      const filtered = templates.filter(
        (template) =>
          template.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
          template.description.toLowerCase().includes(searchTerm.toLowerCase())
      );
      setFilteredTemplates(filtered);
    } else {
      setFilteredTemplates(templates);
    }
  }, [searchTerm, templates]);

  const handleTemplateSelect = (template: ServiceTemplate) => {
    setSelectedTemplate(template);
    updateFormData({
      description: template.description,
      estimated_duration: template.estimated_duration,
      priority: template.priority,
      notes: template.notes || '',
    });
    setShowTemplates(false);
    setSearchTerm('');
  };

  const handleSubmit = async () => {
    if (!isFormValid()) {
      return;
    }

    try {
      // Validate service data
      const validationData = {
        client_id: formData.client_id,
        vehicle_id: formData.vehicle_id,
        description: formData.description,
        estimated_duration: formData.estimated_duration,
        priority: formData.priority,
      };

      const validation = await validateService(validationData);

      if (!validation.is_valid) {
        return;
      }

      // Create service
      await createQuickService({
        client_id: formData.client_id,
        vehicle_id: formData.vehicle_id,
        description: formData.description,
        estimated_duration: formData.estimated_duration,
        priority: formData.priority,
        notes: formData.notes,
        template_id: selectedTemplate?.id,
      });

      handleClose();
    } catch (error) {
      console.error('Error creating service:', error);
    }
  };

  const handleClose = () => {
    resetForm();
    setSelectedTemplate(null);
    setShowTemplates(false);
    setSearchTerm('');
    clearValidation();
    onClose();
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div className="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        {/* Header */}
        <div className="sticky top-0 bg-white rounded-t-2xl p-6 border-b border-gray-100 z-10">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <div className="p-3 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl shadow-lg">
                <PlusIcon className="h-7 w-7 text-white" />
              </div>
              <div>
                <h3 className="text-2xl font-bold text-gray-900">
                  Serviço Rápido
                </h3>
                <p className="text-gray-600 text-sm">
                  Crie um serviço de forma rápida e eficiente
                </p>
              </div>
            </div>
            <button
              onClick={handleClose}
              className="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
            >
              <XMarkIcon className="h-6 w-6" />
            </button>
          </div>
        </div>

        {/* Content */}
        <div className="p-6 space-y-6">
          {/* Template Selection */}
          <div className="space-y-3">
            <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
              <SparklesIcon className="h-4 w-4 text-purple-600" />
              Template (Opcional)
            </label>

            {selectedTemplate ? (
              <div className="p-4 bg-purple-50 border border-purple-200 rounded-xl">
                <div className="flex items-center justify-between">
                  <div>
                    <h4 className="font-semibold text-purple-900">
                      {selectedTemplate.name}
                    </h4>
                    <p className="text-sm text-purple-700">
                      {selectedTemplate.description}
                    </p>
                    <div className="flex items-center gap-4 mt-2 text-xs text-purple-600">
                      <span>⏱️ {selectedTemplate.estimated_duration}min</span>
                      <span>🎯 {selectedTemplate.priority}</span>
                      <span>📂 {selectedTemplate.category}</span>
                    </div>
                  </div>
                  <button
                    onClick={() => setSelectedTemplate(null)}
                    className="text-purple-600 hover:text-purple-800"
                  >
                    <XMarkIcon className="h-5 w-5" />
                  </button>
                </div>
              </div>
            ) : (
              <div className="space-y-2">
                <button
                  onClick={() => setShowTemplates(!showTemplates)}
                  className="w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-xl hover:border-purple-400 hover:bg-purple-50 transition-colors text-gray-600 hover:text-purple-700"
                >
                  <div className="flex items-center justify-center gap-2">
                    <SparklesIcon className="h-5 w-5" />
                    <span>Selecionar Template</span>
                  </div>
                </button>

                {showTemplates && (
                  <div className="border border-gray-200 rounded-xl p-4 bg-gray-50">
                    <input
                      type="text"
                      placeholder="Buscar templates..."
                      value={searchTerm}
                      onChange={(e) => setSearchTerm(e.target.value)}
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                    />

                    <div className="mt-3 max-h-48 overflow-y-auto space-y-2">
                      {filteredTemplates.map((template) => (
                        <button
                          key={template.id}
                          onClick={() => handleTemplateSelect(template)}
                          className="w-full p-3 bg-white border border-gray-200 rounded-lg hover:border-purple-400 hover:bg-purple-50 transition-colors text-left"
                        >
                          <div className="flex items-center justify-between">
                            <div>
                              <h4 className="font-medium text-gray-900">
                                {template.name}
                              </h4>
                              <p className="text-sm text-gray-600">
                                {template.description}
                              </p>
                            </div>
                            <div className="text-xs text-gray-500">
                              <div>⏱️ {template.estimated_duration}min</div>
                              <div>🎯 {template.priority}</div>
                            </div>
                          </div>
                        </button>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            )}
          </div>

          {/* Client and Vehicle Selection */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-3">
              <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                <UserIcon className="h-4 w-4 text-blue-600" />
                Cliente
              </label>
              <select
                value={formData.client_id}
                onChange={(e) =>
                  updateFormData({ client_id: Number(e.target.value) })
                }
                className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value={0}>Selecione um cliente</option>
                {/* Client options would be populated from API */}
                <option value={1}>João Silva</option>
                <option value={2}>Maria Santos</option>
              </select>
            </div>

            <div className="space-y-3">
              <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                <TruckIcon className="h-4 w-4 text-green-600" />
                Veículo
              </label>
              <select
                value={formData.vehicle_id}
                onChange={(e) =>
                  updateFormData({ vehicle_id: Number(e.target.value) })
                }
                className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
              >
                <option value={0}>Selecione um veículo</option>
                {/* Vehicle options would be populated from API */}
                <option value={1}>Toyota Corolla - ABC-1234</option>
                <option value={2}>Honda Civic - XYZ-5678</option>
              </select>
            </div>
          </div>

          {/* Service Description */}
          <div className="space-y-3">
            <label className="block text-sm font-semibold text-gray-700">
              Descrição do Serviço
            </label>
            <textarea
              value={formData.description}
              onChange={(e) => updateFormData({ description: e.target.value })}
              placeholder="Descreva o serviço a ser realizado..."
              className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
              rows={3}
            />
          </div>

          {/* Duration and Priority */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-3">
              <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                <ClockIcon className="h-4 w-4 text-purple-600" />
                Duração Estimada (min)
              </label>
              <input
                type="number"
                value={formData.estimated_duration}
                onChange={(e) =>
                  updateFormData({ estimated_duration: Number(e.target.value) })
                }
                min="15"
                step="15"
                className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
              />
            </div>

            <div className="space-y-3">
              <label className="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                <ExclamationTriangleIcon className="h-4 w-4 text-orange-600" />
                Prioridade
              </label>
              <select
                value={formData.priority}
                onChange={(e) =>
                  updateFormData({
                    priority: e.target.value as 'low' | 'medium' | 'high',
                  })
                }
                className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
              >
                <option value="low">🟢 Baixa</option>
                <option value="medium">🟡 Média</option>
                <option value="high">🔴 Alta</option>
              </select>
            </div>
          </div>

          {/* Notes */}
          <div className="space-y-3">
            <label className="block text-sm font-semibold text-gray-700">
              Observações
            </label>
            <textarea
              value={formData.notes}
              onChange={(e) => updateFormData({ notes: e.target.value })}
              placeholder="Observações adicionais..."
              className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
              rows={2}
            />
          </div>

          {/* Validation Results */}
          {validationResult && (
            <div
              className={`p-4 rounded-xl border ${
                validationResult.is_valid
                  ? 'bg-green-50 border-green-200'
                  : 'bg-red-50 border-red-200'
              }`}
            >
              <div className="flex items-center gap-2 mb-2">
                {validationResult.is_valid ? (
                  <CheckCircleIcon className="h-5 w-5 text-green-600" />
                ) : (
                  <ExclamationTriangleIcon className="h-5 w-5 text-red-600" />
                )}
                <span
                  className={`font-medium ${
                    validationResult.is_valid
                      ? 'text-green-800'
                      : 'text-red-800'
                  }`}
                >
                  {validationResult.is_valid
                    ? 'Dados válidos'
                    : 'Dados inválidos'}
                </span>
              </div>

              {validationResult.warnings.length > 0 && (
                <div className="space-y-1">
                  {validationResult.warnings.map(
                    (warning: string, index: number) => (
                      <p key={index} className="text-sm text-red-700">
                        ⚠️ {warning}
                      </p>
                    )
                  )}
                </div>
              )}

              {validationResult.suggestions.length > 0 && (
                <div className="space-y-1 mt-2">
                  {validationResult.suggestions.map(
                    (suggestion: string, index: number) => (
                      <p key={index} className="text-sm text-blue-700">
                        💡 {suggestion}
                      </p>
                    )
                  )}
                </div>
              )}
            </div>
          )}
        </div>

        {/* Footer */}
        <div className="sticky bottom-0 bg-white rounded-b-2xl p-6 border-t border-gray-100">
          <div className="flex justify-end gap-3">
            <button
              onClick={handleClose}
              className="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors font-medium"
            >
              Cancelar
            </button>
            <button
              onClick={handleSubmit}
              disabled={!isFormValid()}
              className="px-8 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 disabled:transform-none disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-3"
            >
              <PlusIcon className="h-5 w-5" />
              <span>Criar Serviço</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};
