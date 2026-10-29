<?php

namespace App\Http\Controllers;

use App\Models\NormalizedEvent;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class LogExplorerController extends Controller
{
    public function index(Request $request): View
    {
        $query = NormalizedEvent::with(['rawLog.logSource']);

        // 1. Keyword Search
        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where(function ($q) use ($keyword) {
                $q->where('source_ip', 'like', "%{$keyword}%")
                    ->orWhere('destination_ip', 'like', "%{$keyword}%")
                    ->orWhere('username', 'like', "%{$keyword}%")
                    ->orWhere('event_action', 'like', "%{$keyword}%")
                    ->orWhereHas('rawLog', function ($rawQ) use ($keyword) {
                        $rawQ->where('raw_message', 'like', "%{$keyword}%");
                    });
            });
        }

        // 2. Exact IP Filter (e.g. from alert or top attacker link)
        if ($request->filled('source_ip')) {
            $query->where('source_ip', trim($request->input('source_ip')));
        }

        // 3. Category Filter
        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('event_category', $request->input('category'));
        }

        // 4. Severity Filter
        if ($request->filled('severity') && $request->input('severity') !== 'all') {
            $query->where('severity', $request->input('severity'));
        }

        // 5. Outcome Filter
        if ($request->filled('outcome') && $request->input('outcome') !== 'all') {
            $query->where('event_outcome', $request->input('outcome'));
        }

        // 6. Date Range Filters
        if ($request->filled('date_from')) {
            try {
                $from = CarbonImmutable::parse($request->input('date_from'))->startOfDay();
                $query->where('timestamp', '>=', $from);
            } catch (Throwable) {
                // Ignore invalid date
            }
        }

        if ($request->filled('date_to')) {
            try {
                $to = CarbonImmutable::parse($request->input('date_to'))->endOfDay();
                $query->where('timestamp', '<=', $to);
            } catch (Throwable) {
                // Ignore invalid date
            }
        }

        // Get Available Categories for dropdown
        $categories = NormalizedEvent::distinct()
            ->whereNotNull('event_category')
            ->orderBy('event_category')
            ->pluck('event_category');

        $events = $query->orderByDesc('timestamp')->paginate(15)->withQueryString();

        $metrics = [
            'total_in_db' => NormalizedEvent::count(),
            'total_matched' => $events->total(),
        ];

        return view('pages.logs', [
            'events' => $events,
            'categories' => $categories,
            'metrics' => $metrics,
            'filters' => [
                'q' => $request->input('q', ''),
                'source_ip' => $request->input('source_ip', ''),
                'category' => $request->input('category', 'all'),
                'severity' => $request->input('severity', 'all'),
                'outcome' => $request->input('outcome', 'all'),
                'date_from' => $request->input('date_from', ''),
                'date_to' => $request->input('date_to', ''),
            ],
        ]);
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = NormalizedEvent::with(['rawLog.logSource']);

        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where(function ($q) use ($keyword) {
                $q->where('source_ip', 'like', "%{$keyword}%")
                    ->orWhere('destination_ip', 'like', "%{$keyword}%")
                    ->orWhere('username', 'like', "%{$keyword}%")
                    ->orWhere('event_action', 'like', "%{$keyword}%")
                    ->orWhereHas('rawLog', function ($rawQ) use ($keyword) {
                        $rawQ->where('raw_message', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($request->filled('source_ip')) {
            $query->where('source_ip', trim($request->input('source_ip')));
        }

        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('event_category', $request->input('category'));
        }

        if ($request->filled('severity') && $request->input('severity') !== 'all') {
            $query->where('severity', $request->input('severity'));
        }

        if ($request->filled('outcome') && $request->input('outcome') !== 'all') {
            $query->where('event_outcome', $request->input('outcome'));
        }

        $events = $query->orderByDesc('timestamp')->take(1000)->get();
        $filename = 'aegis_soc_telemetry_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($events) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Event ID',
                'Timestamp UTC',
                'Log Source Node',
                'Source IP',
                'Destination IP',
                'Username',
                'Category',
                'Action',
                'Outcome',
                'Severity',
                'Raw Message',
            ]);

            foreach ($events as $event) {
                fputcsv($handle, [
                    $event->id,
                    $event->timestamp?->format('Y-m-d H:i:s') ?? 'N/A',
                    $event->rawLog->logSource->name ?? 'Direct Pipeline',
                    $event->source_ip ?? 'Unknown',
                    $event->destination_ip ?? '-',
                    $event->username ?? '-',
                    $event->event_category,
                    $event->event_action,
                    $event->event_outcome,
                    $event->severity,
                    $event->rawLog->raw_message ?? ($event->parsed_data['raw_message'] ?? ''),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
