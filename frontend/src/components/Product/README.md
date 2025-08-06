# Componentes de Produtos

Esta pasta contém componentes reutilizáveis para gerenciamento de produtos no sistema.

## Componentes Disponíveis

### 1. ProductCard

Exibe informações de um produto individual com opção de adicionar ao carrinho.

```tsx
import { ProductCard } from '@/components/Product';

<ProductCard
  product={product}
  isSelected={false}
  onAddProduct={handleAddProduct}
  compact={false}
/>;
```

**Props:**

- `product`: Dados do produto
- `isSelected`: Se o produto já foi selecionado
- `onAddProduct`: Callback quando produto é adicionado
- `compact`: Layout compacto (padrão: false)

### 2. ProductFilters

Componente para filtrar produtos por busca e categoria.

```tsx
import { ProductFilters } from '@/components/Product';

<ProductFilters
  searchTerm={searchTerm}
  onSearch={setSearchTerm}
  selectedCategory={selectedCategory}
  onCategoryChange={setSelectedCategory}
  categories={categories}
  products={products}
  compact={false}
/>;
```

### 3. ProductList

Lista de produtos com loading e estados vazios.

```tsx
import { ProductList } from '@/components/Product';

<ProductList
  products={filteredProducts}
  isLoading={isLoading}
  searchTerm={searchTerm}
  selectedProductIds={selectedProductIds}
  onAddProduct={handleAddProduct}
  compact={false}
/>;
```

### 4. ProductAddModal

Modal para adicionar produto com quantidade e observações.

```tsx
import { ProductAddModal } from '@/components/Product';

<ProductAddModal
  isOpen={showAddModal}
  onClose={() => setShowAddModal(false)}
  product={selectedProduct}
  onConfirm={handleConfirmAdd}
  maxQuantity={10}
/>;
```

### 5. ProductSelectionModal

Modal completo para seleção de produtos (usado no módulo Technician).

```tsx
import { ProductSelectionModal } from '@/components/Product';

<ProductSelectionModal
  isOpen={isOpen}
  onClose={onClose}
  products={products}
  categories={categories}
  isLoading={isLoading}
  searchTerm={searchTerm}
  onSearch={onSearch}
  onAddProduct={onAddProduct}
  selectedProductIds={selectedProductIds}
  compact={false}
/>;
```

### 6. ProductCatalog

Catálogo completo de produtos com filtros integrados.

```tsx
import { ProductCatalog } from '@/components/Product';

<ProductCatalog
  products={products}
  categories={categories}
  isLoading={isLoading}
  onAddToCart={handleAddToCart}
  selectedProductIds={selectedProductIds}
  title="Catálogo de Produtos"
  showFilters={true}
  compact={false}
/>;
```

### 7. ProductQuickView

Visualização rápida de produto com modal de detalhes.

```tsx
import { ProductQuickView } from '@/components/Product';

<ProductQuickView
  product={product}
  onAddToCart={handleAddToCart}
  isSelected={false}
  compact={false}
/>;
```

## Padrões de Uso

### Layout Compacto vs Padrão

Todos os componentes suportam modo compacto para uso em espaços limitados:

```tsx
// Layout compacto
<ProductCard product={product} compact={true} />

// Layout padrão
<ProductCard product={product} compact={false} />
```

### Estados de Produto

Os componentes lidam automaticamente com:

- Produtos sem estoque
- Produtos já selecionados
- Loading states
- Estados vazios

### Responsividade

Todos os componentes são responsivos e se adaptam a diferentes tamanhos de tela.

## Exemplo de Implementação Completa

```tsx
import React, { useState } from 'react';
import {
  ProductCatalog,
  ProductQuickView,
  type TechnicianProduct,
} from '@/components/Product';

const ProductPage: React.FC = () => {
  const [products, setProducts] = useState<TechnicianProduct[]>([]);
  const [selectedProducts, setSelectedProducts] = useState<number[]>([]);

  const handleAddToCart = (
    product: TechnicianProduct,
    quantity: number,
    notes?: string
  ) => {
    // Lógica para adicionar ao carrinho
    console.log('Adicionando produto:', product.name, quantity, notes);
  };

  return (
    <div className="container mx-auto p-6">
      <ProductCatalog
        products={products}
        categories={[]}
        isLoading={false}
        onAddToCart={handleAddToCart}
        selectedProductIds={selectedProducts}
        title="Nossos Produtos"
        showFilters={true}
        compact={false}
      />
    </div>
  );
};
```

## Tipos TypeScript

Todos os componentes usam o tipo `TechnicianProduct`:

```tsx
interface TechnicianProduct {
  id: number;
  name: string;
  description?: string;
  sku?: string;
  price: number;
  stock_quantity: number;
  category?: {
    id: number;
    name: string;
  };
}
```

## Estilos

Os componentes usam Tailwind CSS e seguem o design system do projeto. Todos incluem:

- Animações suaves
- Estados hover/focus
- Feedback visual
- Acessibilidade básica
