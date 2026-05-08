<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class MonitoringController extends Controller
{
    public function getQueueStats(Request $request): JsonResponse
    {
        $expectedToken = config('services.n8n.token');
        $providedToken = $request->header('X-API-TOKEN') ?? $request->query('token'); // Ou autre méthode de récupération

        if (! $expectedToken || $providedToken !== $expectedToken) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // 2. Récupération de la taille des queues principales
        $queues = config('queue.monitoring', ['default']); // Liste tes queues ici
        $stats = [];
        foreach ($queues as $queue) {
            $stats[$queue] = Queue::size($queue);
        }

        // 3. Détection des "Stuck Jobs" (Jobs réservés depuis plus de 60 min)
        $oneHourAgo = Carbon::now()->subMinutes(60);

        $stuckJobs = DB::table('jobs')
            ->whereNotNull('reserved_at')
            ->where('reserved_at', '<=', $oneHourAgo)
            ->select('id', 'queue', 'payload', 'attempts', 'reserved_at')
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);

                return [
                    'id' => $job->id,
                    'queue' => $job->queue,
                    'job_class' => $payload['displayName'] ?? 'Unknown',
                    'attempts' => $job->attempts,
                    'reserved_since' => Carbon::createFromTimeString($job->reserved_at)->diffForHumans(),
                ];
            });

        // 4. Nombre de jobs en échec total
        $failedCount = DB::table('failed_jobs')->count();

        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'queue_sizes' => $stats,
            'stuck_jobs' => [
                'count' => $stuckJobs->count(),
                'items' => $stuckJobs,
            ],
            'failed_jobs_total' => $failedCount,
        ]);
    }
}
