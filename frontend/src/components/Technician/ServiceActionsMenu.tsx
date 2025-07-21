import {
  ChatBubbleLeftRightIcon,
  DocumentDuplicateIcon,
  EllipsisVerticalIcon,
  ExclamationTriangleIcon,
  EyeIcon,
  PencilIcon,
  PhoneIcon,
  PrinterIcon,
} from '@heroicons/react/24/outline';
import React, { useEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import { type TechnicianService } from '../../types/technician';

interface ServiceActionsMenuProps {
  service: TechnicianService;
  onViewDetails: (service: TechnicianService) => void;
  onUpdateStatus: (service: TechnicianService) => void;
  onDuplicateService?: (service: TechnicianService) => void;
  onPrintService?: (service: TechnicianService) => void;
  onContactClient?: (service: TechnicianService) => void;
  onSendNotification?: (service: TechnicianService) => void;
}

export const ServiceActionsMenu: React.FC<ServiceActionsMenuProps> = ({
  service,
  onViewDetails,
  onUpdateStatus,
  onDuplicateService,
  onPrintService,
  onContactClient,
  onSendNotification,
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const menuRef = useRef<HTMLDivElement>(null);
  const buttonRef = useRef<HTMLButtonElement>(null);

  // Fechar menu quando clicar fora
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (menuRef.current && !menuRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleAction = (action: () => void) => (e: React.MouseEvent) => {
    e.stopPropagation();
    action();
    setIsOpen(false);
  };

  const handleToggleMenu = (e: React.MouseEvent) => {
    e.stopPropagation();
    setIsOpen(!isOpen);
  };

  const getStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
      case 'completed':
      case 'concluído':
        return 'text-green-600';
      case 'in_progress':
      case 'em_andamento':
        return 'text-blue-600';
      case 'pending':
      case 'pendente':
        return 'text-yellow-600';
      case 'cancelled':
      case 'cancelado':
        return 'text-red-600';
      default:
        return 'text-gray-600';
    }
  };

  const renderDropdown = () => {
    if (!isOpen || !buttonRef.current) return null;

    const rect = buttonRef.current.getBoundingClientRect();

    const dropdownContent = (
      <div
        ref={menuRef}
        className="fixed w-56 bg-white rounded-lg shadow-2xl border border-gray-200 py-1 z-[999999]"
        style={{
          top: `${rect.bottom + 4}px`,
          left: `${rect.right - 224}px`,
        }}
      >
        {/* Header do menu */}
        <div className="px-3 py-2 border-b border-gray-100">
          <div className="text-xs font-medium text-gray-900">
            #{service.service_number}
          </div>
          <div className={`text-xs ${getStatusColor(service.status)}`}>
            {service.status}
          </div>
        </div>

        {/* Ações principais */}
        <div className="py-1">
          {/* Ver Detalhes */}
          <button
            onClick={handleAction(() => onViewDetails(service))}
            className="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150"
          >
            <EyeIcon className="h-4 w-4 text-gray-500" />
            <span>Ver Detalhes</span>
          </button>

          {/* Atualizar Status */}
          <button
            onClick={handleAction(() => onUpdateStatus(service))}
            className="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150"
          >
            <PencilIcon className="h-4 w-4 text-gray-500" />
            <span>Atualizar Status</span>
          </button>

          {/* Duplicar Serviço */}
          {onDuplicateService && (
            <button
              onClick={handleAction(() => onDuplicateService(service))}
              className="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150"
            >
              <DocumentDuplicateIcon className="h-4 w-4 text-gray-500" />
              <span>Duplicar Serviço</span>
            </button>
          )}

          {/* Imprimir */}
          {onPrintService && (
            <button
              onClick={handleAction(() => onPrintService(service))}
              className="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150"
            >
              <PrinterIcon className="h-4 w-4 text-gray-500" />
              <span>Imprimir</span>
            </button>
          )}
        </div>

        {/* Separador */}
        <div className="border-t border-gray-100 my-1"></div>

        {/* Ações secundárias */}
        <div className="py-1">
          {/* Contatar Cliente */}
          {onContactClient && (
            <button
              onClick={handleAction(() => onContactClient(service))}
              className="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150"
            >
              <PhoneIcon className="h-4 w-4 text-gray-500" />
              <span>Contatar Cliente</span>
            </button>
          )}

          {/* Notificar Técnico */}
          {onSendNotification && (
            <button
              onClick={handleAction(() => onSendNotification(service))}
              className="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150"
            >
              <ChatBubbleLeftRightIcon className="h-4 w-4 text-gray-500" />
              <span>Notificar Técnico</span>
            </button>
          )}

          {/* Marcar como Urgente */}
          <button
            onClick={handleAction(() => {
              // TODO: Implementar marcação como urgente
              console.log('Marcar como urgente:', service.id);
            })}
            className="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150"
          >
            <ExclamationTriangleIcon className="h-4 w-4 text-gray-500" />
            <span>Marcar como Urgente</span>
          </button>
        </div>
      </div>
    );

    return createPortal(dropdownContent, document.body);
  };

  return (
    <div className="relative">
      {/* Botão de 3 pontos */}
      <button
        ref={buttonRef}
        onClick={handleToggleMenu}
        className="p-1.5 sm:p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        aria-label="Ações do serviço"
      >
        <EllipsisVerticalIcon className="h-3.5 w-3.5 sm:h-4 sm:w-4" />
      </button>

      {/* Renderizar dropdown via portal */}
      {renderDropdown()}
    </div>
  );
};
