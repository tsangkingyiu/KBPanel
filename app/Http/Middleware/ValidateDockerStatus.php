<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\DockerService;

class ValidateDockerStatus
{
    protected DockerService $dockerService;

    public function __construct(DockerService $dockerService)
    {
        $this->dockerService = $dockerService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->dockerService->isDockerRunning()) {
            return response()->json([
                'error' => 'Docker service is not running. Please contact administrator.',
                'status' => 'service_unavailable'
            ], 503);
        }

        return $next($request);
    }
}
