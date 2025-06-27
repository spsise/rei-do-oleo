<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Rei do Óleo API",
 *     version="1.0.0",
 *     description="API completa para gerenciamento do sistema Rei do Óleo - Troca de óleo automotivo",
 *     termsOfService="http://localhost:8100/terms/",
 *     @OA\Contact(
 *         email="dev@reidooleo.com",
 *         name="Rei do Óleo Dev Team"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8100",
 *     description="Desenvolvimento - Servidor Local (Porta 8100)"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Desenvolvimento - Servidor Local (Porta 8000)"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:9000",
 *     description="Desenvolvimento - Servidor Local (Porta 9000)"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="apiKey",
 *     in="header",
 *     name="Authorization",
 *     description="Token de autenticação Sanctum. Use o formato: Bearer {token}"
 * )
 *
 * @OA\Tag(
 *     name="Autenticação",
 *     description="Endpoints para autenticação e gerenciamento de usuários"
 * )
 *
 * @OA\Tag(
 *     name="Clientes",
 *     description="Gerenciamento de clientes"
 * )
 *
 * @OA\Tag(
 *     name="Veículos",
 *     description="Gerenciamento de veículos dos clientes"
 * )
 *
 * @OA\Tag(
 *     name="Produtos",
 *     description="Gerenciamento de produtos (óleos, filtros, etc.)"
 * )
 *
 * @OA\Tag(
 *     name="Categorias",
 *     description="Gerenciamento de categorias de produtos"
 * )
 *
 * @OA\Tag(
 *     name="Centros de Serviço",
 *     description="Gerenciamento de centros de serviço/filiais"
 * )
 *
 * @OA\Tag(
 *     name="Ordens de Serviço",
 *     description="Gerenciamento de ordens de serviço"
 * )
 *
 * @OA\Tag(
 *     name="Itens de Serviço",
 *     description="Gerenciamento de itens das ordens de serviço"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
