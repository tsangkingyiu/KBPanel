<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\DockerService;
use Symfony\Component\HttpFoundation\Response;

class ValidateDockerStatus
{
    public function __construct(
        private DockerService $dockerService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->dockerService->isDockerRunning()) {
            return response()->json([
                'error' => 'Docker service is not running'
            ], 503);
        }

        return $next($request);
    }
}
