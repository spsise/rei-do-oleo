<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Notifications\DeployNotification;
use App\Services\WhatsAppService;
use App\Services\UnifiedNotificationService;
use App\Contracts\LoggingServiceInterface;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

class WebhookController extends Controller
{
    public function __construct(
        private LoggingServiceInterface $loggingService
    ) {}

    /**
     * Handle GitHub webhook for automatic deployment
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deploy(Request $request)
    {
        $startTime = microtime(true);

        try {
            // Log the webhook request
            $this->loggingService->logApiRequest($request, [
                'webhook_type' => 'github_deploy',
                'operation' => 'deploy_webhook'
            ]);

            // Get the payload
            $payload = $request->all();

            // Verify if it's a push to the hostinger-hom branch
            if (!isset($payload['ref']) || $payload['ref'] !== 'refs/heads/hostinger-hom') {
                $this->loggingService->logBusinessOperation('deploy_webhook_ignored', [
                    'reason' => 'not_hostinger_hom_branch',
                    'ref' => $payload['ref'] ?? 'not set'
                ], 'info');

                return response()->json([
                    'status' => 'ignored',
                    'message' => 'Ignored - not hostinger-hom branch',
                    'ref' => $payload['ref'] ?? 'not set'
                ]);
            }

            // Verify repository
            if (!isset($payload['repository']['full_name']) ||
                $payload['repository']['full_name'] !== 'spsise/rei-do-oleo') {
                $this->loggingService->logSecurityEvent('deploy_webhook_wrong_repository', [
                    'repository' => $payload['repository']['full_name'] ?? 'not set',
                    'expected' => 'spsise/rei-do-oleo'
                ], 'warning');

                return response()->json([
                    'status' => 'error',
                    'message' => 'Wrong repository'
                ], 400);
            }

            $this->loggingService->logBusinessOperation('deploy_webhook_started', [
                'branch' => 'hostinger-hom',
                'commit' => $payload['head_commit']['id'] ?? 'unknown',
                'message' => $payload['head_commit']['message'] ?? 'no message',
                'repository' => $payload['repository']['full_name']
            ], 'info');

            // Execute deployment script in background
            $deployScript = '/home/' . get_current_user() . '/rei-do-oleo/deploy.sh';

            if (!file_exists($deployScript)) {
                $this->loggingService->logBusinessOperation('deploy_script_not_found', [
                    'script_path' => $deployScript,
                    'current_user' => get_current_user()
                ], 'error');

                $this->sendDeployNotification('error', $payload, 'Deploy script not found');

                return response()->json([
                    'status' => 'error',
                    'message' => 'Deploy script not found'
                ], 500);
            }

            // Check if script is executable
            if (!is_executable($deployScript)) {
                $this->loggingService->logBusinessOperation('deploy_script_made_executable', [
                    'script_path' => $deployScript
                ], 'warning');
                chmod($deployScript, 0755);
            }

            // Create process to run deploy script with better error handling
            $process = new Process(['bash', $deployScript]);
            $process->setWorkingDirectory('/home/' . get_current_user() . '/rei-do-oleo');
            $process->setTimeout(300); // 5 minutes timeout
            $process->setIdleTimeout(60); // 1 minute idle timeout

            // Start the process and capture output
            $process->start(function ($type, $buffer) {
                if ($type === Process::ERR) {
                    $this->loggingService->logBusinessOperation('deploy_process_error', [
                        'output' => trim($buffer)
                    ], 'error');
                } else {
                    $this->loggingService->logBusinessOperation('deploy_process_output', [
                        'output' => trim($buffer)
                    ], 'info');
                }
            });

            // Wait for process to complete
            $process->wait();

            // Check if process completed successfully
            if (!$process->isSuccessful()) {
                $this->loggingService->logBusinessOperation('deploy_process_failed', [
                    'exit_code' => $process->getExitCode(),
                    'error_output' => $process->getErrorOutput(),
                    'output' => $process->getOutput()
                ], 'error');

                // Send WhatsApp notification for failed deploy
                $this->sendDeployNotification('error', $payload, $process->getErrorOutput());

                return response()->json([
                    'status' => 'error',
                    'message' => 'Deploy process failed',
                    'exit_code' => $process->getExitCode(),
                    'error' => $process->getErrorOutput()
                ], 500);
            }

            $duration = (microtime(true) - $startTime) * 1000;

            $this->loggingService->logBusinessOperation('deploy_process_completed', [
                'exit_code' => $process->getExitCode(),
                'output' => $process->getOutput(),
                'processing_time_ms' => round($duration, 2)
            ], 'success');

            // Log performance metric
            $this->loggingService->logPerformance('github_deploy_webhook', $duration, [
                'branch' => 'hostinger-hom',
                'commit' => $payload['head_commit']['id'] ?? 'unknown'
            ]);

            // Send WhatsApp notification for successful deploy (Telegram)
            $this->sendDeployNotification('success', $payload, $process->getOutput());

            return response()->json([
                'status' => 'success',
                'message' => 'Deployment completed successfully',
            ]);

        } catch (\Exception $e) {
            $duration = (microtime(true) - $startTime) * 1000;

            $this->loggingService->logException($e, [
                'operation' => 'github_deploy_webhook',
                'processing_time_ms' => round($duration, 2)
            ]);

            // Send WhatsApp notification for exception
            $this->sendDeployNotification('error', $payload ?? [], $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Exception during deployment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send deploy notification via unified service
     *
     * @param string $status
     * @param array $payload
     * @param string $output
     * @return void
     */
    private function sendDeployNotification(string $status, array $payload, string $output = ''): void
    {
        try {
            $deployData = [
                'status' => $status,
                'branch' => $payload['ref'] ?? 'unknown',
                'commit' => $payload['head_commit']['id'] ?? 'unknown',
                'message' => $payload['head_commit']['message'] ?? 'no message',
                'timestamp' => now()->format('d/m/Y H:i:s'),
                'output' => $output
            ];

            // Send notification using unified service
            $notificationService = app(UnifiedNotificationService::class);
            $result = $notificationService->sendDeployNotification($deployData);

            if ($result['success']) {
                $this->loggingService->logBusinessOperation('deploy_notification_sent', [
                    'status' => $status,
                    'sent_to_channels' => $result['sent_to_channels'],
                    'total_channels' => $result['total_channels'],
                    'channels' => array_keys(array_filter($result['results'], fn($r) => $r['success']))
                ], 'success');
            } else {
                $this->loggingService->logBusinessOperation('deploy_notification_failed', [
                    'status' => $status,
                    'results' => $result['results']
                ], 'error');
            }

        } catch (\Exception $e) {
            $this->loggingService->logException($e, [
                'operation' => 'send_deploy_notification',
                'status' => $status
            ]);
        }
    }

    /**
     * Health check for webhook endpoint
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function health()
    {
        $deployScript = '/home/' . get_current_user() . '/rei-do-oleo/deploy.sh';

        $this->loggingService->logBusinessOperation('webhook_health_check', [
            'deploy_script_exists' => file_exists($deployScript),
            'deploy_script_executable' => file_exists($deployScript) ? is_executable($deployScript) : false,
            'current_user' => get_current_user()
        ], 'info');

        return response()->json([
            'status' => 'healthy',
            'message' => 'Webhook endpoint is working',
            'timestamp' => now()->toISOString(),
            'deploy_script_exists' => file_exists($deployScript),
            'deploy_script_executable' => file_exists($deployScript) ? is_executable($deployScript) : false,
            'deploy_script_path' => $deployScript,
            'current_user' => get_current_user(),
            'working_directory' => getcwd()
        ]);
    }

    public function testSendNotification()
    {
        $this->loggingService->logBusinessOperation('test_notification_sent', [
            'type' => 'deploy_notification'
        ], 'info');

        $this->sendDeployNotification('testNotification', [], 'Test');

        return response()->json([
            'status' => 'success',
            'message' => 'Test notification sent'
        ]);
    }
}
