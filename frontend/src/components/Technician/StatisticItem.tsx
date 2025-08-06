import React from 'react';

interface StatisticItemProps {
  value: string | number;
  label: string;
  color: 'blue' | 'green' | 'yellow' | 'purple';
}

const colorClasses = {
  blue: 'border-blue-200 text-blue-600',
  green: 'border-green-200 text-green-600',
  yellow: 'border-yellow-200 text-yellow-600',
  purple: 'border-purple-200 text-purple-600',
};

export const StatisticItem: React.FC<StatisticItemProps> = ({
  value,
  label,
  color,
}) => {
  return (
    <div
      className={`text-center p-3 bg-white rounded-lg border ${colorClasses[color]}`}
    >
      <div className="text-lg sm:text-xl font-bold">{value}</div>
      <div className="text-xs sm:text-sm text-gray-600">{label}</div>
    </div>
  );
};
