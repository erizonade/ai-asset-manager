<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use ZipArchive;

class ExportController extends Controller
{
    /**
     * Export assets as CSV for Adobe Stock upload
     */
    public function exportCsv(Request $request)
    {
        $query = Asset::where('status', 'ready')
            ->orWhere('status', 'uploaded')
            ->with('category');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('file_type')) {
            $query->where('file_type', $request->file_type);
        }

        $assets = $query->get();

        $csvData = [];
        $csvData[] = ['Title', 'Description', 'Keywords', 'File Name', 'Category', 'Type', 'Status', 'File Path'];

        foreach ($assets as $asset) {
            $csvData[] = [
                $asset->title ?? '',
                $asset->description ?? '',
                implode(', ', $asset->keywords ?? []),
                $asset->file_name ?? '',
                $asset->category->name ?? '',
                $asset->file_type,
                $asset->status,
                $asset->file_path,
            ];
        }

        $filename = 'adobe_stock_export_' . date('Y-m-d_His') . '.csv';
        
        return Response::stream(function () use ($csvData) {
            $handle = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Export metadata only (JSON format)
     */
    public function exportJson(Request $request)
    {
        $query = Asset::where('status', 'ready')
            ->orWhere('status', 'uploaded')
            ->with('category');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $assets = $query->get()->map(function ($asset) {
            return [
                'id' => $asset->id,
                'title' => $asset->title,
                'description' => $asset->description,
                'keywords' => $asset->keywords,
                'file_name' => $asset->file_name,
                'file_type' => $asset->file_type,
                'category' => $asset->category->name ?? null,
                'status' => $asset->status,
                'created_at' => $asset->created_at->toIso8601String(),
            ];
        });

        $filename = 'adobe_stock_metadata_' . date('Y-m-d') . '.json';

        return response()->json([
            'export_date' => now()->toIso8601String(),
            'total_assets' => $assets->count(),
            'assets' => $assets,
        ])->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Export structured package with files and metadata
     */
    public function exportStructured(Request $request)
    {
        $assets = Asset::where('status', 'ready')
            ->orWhere('status', 'uploaded')
            ->with('category')
            ->get();

        if ($assets->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No ready assets to export',
            ], 404);
        }

        $zipFileName = 'assets_export_' . date('Y-m-d_His') . '.zip';
        $zipFilePath = storage_path('app/public/exports/' . $zipFileName);

        // Create exports directory if not exists
        $exportDir = storage_path('app/public/exports');
        if (!file_exists($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        $zip = new ZipArchive();
        
        if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
            foreach ($assets as $asset) {
                // Add asset file
                $filePath = storage_path('app/public/' . $asset->file_path);
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, 'files/' . $asset->file_name);
                }

                // Add metadata JSON
                $metadata = [
                    'id' => $asset->id,
                    'title' => $asset->title,
                    'description' => $asset->description,
                    'keywords' => $asset->keywords,
                    'file_name' => $asset->file_name,
                    'category' => $asset->category->name ?? null,
                    'status' => $asset->status,
                ];
                
                $zip->addFromString(
                    'metadata/' . $asset->id . '.json',
                    json_encode($metadata, JSON_PRETTY_PRINT)
                );
            }

            // Add manifest
            $manifest = [
                'export_date' => now()->toIso8601String(),
                'total_files' => $assets->count(),
                'assets' => $assets->map(function ($asset) {
                    return [
                        'id' => $asset->id,
                        'file_name' => $asset->file_name,
                        'metadata_file' => 'metadata/' . $asset->id . '.json',
                    ];
                })->toArray(),
            ];
            
            $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
            
            $zip->close();

            return response()->download($zipFilePath, $zipFileName, [
                'Content-Type' => 'application/zip',
            ])->deleteFileAfterSend(true);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to create ZIP archive',
        ], 500);
    }

    /**
     * Get export preview (without downloading)
     */
    public function preview(Request $request)
    {
        $query = Asset::where('status', 'ready')
            ->orWhere('status', 'uploaded')
            ->with('category');

        $total = $query->count();
        $images = (clone $query)->where('file_type', 'image')->count();
        $videos = (clone $query)->where('file_type', 'video')->count();

        $categories = Asset::where('status', 'ready')
            ->orWhere('status', 'uploaded')
            ->with('category')
            ->get()
            ->groupBy(fn($a) => $a->category->name ?? 'Unknown')
            ->map(fn($g) => $g->count());

        return response()->json([
            'success' => true,
            'data' => [
                'total_assets' => $total,
                'images' => $images,
                'videos' => $videos,
                'by_category' => $categories,
            ],
        ]);
    }
}