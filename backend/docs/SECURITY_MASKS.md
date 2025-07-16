# Sistema de Máscaras de Segurança

## Visão Geral

O sistema de máscaras de segurança foi implementado para proteger informações sensíveis dos clientes (email, telefone e documentos) de serem completamente visíveis para usuários não autorizados.

## Como Funciona

### 1. Helper de Máscaras (`SecurityMaskHelper`)

O helper `SecurityMaskHelper` contém métodos para aplicar máscaras em diferentes tipos de dados:

#### Máscara de Email

```php
// Exemplo: john.doe@example.com -> j***.d**@e******.com
$maskedEmail = SecurityMaskHelper::maskEmail('john.doe@example.com');
```

#### Máscara de Telefone

```php
// Exemplo: (11) 99999-9999 -> (11) 9****-9999
$maskedPhone = SecurityMaskHelper::maskPhone('(11) 99999-9999');
```

#### Máscara de Documento

```php
// CPF: 123.456.789-01 -> 123.***.***-01
$maskedCpf = SecurityMaskHelper::maskDocument('123.456.789-01');

// CNPJ: 12.345.678/0001-90 -> 12.***.***/****-90
$maskedCnpj = SecurityMaskHelper::maskDocument('12.345.678/0001-90');
```

### 2. Máscara Condicional

O método `conditionalMask()` verifica as permissões do usuário e aplica a máscara apenas se necessário:

```php
$email = SecurityMaskHelper::conditionalMask($user->email, 'email');
```

### 3. Verificação de Permissões

O sistema verifica se o usuário tem permissão para ver dados sensíveis através de:

- Permissão específica: `view.sensitive.data`
- Roles específicos: `admin`, `manager`, `technician`

## Implementação

### 1. No Resource

```php
use App\Support\Helpers\SecurityMaskHelper;

class TechnicianSearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'client' => [
                'id' => (int) $this['client']->id,
                'name' => $this['client']->name ?? '',
                'email' => SecurityMaskHelper::conditionalMask($this['client']->email ?? '', 'email'),
                'phone' => SecurityMaskHelper::conditionalMask($this['client']->phone01 ?? '', 'phone'),
                'document' => SecurityMaskHelper::conditionalMask($this['client']->cpf ?? $this['client']->cnpj ?? '', 'document'),
            ],
            // ... outros campos
        ];
    }
}
```

### 2. Middleware

O middleware `CheckSensitiveDataPermission` pode ser usado nas rotas:

```php
Route::middleware(['auth:sanctum', 'sensitive.data'])->group(function () {
    Route::get('/technician/search', [TechnicianController::class, 'search']);
});
```

### 3. Configuração de Permissões

Para dar permissão a um usuário para ver dados sensíveis:

```php
// Via permissão específica
$user->givePermissionTo('view.sensitive.data');

// Via role
$user->assignRole('admin'); // ou 'manager', 'technician'
```

## Exemplos de Máscaras

### Email

- Original: `john.doe@example.com`
- Mascarado: `j***.d**@e******.com`

### Telefone

- Original: `(11) 99999-9999`
- Mascarado: `(11) 9****-9999`

### CPF

- Original: `123.456.789-01`
- Mascarado: `123.***.***-01`

### CNPJ

- Original: `12.345.678/0001-90`
- Mascarado: `12.***.***/****-90`

## Testes

Os testes unitários estão disponíveis em `tests/Unit/SecurityMaskHelperTest.php`:

```bash
php artisan test tests/Unit/SecurityMaskHelperTest.php
```

## Segurança

### Pontos Importantes

1. **Validação de Email**: O sistema valida se o email é válido antes de aplicar a máscara
2. **Formato de Telefone**: Suporta formatos brasileiros (móvel e fixo)
3. **Documentos**: Suporta CPF e CNPJ
4. **Permissões**: Verifica permissões do usuário antes de mostrar dados completos
5. **Fallback**: Retorna string vazia para dados inválidos

### Boas Práticas

1. Sempre use `conditionalMask()` em vez dos métodos individuais
2. Configure permissões adequadas para diferentes níveis de usuário
3. Teste as máscaras com diferentes formatos de dados
4. Monitore logs para detectar tentativas de acesso não autorizado

## Extensibilidade

Para adicionar novos tipos de máscaras:

1. Adicione o método no `SecurityMaskHelper`
2. Atualize o método `conditionalMask()` com o novo tipo
3. Adicione testes unitários
4. Atualize a documentação

```php
public static function maskNewType(string $value): string
{
    // Implementação da nova máscara
}

// No método conditionalMask()
return match ($type) {
    'email' => self::maskEmail($value),
    'phone' => self::maskPhone($value),
    'document' => self::maskDocument($value),
    'new_type' => self::maskNewType($value), // Nova máscara
    default => $value,
};
```
