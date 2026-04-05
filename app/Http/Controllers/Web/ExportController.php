<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use ZipArchive;
use Illuminate\View\View;

class ExportController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total' => Asset::where('status', 'ready')->orWhere('status', 'uploaded')->count(),
            'images' => Asset::where('status', 'ready')->orWhere('status', 'uploaded')->where('file_type', 'image')->count(),
            'videos' => Asset::where('status', 'ready')->orWhere('status', 'uploaded')->where('file_type', 'video')->count(),
        ];

        $categories = Asset::where('status', 'ready')->orWhere('status', 'uploaded')
            ->with('category')
            ->get()
            ->groupBy(fn($a) => $a->category->name ?? 'Unknown')
            ->map(fn($g) => $g->count());

        return view('export.index', compact('stats', 'categories'));
    }

    public function exportCsv(Request $request)
    {
        $query = Asset::where('status', 'ready')->orWhere('status', 'uploaded')->with('category');

        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
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

    public function exportJson(Request $request)
    {
        $query = Asset::where('status', 'ready')->orWhere('status', 'uploaded')->with('category');

        if ($request->has('category_id') && $request->category_id) {
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

    public function exportZip(Request $request)
    {
        $assets = Asset::where('status', 'ready')->orWhere('status', 'uploaded')->with('category')->get();

        if ($assets->isEmpty()) {
            return redirect()->back()->with('error', 'No ready assets to export');
        }

        $zipFileName = 'assets_export_' . date('Y-m-d_His') . '.zip';
        $zipFilePath = storage_path('app/public/exports/' . $zipFileName);

        $exportDir = storage_path('app/public/exports');
        if (!file_exists($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        $zip = new ZipArchive();
        
        if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
            foreach ($assets as $asset) {
                $filePath = storage_path('app/public/' . $asset->file_path);
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, 'files/' . $asset->file_name);
                }

                $metadata = [
                    'id' => $asset->id,
                    'title' => $asset->title,
                    'description' => $asset->description,
                    'keywords' => $asset->keywords,
                    'file_name' => $asset->file_name,
                    'category' => $asset->category->name ?? null,
                    'status' => $asset->status,
                ];
                
                $zip->addFromString('metadata/' . $asset->id . '.json', json_encode($metadata, JSON_PRETTY_PRINT));
            }

            $manifest = [
                'export_date' => now()->toIso8601String(),
                'total_files' => $assets->count(),
                'assets' => $assets->map(fn($a) => [
                    'id' => $a->id,
                    'file_name' => $a->file_name,
                    'metadata_file' => 'metadata/' . $a->id . '.json',
                ])->toArray(),
            ];
            
            $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
            $zip->close();

            return response()->download($zipFilePath, $zipFileName, ['Content-Type' => 'application/zip'])->deleteFileAfterSend(true);
        }

        return redirect()->back()->with('error', 'Failed to create ZIP');
    }
}