<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Artisan;

class WebhookController extends Controller
{
    /**
     * Handle GitHub webhook for automatic deployment
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deploy(Request $request)
    {
        try {
            // Log the webhook request
            Log::info('Webhook: Deploy request received', [
                'headers' => $request->headers->all(),
                'payload' => $request->all()
            ]);

            // Get the payload
            $payload = $request->all();

            // Verify if it's a push to the hostinger-hom branch
            if (!isset($payload['ref']) || $payload['ref'] !== 'refs/heads/hostinger-hom') {
                Log::info('Webhook: Ignored - not hostinger-hom branch', [
                    'ref' => $payload['ref'] ?? 'not set'
                ]);

                return response()->json([
                    'status' => 'ignored',
                    'message' => 'Ignored - not hostinger-hom branch',
                    'ref' => $payload['ref'] ?? 'not set'
                ]);
            }

            // Verify repository
            if (!isset($payload['repository']['full_name']) ||
                $payload['repository']['full_name'] !== 'spsise/rei-do-oleo') {
                Log::warning('Webhook: Wrong repository', [
                    'repository' => $payload['repository']['full_name'] ?? 'not set'
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Wrong repository'
                ], 400);
            }

            Log::info('Webhook: Starting deployment', [
                'branch' => 'hostinger-hom',
                'commit' => $payload['head_commit']['id'] ?? 'unknown',
                'message' => $payload['head_commit']['message'] ?? 'no message'
            ]);

            // Execute deployment script in background
            $deployScript = '/home/' . get_current_user() . '/rei-do-oleo/deploy.sh';

            if (!file_exists($deployScript)) {
                Log::error('Webhook: Deploy script not found', ['script' => $deployScript]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Deploy script not found'
                ], 500);
            }

            // Check if script is executable
            if (!is_executable($deployScript)) {
                Log::warning('Webhook: Deploy script not executable, making it executable', ['script' => $deployScript]);
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
                    Log::error('Webhook: Deploy error', ['output' => trim($buffer)]);
                } else {
                    Log::info('Webhook: Deploy output', ['output' => trim($buffer)]);
                }
            });

            // Wait for process to complete
            $process->wait();

            // Check if process completed successfully
            if (!$process->isSuccessful()) {
                Log::error('Webhook: Deploy process failed', [
                    'exit_code' => $process->getExitCode(),
                    'error_output' => $process->getErrorOutput(),
                    'output' => $process->getOutput()
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Deploy process failed',
                    'exit_code' => $process->getExitCode(),
                    'error' => $process->getErrorOutput()
                ], 500);
            }

            Log::info('Webhook: Deploy process completed successfully', [
                'exit_code' => $process->getExitCode(),
                'output' => $process->getOutput(),
                'deploy_script' => $deployScript
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Deployment completed successfully',
                'branch' => 'hostinger-hom',
                'commit' => $payload['head_commit']['id'] ?? 'unknown',
                'exit_code' => $process->getExitCode(),
                'output' => $process->getOutput(),
                'deploy_script' => $deployScript,
                'process_id' => $process->getPid()
            ]);

        } catch (\Exception $e) {
            Log::error('Webhook: Exception during deployment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Exception during deployment: ' . $e->getMessage()
            ], 500);
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
}
