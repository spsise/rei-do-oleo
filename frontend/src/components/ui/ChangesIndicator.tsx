import React from 'react';

interface ChangesIndicatorProps {
  changedFields: string[];
  className?: string;
}

export const ChangesIndicator: React.FC<ChangesIndicatorProps> = ({
  changedFields,
  className = '',
}) => {
  if (changedFields.length === 0) {
    return null;
  }

  return (
    <div className={`text-xs text-gray-500 ${className}`}>
      <span className="font-medium">Campos alterados:</span>{' '}
      {changedFields.slice(0, 3).join(', ')}
      {changedFields.length > 3 && ` +${changedFields.length - 3} mais`}
    </div>
  );
};

interface FieldChangeIndicatorProps {
  isChanged: boolean;
  children: React.ReactNode;
  className?: string;
}

export const FieldChangeIndicator: React.FC<FieldChangeIndicatorProps> = ({
  isChanged,
  children,
  className = '',
}) => {
  return (
    <div className={`relative ${className}`}>
      {children}
      {isChanged && (
        <div className="absolute -top-1 -right-1 w-3 h-3 bg-orange-500 rounded-full border-2 border-white shadow-sm"></div>
      )}
    </div>
  );
};
