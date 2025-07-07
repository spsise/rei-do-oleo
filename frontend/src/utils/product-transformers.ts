import type { Product } from '../types/product';

/**
 * Converte campos numéricos de string para number
 */
export const transformProductData = (product: Product): Product => {
  return {
    ...product,
    price:
      typeof product.price === 'string'
        ? parseFloat(product.price)
        : product.price,
    cost_price: product.cost_price
      ? typeof product.cost_price === 'string'
        ? parseFloat(product.cost_price)
        : product.cost_price
      : undefined,
    stock_quantity:
      typeof product.stock_quantity === 'string'
        ? parseInt(product.stock_quantity)
        : product.stock_quantity,
    min_stock:
      typeof product.min_stock === 'string'
        ? parseInt(product.min_stock)
        : product.min_stock,
    weight: product.weight
      ? typeof product.weight === 'string'
        ? parseFloat(product.weight)
        : product.weight
      : undefined,
    warranty_months: product.warranty_months
      ? typeof product.warranty_months === 'string'
        ? parseInt(product.warranty_months)
        : product.warranty_months
      : undefined,
  };
};

/**
 * Transforma um array de produtos
 */
export const transformProductsArray = (products: Product[]): Product[] => {
  return products.map(transformProductData);
};
