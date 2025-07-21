import {
  CalendarIcon,
  CheckCircleIcon,
  ClockIcon,
  ExclamationTriangleIcon,
  XCircleIcon,
  XMarkIcon,
} from '@heroicons/react/24/outline';
import React, { useState } from 'react';
import { type AttendantService } from '../../types/attendant';

interface UpdateStatusModalProps {
  isOpen: boolean;
  onClose: () => void;
  service: AttendantService | null;
  onUpdateStatus: (
    serviceId: number,
    statusId: number,
    notes?: string
  ) => Promise<void>;
  isLoading?: boolean;
}

interface StatusOption {
  id: number;
  name: string;
  label: string;
  color: string;
  icon: React.ReactNode;
  description: string;
}

export const UpdateStatusModal: React.FC<UpdateStatusModalProps> = ({
  isOpen,
  onClose,
  service,
  onUpdateStatus,
}) => {
  const [selectedStatusId, setSelectedStatusId] = useState<number | null>(null);
  const [notes, setNotes] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Status disponíveis (baseado no backend)
  const statusOptions: StatusOption[] = [
    {
      id: 1,
      name: 'scheduled',
      label: 'Agendado',
      color: 'bg-blue-100 text-blue-700 border-blue-200',
      icon: <CalendarIcon className="h-5 w-5" />,
      description: 'Serviço agendado para data futura',
    },
    {
      id: 2,
      name: 'in_progress',
      label: 'Em Andamento',
      color: 'bg-yellow-100 text-yellow-700 border-yellow-200',
      icon: <ClockIcon className="h-5 w-5" />,
      description: 'Serviço sendo executado',
    },
    {
      id: 3,
      name: 'completed',
      label: 'Concluído',
      color: 'bg-green-100 text-green-700 border-green-200',
      icon: <CheckCircleIcon className="h-5 w-5" />,
      description: 'Serviço finalizado com sucesso',
    },
    {
      id: 4,
      name: 'cancelled',
      label: 'Cancelado',
      color: 'bg-red-100 text-red-700 border-red-200',
      icon: <XCircleIcon className="h-5 w-5" />,
      description: 'Serviço cancelado',
    },
  ];

  // Resetar estado quando modal abrir
  React.useEffect(() => {
    if (isOpen && service) {
      // Mapear status string para ID
      const statusMap: Record<string, number> = {
        scheduled: 1,
        in_progress: 2,
        completed: 3,
        cancelled: 4,
      };
      setSelectedStatusId(statusMap[service.status] || null);
      setNotes('');
    }
  }, [isOpen, service]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!selectedStatusId || !service) return;

    setIsSubmitting(true);
    try {
      await onUpdateStatus(
        service.id,
        selectedStatusId,
        notes.trim() || undefined
      );
      onClose();
    } catch (error) {
      console.error('Erro ao atualizar status:', error);
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleClose = () => {
    if (!isSubmitting) {
      onClose();
    }
  };

  if (!isOpen || !service) return null;

  const currentStatus = statusOptions.find((s) => s.id === selectedStatusId);

  return (
    <div className="fixed inset-0 z-50 overflow-y-auto">
      {/* Backdrop */}
      <div
        className="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
        onClick={handleClose}
      />

      {/* Modal */}
      <div className="flex min-h-full items-center justify-center p-4">
        <div className="relative w-full max-w-md bg-white rounded-xl shadow-xl">
          {/* Header */}
          <div className="flex items-center justify-between p-6 border-b border-gray-200">
            <div>
              <h3 className="text-lg font-semibold text-gray-900">
                Atualizar Status
              </h3>
              <p className="text-sm text-gray-600 mt-1">
                Serviço #{service.service_number}
              </p>
            </div>
            <button
              onClick={handleClose}
              disabled={isSubmitting}
              className="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors disabled:opacity-50"
            >
              <XMarkIcon className="h-5 w-5" />
            </button>
          </div>

          {/* Content */}
          <form onSubmit={handleSubmit} className="p-6">
            {/* Status Atual */}
            <div className="mb-6">
              <label className="block text-sm font-medium text-gray-700 mb-3">
                Status Atual
              </label>
              {currentStatus ? (
                <div
                  className={`flex items-center gap-3 p-3 rounded-lg border ${currentStatus.color}`}
                >
                  {currentStatus.icon}
                  <div>
                    <div className="font-medium">{currentStatus.label}</div>
                    <div className="text-xs opacity-75">
                      {currentStatus.description}
                    </div>
                  </div>
                </div>
              ) : (
                <div className="flex items-center gap-3 p-3 rounded-lg border border-gray-200 bg-gray-50">
                  <ExclamationTriangleIcon className="h-5 w-5 text-gray-500" />
                  <div>
                    <div className="font-medium text-gray-700">
                      Status não definido
                    </div>
                    <div className="text-xs text-gray-500">
                      Selecione um novo status
                    </div>
                  </div>
                </div>
              )}
            </div>

            {/* Novo Status */}
            <div className="mb-6">
              <label className="block text-sm font-medium text-gray-700 mb-3">
                Novo Status *
              </label>
              <div className="space-y-2">
                {statusOptions.map((status) => (
                  <button
                    key={status.id}
                    type="button"
                    onClick={() => setSelectedStatusId(status.id)}
                    className={`w-full flex items-center gap-3 p-3 rounded-lg border transition-all duration-200 ${
                      selectedStatusId === status.id
                        ? `${status.color} ring-2 ring-offset-2 ring-blue-500`
                        : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
                    }`}
                  >
                    {status.icon}
                    <div className="text-left">
                      <div className="font-medium">{status.label}</div>
                      <div className="text-xs opacity-75">
                        {status.description}
                      </div>
                    </div>
                  </button>
                ))}
              </div>
            </div>

            {/* Observações */}
            <div className="mb-6">
              <label
                htmlFor="notes"
                className="block text-sm font-medium text-gray-700 mb-2"
              >
                Observações (opcional)
              </label>
              <textarea
                id="notes"
                value={notes}
                onChange={(e) => setNotes(e.target.value)}
                rows={3}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                placeholder="Adicione observações sobre a mudança de status..."
                disabled={isSubmitting}
              />
            </div>

            {/* Footer */}
            <div className="flex gap-3">
              <button
                type="button"
                onClick={handleClose}
                disabled={isSubmitting}
                className="flex-1 px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition-colors disabled:opacity-50"
              >
                Cancelar
              </button>
              <button
                type="submit"
                disabled={!selectedStatusId || isSubmitting}
                className="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {isSubmitting ? (
                  <div className="flex items-center justify-center gap-2">
                    <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    Atualizando...
                  </div>
                ) : (
                  'Atualizar Status'
                )}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
};
