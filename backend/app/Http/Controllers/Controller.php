<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Rei do Óleo API",
 *     version="1.0.0",
 *     description="API para sistema de gestão de postos de troca de óleo e serviços automotivos",
 *     termsOfService="http://swagger.io/terms/",
 *     @OA\Contact(
 *         email="suporte@reidooleo.com.br",
 *         name="Suporte Rei do Óleo"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Servidor de Desenvolvimento"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="apiKey",
 *     in="header",
 *     name="Authorization",
 *     description="Token de acesso Sanctum no formato: Bearer {token}"
 * )
 *
 * @OA\Tag(
 *     name="Autenticação",
 *     description="Endpoints para autenticação e autorização"
 * )
 *
 * @OA\Tag(
 *     name="Clientes",
 *     description="Gestão de clientes"
 * )
 *
 * @OA\Tag(
 *     name="Veículos",
 *     description="Gestão de veículos dos clientes"
 * )
 *
 * @OA\Tag(
 *     name="Produtos",
 *     description="Gestão de produtos e estoque"
 * )
 *
 * @OA\Tag(
 *     name="Categorias",
 *     description="Gestão de categorias de produtos"
 * )
 *
 * @OA\Tag(
 *     name="Centros de Serviço",
 *     description="Gestão de postos e centros de serviço"
 * )
 *
 * @OA\Tag(
 *     name="Serviços",
 *     description="Gestão de serviços realizados"
 * )
 *
 * @OA\Tag(
 *     name="Itens de Serviço",
 *     description="Gestão de itens utilizados nos serviços"
 * )
 *
 * @OA\Tag(
 *     name="Usuários",
 *     description="Gestão de usuários do sistema"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
