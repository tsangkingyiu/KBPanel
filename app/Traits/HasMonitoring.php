<?php

namespace App\Traits;

use App\Models\ResourceUsage;

trait HasMonitoring
{
    public function recordResourceUsage($cpuPercent, $memoryMb, $diskMb, $bandwidthMb = 0)
    {
        return ResourceUsage::create([
            'project_id' => $this->id,
            'user_id' => $this->user_id,
            'cpu_percent' => $cpuPercent,
            'memory_mb' => $memoryMb,
            'disk_mb' => $diskMb,
            'bandwidth_mb' => $bandwidthMb,
            'recorded_at' => now(),
        ]);
    }

    public function getLatestResourceUsage()
    {
        return $this->resourceUsage()->latest('recorded_at')->first();
    }

    public function getResourceUsageHistory($hours = 24)
    {
        return $this->resourceUsage()
            ->where('recorded_at', '>=', now()->subHours($hours))
            ->orderBy('recorded_at', 'desc')
            ->get();
    }

    public function getAverageResourceUsage($hours = 24)
    {
        return $this->resourceUsage()
            ->where('recorded_at', '>=', now()->subHours($hours))
            ->selectRaw('AVG(cpu_percent) as avg_cpu, AVG(memory_mb) as avg_memory, AVG(disk_mb) as avg_disk')
            ->first();
    }
}
