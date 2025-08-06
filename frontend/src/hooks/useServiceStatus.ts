import { useState } from 'react';
import { toast } from 'react-hot-toast';
import {
  technicianService,
  type UpdateServiceStatusData,
} from '../services/technician.service';

export const useServiceStatus = () => {
  const [isUpdatingStatus, setIsUpdatingStatus] = useState(false);

  const updateServiceStatus = async (
    serviceId: number,
    statusId: number,
    notes?: string
  ): Promise<void> => {
    setIsUpdatingStatus(true);

    try {
      // Mapear status_id para status string (baseado no backend)
      const statusMap: Record<
        number,
        'in_progress' | 'completed' | 'cancelled'
      > = {
        1: 'in_progress', // scheduled -> in_progress (quando iniciar)
        2: 'in_progress',
        3: 'completed',
        4: 'cancelled',
      };

      const statusString = statusMap[statusId];
      if (!statusString) {
        throw new Error('Status inválido');
      }

      const updateData: UpdateServiceStatusData = {
        status: statusString,
        notes,
      };

      const response = await technicianService.updateServiceStatus(
        serviceId,
        updateData
      );

      if (response.status === 'success') {
        toast.success('Status atualizado com sucesso!');
      } else {
        throw new Error(response.message || 'Erro ao atualizar status');
      }
    } catch (error) {
      console.error('Erro ao atualizar status:', error);
      toast.error('Erro ao atualizar status do serviço');
      throw error;
    } finally {
      setIsUpdatingStatus(false);
    }
  };

  return {
    updateServiceStatus,
    isUpdatingStatus,
  };
};
