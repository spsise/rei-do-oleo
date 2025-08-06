import {
  CheckCircleIcon,
  ExclamationTriangleIcon,
} from '@heroicons/react/24/outline';
import React from 'react';

interface ChangesIndicatorProps {
  isDirty: boolean;
  changedFields?: string[];
  changedFieldsCount?: number;
  showDetails?: boolean;
  className?: string;
  variant?: 'compact' | 'detailed' | 'minimal';
}

export const ChangesIndicator: React.FC<ChangesIndicatorProps> = ({
  isDirty,
  changedFields = [],
  changedFieldsCount = 0,
  showDetails = false,
  className = '',
  variant = 'compact',
}) => {
  if (!isDirty) {
    return (
      <div className={`flex items-center gap-2 text-green-600 ${className}`}>
        <CheckCircleIcon className="w-4 h-4" />
        <span className="text-sm font-medium">Sem alterações</span>
      </div>
    );
  }

  const renderCompact = () => (
    <div className={`flex items-center gap-2 text-yellow-600 ${className}`}>
      <ExclamationTriangleIcon className="w-4 h-4" />
      <span className="text-sm font-medium">
        {changedFieldsCount > 0
          ? `${changedFieldsCount} alteração${changedFieldsCount > 1 ? 'ões' : ''}`
          : 'Alterações detectadas'}
      </span>
    </div>
  );

  const renderDetailed = () => (
    <div className={`flex items-center gap-2 text-yellow-600 ${className}`}>
      <ExclamationTriangleIcon className="w-4 h-4" />
      <div className="flex flex-col">
        <span className="text-sm font-medium">
          {changedFieldsCount > 0
            ? `${changedFieldsCount} alteração${changedFieldsCount > 1 ? 'ões' : ''}`
            : 'Alterações detectadas'}
        </span>
        {showDetails && changedFields.length > 0 && (
          <span className="text-xs text-gray-500">
            Campos: {changedFields.slice(0, 3).join(', ')}
            {changedFields.length > 3 && ` +${changedFields.length - 3} mais`}
          </span>
        )}
      </div>
    </div>
  );

  const renderMinimal = () => (
    <div className={`flex items-center gap-1 ${className}`}>
      <div className="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></div>
      {changedFieldsCount > 0 && (
        <span className="text-xs bg-yellow-100 text-yellow-800 px-1.5 py-0.5 rounded-full font-medium">
          {changedFieldsCount}
        </span>
      )}
    </div>
  );

  switch (variant) {
    case 'detailed':
      return renderDetailed();
    case 'minimal':
      return renderMinimal();
    case 'compact':
    default:
      return renderCompact();
  }
};

// Componente de badge para mostrar mudanças específicas
interface ChangesBadgeProps {
  field: string;
  className?: string;
}

export const ChangesBadge: React.FC<ChangesBadgeProps> = ({
  field,
  className = '',
}) => {
  return (
    <span
      className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 ${className}`}
    >
      <div className="w-1 h-1 bg-yellow-400 rounded-full mr-1"></div>
      {field}
    </span>
  );
};

// Componente de lista de mudanças
interface ChangesListProps {
  changedFields: string[];
  className?: string;
  maxItems?: number;
}

export const ChangesList: React.FC<ChangesListProps> = ({
  changedFields,
  className = '',
  maxItems = 5,
}) => {
  const displayFields = changedFields.slice(0, maxItems);
  const remainingCount = changedFields.length - maxItems;

  return (
    <div className={`space-y-1 ${className}`}>
      <span className="text-sm font-medium text-gray-700">
        Campos alterados:
      </span>
      <div className="flex flex-wrap gap-1">
        {displayFields.map((field) => (
          <ChangesBadge key={field} field={field} />
        ))}
        {remainingCount > 0 && (
          <span className="text-xs text-gray-500 px-2 py-1">
            +{remainingCount} mais
          </span>
        )}
      </div>
    </div>
  );
};

// Componente de alerta de mudanças não salvas
interface UnsavedChangesAlertProps {
  isDirty: boolean;
  onSave?: () => void;
  onDiscard?: () => void;
  className?: string;
}

export const UnsavedChangesAlert: React.FC<UnsavedChangesAlertProps> = ({
  isDirty,
  onSave,
  onDiscard,
  className = '',
}) => {
  if (!isDirty) return null;

  return (
    <div
      className={`bg-yellow-50 border border-yellow-200 rounded-md p-4 ${className}`}
    >
      <div className="flex items-start">
        <ExclamationTriangleIcon className="w-5 h-5 text-yellow-400 mt-0.5" />
        <div className="ml-3 flex-1">
          <h3 className="text-sm font-medium text-yellow-800">
            Alterações não salvas
          </h3>
          <p className="text-sm text-yellow-700 mt-1">
            Você tem alterações não salvas. Deseja salvar antes de sair?
          </p>
          <div className="flex gap-2 mt-3">
            {onSave && (
              <button
                onClick={onSave}
                className="px-3 py-1.5 text-xs font-medium bg-yellow-600 text-white rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500"
              >
                Salvar
              </button>
            )}
            {onDiscard && (
              <button
                onClick={onDiscard}
                className="px-3 py-1.5 text-xs font-medium bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
              >
                Descartar
              </button>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};
