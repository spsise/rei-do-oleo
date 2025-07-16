<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\{
    LoginRequest,
    RegisterRequest,
    UpdateProfileRequest,
    ChangePasswordRequest,
    ForgotPasswordRequest,
    ResetPasswordRequest
};
use App\Http\Resources\Auth\{UserResource, AuthResource};
use App\Services\AuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * @OA\Post(
     *     path="/api/v1/auth/register",
     *     tags={"Autenticação"},
     *     summary="Registrar novo usuário",
     *     description="Cria uma nova conta de usuário no sistema",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="João Silva"),
     *             @OA\Property(property="email", type="string", format="email", example="joao@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="MinhaSenh@123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="MinhaSenh@123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuário registrado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $authData = $this->authService->register($request->validated());

            return $this->createdResponse(
                new AuthResource($authData),
                'User registered successfully'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to register user');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     tags={"Autenticação"},
     *     summary="Fazer login",
     *     description="Autentica um usuário e retorna um token de acesso",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="joao@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="MinhaSenh@123"),
     *             @OA\Property(property="remember_me", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciais inválidas"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only(['email', 'password']);
            $rememberMe = $request->boolean('remember_me', false);

            $authData = $this->authService->login($credentials, $rememberMe);

            return $this->successResponse(
                new AuthResource($authData),
                'Login successful'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            $status = method_exists($e, 'status') ? $e->status : 422;
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
                'code' => $status,
                'timestamp' => now()->toISOString(),
                'version' => '1.0',
            ], $status);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => [],
                'code' => 500,
                'timestamp' => now()->toISOString(),
                'version' => '1.0',
            ], 500);
            // return $this->serverErrorResponse('Failed to authenticate user');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     tags={"Autenticação"},
     *     summary="Fazer logout",
     *     description="Invalida o token de acesso atual do usuário",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Logout successful")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido ou não fornecido"
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user) {
                $this->authService->logout($user);
            }

            return $this->successResponse(null, 'Logout successful');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to logout user');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/auth/me",
     *     tags={"Autenticação"},
     *     summary="Obter dados do usuário autenticado",
     *     description="Retorna as informações do usuário atualmente autenticado",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dados do usuário obtidos com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido ou não fornecido"
     *     )
     * )
     */
    public function me(Request $request): JsonResponse
    {
        return $this->successResponse([
            'user' => new UserResource($request->user())
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/refresh",
     *     tags={"Autenticação"},
     *     summary="Renovar token de acesso",
     *     description="Invalida o token atual e gera um novo token de acesso",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token renovado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Token refreshed successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido ou não fornecido"
     *     )
     * )
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $tokenData = $this->authService->refreshToken($user);

            return $this->successResponse(
                $tokenData,
                'Token refreshed successfully'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to refresh token');
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/auth/me",
     *     tags={"Autenticação"},
     *     summary="Atualizar perfil do usuário",
     *     description="Atualiza as informações do perfil do usuário autenticado",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="João Silva Santos"),
     *             @OA\Property(property="email", type="string", format="email", example="joao.santos@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Perfil atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Profile updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido ou não fornecido"
     *     )
     * )
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $updatedUser = $this->authService->updateProfile($user, $request->validated());

            return $this->successResponse(
                new UserResource($updatedUser),
                'Profile updated successfully'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update profile');
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/auth/change-password",
     *     tags={"Autenticação"},
     *     summary="Alterar senha do usuário",
     *     description="Altera a senha do usuário autenticado",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password","password","password_confirmation"},
     *             @OA\Property(property="current_password", type="string", format="password", example="SenhaAtual123"),
     *             @OA\Property(property="password", type="string", format="password", example="NovaSenha456@"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="NovaSenha456@")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Senha alterada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Password changed successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação ou senha atual incorreta"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido ou não fornecido"
     *     )
     * )
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $this->authService->changePassword(
                $user,
                $request->current_password,
                $request->password
            );

            return $this->successResponse(null, 'Password changed successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $status = method_exists($e, 'status') ? $e->status : 422;
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
                'code' => $status,
                'timestamp' => now()->toISOString(),
                'version' => '1.0',
            ], $status);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to change password');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/forgot-password",
     *     tags={"Autenticação"},
     *     summary="Solicitar redefinição de senha",
     *     description="Envia um link de redefinição de senha para o email do usuário",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="joao@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Link de redefinição enviado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Password reset link sent to your email")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor"
     *     )
     * )
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->sendPasswordResetLink($request->email);

            return $this->successResponse(
                null,
                'Password reset link sent to your email'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            $status = method_exists($e, 'status') ? $e->status : 422;
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
                'code' => $status,
                'timestamp' => now()->toISOString(),
                'version' => '1.0',
            ], $status);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Unable to send reset link');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/reset-password",
     *     tags={"Autenticação"},
     *     summary="Redefinir senha",
     *     description="Redefine a senha do usuário usando o token de redefinição",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token","email","password","password_confirmation"},
     *             @OA\Property(property="token", type="string", example="abc123def456..."),
     *             @OA\Property(property="email", type="string", format="email", example="joao@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="NovaSenha123@"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="NovaSenha123@")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Senha redefinida com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Password reset successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação ou token inválido"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Falha na redefinição da senha"
     *     )
     * )
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->resetPassword($request->validated());

            return $this->successResponse(
                null,
                'Password reset successfully'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            $status = method_exists($e, 'status') ? $e->status : 422;
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
                'code' => $status,
                'timestamp' => now()->toISOString(),
                'version' => '1.0',
            ], $status);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Password reset failed');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/auth/verify-email",
     *     tags={"Autenticação"},
     *     summary="Verificar email",
     *     description="Verifica o email do usuário usando o link de verificação",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=true,
     *         description="ID do usuário",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="hash",
     *         in="query",
     *         required=true,
     *         description="Hash de verificação",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email verificado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Email verified successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido ou não fornecido"
     *     )
     * )
     */
    public function verifyEmail(EmailVerificationRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $wasVerified = $this->authService->verifyEmail($user);

            $message = $wasVerified
                ? 'Email verified successfully'
                : 'Email already verified';

            return $this->successResponse(null, $message);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to verify email');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/send-verification",
     *     tags={"Autenticação"},
     *     summary="Reenviar email de verificação",
     *     description="Reenvia o email de verificação para o usuário autenticado",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Email de verificação enviado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Verification link sent")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido ou não fornecido"
     *     )
     * )
     */
    public function sendVerification(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $this->authService->sendEmailVerification($user);

            return $this->successResponse(
                null,
                'Verification link sent'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to send verification email');
        }
    }
}
