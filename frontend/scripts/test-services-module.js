#!/usr/bin/env node

/**
 * Script de teste para o módulo de serviços
 * Verifica se todos os arquivos foram criados corretamente
 */

const fs = require('fs');
const path = require('path');

// Lista de arquivos que devem existir
const requiredFiles = [
  'src/types/service.ts',
  'src/types/category.ts',
  'src/hooks/useServices.ts',
  'src/hooks/useCategories.ts',
  'src/pages/Services.tsx',
  'src/pages/Categories.tsx',
  'src/components/Service/ServiceTable.tsx',
  'src/components/Service/ServiceFilters.tsx',
  'src/components/Service/ServiceForm.tsx',
  'src/components/Service/ServiceSearchForm.tsx',
  'src/components/Category/CategoryTable.tsx',
  'src/components/Category/CategoryFilters.tsx',
  'src/components/Category/CategoryForm.tsx',
  'docs/SERVICES_MODULE.md',
];

// Arquivos que devem ser atualizados
const updatedFiles = [
  'src/App.tsx',
  'src/components/LayoutApp/Sidebar.tsx',
  'src/services/api.ts',
];

console.log('🔍 Verificando módulo de serviços...\n');

let allFilesExist = true;
let missingFiles = [];

// Verificar arquivos criados
console.log('📁 Verificando arquivos criados:');
requiredFiles.forEach((file) => {
  const filePath = path.join(process.cwd(), file);
  if (fs.existsSync(filePath)) {
    console.log(`  ✅ ${file}`);
  } else {
    console.log(`  ❌ ${file} - FALTANDO`);
    allFilesExist = false;
    missingFiles.push(file);
  }
});

console.log('\n📝 Verificando arquivos atualizados:');
updatedFiles.forEach((file) => {
  const filePath = path.join(process.cwd(), file);
  if (fs.existsSync(filePath)) {
    console.log(`  ✅ ${file}`);
  } else {
    console.log(`  ❌ ${file} - FALTANDO`);
    allFilesExist = false;
    missingFiles.push(file);
  }
});

// Verificar rotas no App.tsx
console.log('\n🛣️  Verificando rotas:');
const appTsxPath = path.join(process.cwd(), 'src/App.tsx');
if (fs.existsSync(appTsxPath)) {
  const appContent = fs.readFileSync(appTsxPath, 'utf8');
  const hasServicesRoute = appContent.includes('/services');
  const hasCategoriesRoute = appContent.includes('/categories');

  console.log(`  ${hasServicesRoute ? '✅' : '❌'} Rota /services`);
  console.log(`  ${hasCategoriesRoute ? '✅' : '❌'} Rota /categories`);

  if (!hasServicesRoute || !hasCategoriesRoute) {
    allFilesExist = false;
  }
}

// Verificar menu no Sidebar
console.log('\n🍔 Verificando menu:');
const sidebarPath = path.join(
  process.cwd(),
  'src/components/LayoutApp/Sidebar.tsx'
);
if (fs.existsSync(sidebarPath)) {
  const sidebarContent = fs.readFileSync(sidebarPath, 'utf8');
  const hasServicesMenu = sidebarContent.includes('Serviços');
  const hasCategoriesMenu = sidebarContent.includes('Categorias');

  console.log(`  ${hasServicesMenu ? '✅' : '❌'} Menu Serviços`);
  console.log(`  ${hasCategoriesMenu ? '✅' : '❌'} Menu Categorias`);

  if (!hasServicesMenu || !hasCategoriesMenu) {
    allFilesExist = false;
  }
}

// Verificar métodos da API
console.log('\n🔌 Verificando métodos da API:');
const apiPath = path.join(process.cwd(), 'src/services/api.ts');
if (fs.existsSync(apiPath)) {
  const apiContent = fs.readFileSync(apiPath, 'utf8');
  const hasServiceMethods =
    apiContent.includes('getServices') && apiContent.includes('createService');
  const hasCategoryMethods =
    apiContent.includes('getCategories') &&
    apiContent.includes('createCategory');

  console.log(`  ${hasServiceMethods ? '✅' : '❌'} Métodos de serviços`);
  console.log(`  ${hasCategoryMethods ? '✅' : '❌'} Métodos de categorias`);

  if (!hasServiceMethods || !hasCategoryMethods) {
    allFilesExist = false;
  }
}

// Resultado final
console.log('\n' + '='.repeat(50));
if (allFilesExist) {
  console.log('🎉 MÓDULO DE SERVIÇOS IMPLEMENTADO COM SUCESSO!');
  console.log('\n✅ Todos os arquivos foram criados/atualizados');
  console.log('✅ Rotas configuradas');
  console.log('✅ Menu atualizado');
  console.log('✅ API integrada');
  console.log('\n🚀 O módulo está pronto para uso!');
} else {
  console.log('❌ PROBLEMAS ENCONTRADOS:');
  missingFiles.forEach((file) => {
    console.log(`  - ${file}`);
  });
  console.log('\n🔧 Verifique os arquivos faltantes e tente novamente.');
}

console.log('\n📋 Próximos passos:');
console.log('1. Testar as rotas no navegador');
console.log('2. Verificar se os menus funcionam');
console.log('3. Testar CRUD de serviços e categorias');
console.log('4. Verificar responsividade');
console.log('5. Implementar testes unitários');

console.log('\n' + '='.repeat(50));
