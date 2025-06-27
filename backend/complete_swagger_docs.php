<?php

// Este arquivo contém toda a documentação Swagger restante para ser aplicada nos controladores
// Documentação completa para ServiceCenterController (métodos restantes)

/**
 * @OA\Get(
 *     path="/api/v1/service-centers/{id}",
 *     tags={"Centros de Serviço"},
 *     summary="Obter centro de serviço específico",
 *     description="Retorna os dados de um centro de serviço específico",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID do centro de serviço",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Centro de serviço encontrado",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Centro de serviço encontrado"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="code", type="string", example="CS001"),
 *                 @OA\Property(property="name", type="string", example="Centro de Serviço Principal"),
 *                 @OA\Property(property="slug", type="string", example="centro-de-servico-principal"),
 *                 @OA\Property(property="cnpj", type="string", example="12.345.678/0001-90"),
 *                 @OA\Property(property="city", type="string", example="São Paulo"),
 *                 @OA\Property(property="state", type="string", example="SP"),
 *                 @OA\Property(property="phone", type="string", example="(11) 3333-4444"),
 *                 @OA\Property(property="active", type="boolean", example=true)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Centro de serviço não encontrado"
 *     )
 * )
 */
// ServiceCenterController::show

/**
 * @OA\Put(
 *     path="/api/v1/service-centers/{id}",
 *     tags={"Centros de Serviço"},
 *     summary="Atualizar centro de serviço",
 *     description="Atualiza os dados de um centro de serviço existente",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID do centro de serviço",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="code", type="string", example="CS001", description="Código único do centro"),
 *             @OA\Property(property="name", type="string", example="Centro de Serviço Principal Atualizado", description="Nome do centro"),
 *             @OA\Property(property="cnpj", type="string", example="12.345.678/0001-90", description="CNPJ da empresa"),
 *             @OA\Property(property="city", type="string", example="São Paulo", description="Cidade"),
 *             @OA\Property(property="state", type="string", example="SP", description="Estado (UF)"),
 *             @OA\Property(property="phone", type="string", example="(11) 3333-4444", description="Telefone"),
 *             @OA\Property(property="active", type="boolean", example=true, description="Status ativo")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Centro de serviço atualizado com sucesso"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Centro de serviço não encontrado"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Erro de validação"
 *     )
 * )
 */
// ServiceCenterController::update

/**
 * @OA\Delete(
 *     path="/api/v1/service-centers/{id}",
 *     tags={"Centros de Serviço"},
 *     summary="Excluir centro de serviço",
 *     description="Remove um centro de serviço do sistema",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID do centro de serviço",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Centro de serviço excluído com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Centro de serviço excluído com sucesso")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Centro de serviço não encontrado"
 *     )
 * )
 */
// ServiceCenterController::destroy

/**
 * @OA\Get(
 *     path="/api/v1/service-centers/active",
 *     tags={"Centros de Serviço"},
 *     summary="Listar centros de serviço ativos",
 *     description="Retorna apenas os centros de serviço que estão ativos no sistema",
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Centros de serviço ativos listados com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Centros de serviço ativos listados"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="code", type="string", example="CS001"),
 *                     @OA\Property(property="name", type="string", example="Centro de Serviço Principal"),
 *                     @OA\Property(property="active", type="boolean", example=true)
 *                 )
 *             )
 *         )
 *     )
 * )
 */
// ServiceCenterController::getActive

/**
 * @OA\Post(
 *     path="/api/v1/service-centers/search/code",
 *     tags={"Centros de Serviço"},
 *     summary="Buscar centro de serviço por código",
 *     description="Busca um centro de serviço específico pelo código",
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"code"},
 *             @OA\Property(property="code", type="string", example="CS001", description="Código do centro de serviço")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Centro de serviço encontrado por código"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Centro de serviço não encontrado"
 *     )
 * )
 */
// ServiceCenterController::findByCode

// =====================================
// UserController - Documentação completa
// =====================================

/**
 * @OA\Get(
 *     path="/api/v1/users",
 *     tags={"Usuários"},
 *     summary="Listar usuários",
 *     description="Lista todos os usuários do sistema com opções de filtro",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="search",
 *         in="query",
 *         description="Termo de busca por nome ou email",
 *         required=false,
 *         @OA\Schema(type="string", example="joão")
 *     ),
 *     @OA\Parameter(
 *         name="service_center_id",
 *         in="query",
 *         description="Filtrar por centro de serviço",
 *         required=false,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Parameter(
 *         name="role",
 *         in="query",
 *         description="Filtrar por papel/função",
 *         required=false,
 *         @OA\Schema(type="string", example="technician")
 *     ),
 *     @OA\Parameter(
 *         name="active",
 *         in="query",
 *         description="Filtrar por status ativo",
 *         required=false,
 *         @OA\Schema(type="boolean", example=true)
 *     ),
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         description="Número de itens por página",
 *         required=false,
 *         @OA\Schema(type="integer", example=15)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Usuários listados com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Usuários listados com sucesso"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="João Silva"),
 *                     @OA\Property(property="email", type="string", example="joao@reidooleo.com"),
 *                     @OA\Property(property="role", type="string", example="technician"),
 *                     @OA\Property(property="service_center_id", type="integer", example=1),
 *                     @OA\Property(property="active", type="boolean", example=true),
 *                     @OA\Property(property="created_at", type="string", format="date-time"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Token inválido ou não fornecido"
 *     )
 * )
 */
// UserController::index

/**
 * @OA\Post(
 *     path="/api/v1/users",
 *     tags={"Usuários"},
 *     summary="Criar novo usuário",
 *     description="Cria um novo usuário no sistema",
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name","email","password","role"},
 *             @OA\Property(property="name", type="string", example="Maria Santos", description="Nome completo do usuário"),
 *             @OA\Property(property="email", type="string", format="email", example="maria@reidooleo.com", description="Email único do usuário"),
 *             @OA\Property(property="password", type="string", format="password", example="MinhaSenh@123", description="Senha do usuário"),
 *             @OA\Property(property="role", type="string", example="technician", description="Papel/função do usuário"),
 *             @OA\Property(property="service_center_id", type="integer", example=1, description="ID do centro de serviço"),
 *             @OA\Property(property="phone", type="string", example="(11) 98765-4321", description="Telefone do usuário"),
 *             @OA\Property(property="active", type="boolean", example=true, description="Status ativo")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Usuário criado com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Usuário criado com sucesso"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=2),
 *                 @OA\Property(property="name", type="string", example="Maria Santos"),
 *                 @OA\Property(property="email", type="string", example="maria@reidooleo.com"),
 *                 @OA\Property(property="role", type="string", example="technician"),
 *                 @OA\Property(property="active", type="boolean", example=true),
 *                 @OA\Property(property="created_at", type="string", format="date-time"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Erro de validação",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Dados inválidos"),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="O email já está em uso"))
 *             )
 *         )
 *     )
 * )
 */
// UserController::store

/**
 * @OA\Get(
 *     path="/api/v1/users/{id}",
 *     tags={"Usuários"},
 *     summary="Obter usuário específico",
 *     description="Retorna os dados de um usuário específico",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID do usuário",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Usuário encontrado",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Usuário encontrado"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="João Silva"),
 *                 @OA\Property(property="email", type="string", example="joao@reidooleo.com"),
 *                 @OA\Property(property="role", type="string", example="technician"),
 *                 @OA\Property(property="service_center_id", type="integer", example=1),
 *                 @OA\Property(property="active", type="boolean", example=true)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Usuário não encontrado"
 *     )
 * )
 */
// UserController::show

/**
 * @OA\Put(
 *     path="/api/v1/users/{id}",
 *     tags={"Usuários"},
 *     summary="Atualizar usuário",
 *     description="Atualiza os dados de um usuário existente",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID do usuário",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="João Silva Santos", description="Nome completo do usuário"),
 *             @OA\Property(property="email", type="string", format="email", example="joao.santos@reidooleo.com", description="Email único do usuário"),
 *             @OA\Property(property="role", type="string", example="manager", description="Papel/função do usuário"),
 *             @OA\Property(property="service_center_id", type="integer", example=2, description="ID do centro de serviço"),
 *             @OA\Property(property="phone", type="string", example="(11) 98765-4321", description="Telefone do usuário"),
 *             @OA\Property(property="active", type="boolean", example=true, description="Status ativo")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Usuário atualizado com sucesso"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Usuário não encontrado"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Erro de validação"
 *     )
 * )
 */
// UserController::update

/**
 * @OA\Delete(
 *     path="/api/v1/users/{id}",
 *     tags={"Usuários"},
 *     summary="Excluir usuário",
 *     description="Remove um usuário do sistema",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID do usuário",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Usuário excluído com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Usuário excluído com sucesso")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Usuário não encontrado"
 *     )
 * )
 */
// UserController::destroy

/**
 * @OA\Put(
 *     path="/api/v1/users/{id}/change-password",
 *     tags={"Usuários"},
 *     summary="Alterar senha do usuário",
 *     description="Altera a senha de um usuário específico",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID do usuário",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"password","password_confirmation"},
 *             @OA\Property(property="password", type="string", format="password", example="NovaSenha@123", description="Nova senha"),
 *             @OA\Property(property="password_confirmation", type="string", format="password", example="NovaSenha@123", description="Confirmação da nova senha")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Senha alterada com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Senha alterada com sucesso")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Usuário não encontrado"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Erro de validação"
 *     )
 * )
 */
// UserController::changePassword

// =====================================
// ServiceController - Métodos restantes para completar
// =====================================

/**
 * @OA\Put(
 *     path="/api/v1/services/{id}",
 *     tags={"Serviços"},
 *     summary="Atualizar serviço",
 *     description="Atualiza os dados de um serviço existente",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID do serviço",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="service_center_id", type="integer", example=1, description="ID do centro de serviço"),
 *             @OA\Property(property="client_id", type="integer", example=1, description="ID do cliente"),
 *             @OA\Property(property="vehicle_id", type="integer", example=1, description="ID do veículo"),
 *             @OA\Property(property="technician_id", type="integer", example=1, description="ID do técnico"),
 *             @OA\Property(property="status", type="string", example="in_progress", description="Status do serviço"),
 *             @OA\Property(property="description", type="string", example="Troca de óleo e filtros", description="Descrição do serviço"),
 *             @OA\Property(property="observations", type="string", example="Cliente solicitou óleo sintético", description="Observações")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Serviço atualizado com sucesso"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Serviço não encontrado"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Erro de validação"
 *     )
 * )
 */
// ServiceController::update

/**
 * @OA\Delete(
 *     path="/api/v1/services/{id}",
 *     tags={"Serviços"},
 *     summary="Excluir serviço",
 *     description="Remove um serviço do sistema",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID do serviço",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Serviço excluído com sucesso"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Serviço não encontrado"
 *     )
 * )
 */
// ServiceController::destroy

/**
 * @OA\Get(
 *     path="/api/v1/services/service-center/{serviceCenterId}",
 *     tags={"Serviços"},
 *     summary="Listar serviços de um centro de serviço",
 *     description="Retorna todos os serviços de um centro de serviço específico",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="serviceCenterId",
 *         in="path",
 *         required=true,
 *         description="ID do centro de serviço",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Serviços do centro de serviço listados"
 *     )
 * )
 */
// ServiceController::getByServiceCenter

/**
 * @OA\Get(
 *     path="/api/v1/services/client/{clientId}",
 *     tags={"Serviços"},
 *     summary="Listar serviços de um cliente",
 *     description="Retorna todos os serviços de um cliente específico",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="clientId",
 *         in="path",
 *         required=true,
 *         description="ID do cliente",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Serviços do cliente listados"
 *     )
 * )
 */
// ServiceController::getByClient

/**
 * @OA\Get(
 *     path="/api/v1/services/vehicle/{vehicleId}",
 *     tags={"Serviços"},
 *     summary="Listar histórico de serviços de um veículo",
 *     description="Retorna todos os serviços realizados em um veículo específico",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="vehicleId",
 *         in="path",
 *         required=true,
 *         description="ID do veículo",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Histórico de serviços do veículo"
 *     )
 * )
 */
// ServiceController::getByVehicle

/**
 * @OA\Get(
 *     path="/api/v1/services/technician/{technicianId}",
 *     tags={"Serviços"},
 *     summary="Listar serviços de um técnico",
 *     description="Retorna todos os serviços atribuídos a um técnico específico",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="technicianId",
 *         in="path",
 *         required=true,
 *         description="ID do técnico",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Serviços do técnico listados"
 *     )
 * )
 */
// ServiceController::getByTechnician

/**
 * @OA\Post(
 *     path="/api/v1/services/search/service-number",
 *     tags={"Serviços"},
 *     summary="Buscar serviço por número",
 *     description="Busca um serviço específico pelo número de serviço",
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"service_number"},
 *             @OA\Property(property="service_number", type="string", example="SRV-2024-001", description="Número do serviço")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Serviço encontrado por número"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Serviço não encontrado"
 *     )
 * )
 */
// ServiceController::searchByServiceNumber

// =====================================
// AuthController - Método restante
// =====================================

/**
 * @OA\Post(
 *     path="/api/v1/auth/forgot-password",
 *     tags={"Autenticação"},
 *     summary="Esqueci minha senha",
 *     description="Envia um email para redefinição de senha",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email"},
 *             @OA\Property(property="email", type="string", format="email", example="joao@example.com", description="Email do usuário")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Email de redefinição enviado com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Email de redefinição enviado com sucesso")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Email não encontrado"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Erro de validação"
 *     )
 * )
 */
// AuthController::forgotPassword

?>
