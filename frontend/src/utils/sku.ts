/**
 * Normalizes SKU by converting to uppercase and trimming whitespace
 * @param sku - The SKU to normalize
 * @returns Normalized SKU in uppercase
 */
export const normalizeSku = (sku: string): string => {
  return sku.trim().toUpperCase();
};

/**
 * Formats SKU for display (ensures it's always uppercase)
 * @param sku - The SKU to format
 * @returns Formatted SKU
 */
export const formatSku = (sku: string): string => {
  return sku ? normalizeSku(sku) : '';
};

/**
 * Validates if SKU is properly formatted
 * @param sku - The SKU to validate
 * @returns True if SKU is valid
 */
export const isValidSku = (sku: string): boolean => {
  const normalized = normalizeSku(sku);
  return normalized.length > 0 && normalized.length <= 50;
};

/**
 * Generates a display-friendly SKU with proper formatting
 * @param sku - The SKU to format for display
 * @returns Formatted SKU for display
 */
export const getDisplaySku = (sku?: string): string => {
  if (!sku) return '-';
  return formatSku(sku);
};
