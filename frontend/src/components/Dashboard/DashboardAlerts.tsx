import {
  ExclamationTriangleIcon,
  InformationCircleIcon,
  XCircleIcon,
} from '@heroicons/react/24/outline';
import React from 'react';
import type { DashboardAlert } from '../../services';

interface DashboardAlertsProps {
  alerts: DashboardAlert[];
}

const getAlertIcon = (severity: DashboardAlert['severity']) => {
  switch (severity) {
    case 'error':
      return XCircleIcon;
    case 'warning':
      return ExclamationTriangleIcon;
    case 'info':
    default:
      return InformationCircleIcon;
  }
};

const getAlertColor = (severity: DashboardAlert['severity']) => {
  switch (severity) {
    case 'error':
      return 'bg-red-50 border-red-200 text-red-800';
    case 'warning':
      return 'bg-yellow-50 border-yellow-200 text-yellow-800';
    case 'info':
    default:
      return 'bg-blue-50 border-blue-200 text-blue-800';
  }
};

const getIconColor = (severity: DashboardAlert['severity']) => {
  switch (severity) {
    case 'error':
      return 'text-red-400';
    case 'warning':
      return 'text-yellow-400';
    case 'info':
    default:
      return 'text-blue-400';
  }
};

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

export const DashboardAlerts: React.FC<DashboardAlertsProps> = ({ alerts }) => {
  if (alerts.length === 0) {
    return (
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div className="text-center">
          <InformationCircleIcon className="mx-auto h-12 w-12 text-gray-400" />
          <h3 className="mt-2 text-sm font-medium text-gray-900">
            Nenhum alerta
          </h3>
          <p className="mt-1 text-sm text-gray-500">
            Tudo está funcionando normalmente.
          </p>
        </div>
      </div>
    );
  }

  return (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200">
      <div className="px-6 py-4 border-b border-gray-200">
        <div className="flex items-center justify-between">
          <h3 className="text-lg font-medium text-gray-900">Alertas</h3>
          <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
            {alerts.length}
          </span>
        </div>
      </div>

      <div className="divide-y divide-gray-200">
        {alerts.map((alert, index) => {
          const Icon = getAlertIcon(alert.severity);

          return (
            <div
              key={index}
              className={`px-6 py-4 border-l-4 ${getAlertColor(alert.severity)}`}
            >
              <div className="flex">
                <div className="flex-shrink-0">
                  <Icon className={`h-5 w-5 ${getIconColor(alert.severity)}`} />
                </div>
                <div className="ml-3 flex-1">
                  <h4 className="text-sm font-medium">{alert.title}</h4>
                  <p className="mt-1 text-sm">{alert.message}</p>
                  <p className="mt-1 text-xs opacity-75">
                    {formatDate(alert.created_at)}
                  </p>
                </div>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
};
