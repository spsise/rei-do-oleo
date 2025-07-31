import type { Service } from '../types/service';
import type { TechnicianServiceItem } from '../types/technician';

// Interface unificada para dados de serviço no modal
export interface UnifiedServiceData {
  id: number;
  service_number: string;
  description: string;
  scheduled_at?: string;
  started_at?: string;
  completed_at?: string;
  mileage_at_service: number;
  estimated_duration: number;
  status: {
    id: number;
    name: string;
    label: string;
    color: string;
  };
  financial: {
    labor_cost: number;
    items_total: number;
    total_amount: number;
    discount_amount: number;
    final_amount: number;
  };
  vehicle: {
    id: number;
    license_plate: string;
    brand: string;
    model: string;
    year: number;
  };
  items: TechnicianServiceItem[];
  observations: string;
  notes: string;
}

export class ServiceAdapter {
  /**
   * Converte Service para UnifiedServiceData
   */
  static fromService(service: Service): UnifiedServiceData {
    return {
      id: service.id,
      service_number: service.service_number,
      description: service.description || '',
      scheduled_at: service.scheduled_date,
      started_at: service.started_at,
      completed_at: service.finished_at,
      mileage_at_service: service.vehicle?.mileage_at_service || 0,
      estimated_duration: service.estimated_duration || 60,
      status: {
        id: service.status?.id || 1,
        name: service.status?.name || 'pending',
        label: service.status?.label || 'Pendente',
        color: service.status?.color || 'gray',
      },
      financial: {
        labor_cost: service.financial?.labor_cost || 0,
        items_total: service.financial?.items_total || 0,
        total_amount: service.financial?.total_amount
          ? parseFloat(service.financial.total_amount)
          : 0,
        discount_amount: service.financial?.discount || 0,
        final_amount: service.financial?.total_amount
          ? parseFloat(service.financial.total_amount)
          : 0,
      },
      vehicle: {
        id: service.vehicle?.id || 0,
        license_plate: service.vehicle?.license_plate || '',
        brand: service.vehicle?.brand || '',
        model: service.vehicle?.model || '',
        year: service.vehicle?.year || 0,
      },
      items:
        service.items?.map((item) => ({
          id: `item-${service.id}-${item.product?.id || item.product_id}`,
          product_id:
            item.product?.id || parseInt(String(item.product_id || 0)),
          quantity: parseInt(String(item.quantity || 0)),
          unit_price: parseFloat(String(item.unit_price || 0)),
          total_price: parseFloat(String(item.total_price || 0)),
          notes: item.notes || '',
          product: item.product
            ? {
                id: item.product.id,
                name: item.product.name,
                sku: item.product.sku,
                price: 0, // Não disponível no ServiceItem
                stock_quantity: item.product.current_stock,
                category: item.product.category
                  ? {
                      id: parseInt(item.product.category),
                      name: item.product.category,
                    }
                  : undefined,
              }
            : undefined,
        })) || [],
      observations: service.observations || '',
      notes: service.internal_notes || '',
    };
  }
}
