<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {

        $backups = Backup::latest()->paginate(10);

        return inertia('backups/index', [
            'pageTitle' => 'Database Backups',
            'backups' => $backups->through(fn ($b) => [
                'id' => $b->id,
                'filename' => $b->filename,
                'date' => $b->created_at->format('M d, Y h:i A'),
                'size' => $b->size,
                'download_url' => route('backups.download', $b->id),
            ]),
        ]);
    }

    public function store(Request $request)
    {
        try {
            Artisan::call('config:clear');

            // Run the backup
            Artisan::call('backup:run', [
                '--only-db' => true,
                '--disable-notifications' => true,
            ]);

            // Get the latest backup file
            $disk = Storage::disk(config('backup.backup.destination.disks')[0]);
            $files = $disk->files(config('backup.backup.name'));

            if (empty($files)) {
                return redirect()->back()->with('error', 'Backup created but file not found.');
            }

            // Get the most recent file
            $latestFile = collect($files)->sortByDesc(function ($file) use ($disk) {
                return $disk->lastModified($file);
            })->first();

            $filename = basename($latestFile);
            $size = $disk->size($latestFile);

            // Save to database
            Backup::create([
                'filename' => $filename,
                'path' => $latestFile,
                'size' => $size,
                'backed_up_at' => now(),
            ]);

            return redirect()->back()->with('success', 'Database backup created successfully!');

        } catch (\Exception $e) {
            \Log::error('Backup failed: '.$e->getMessage());

            return redirect()->back()->with('error', 'Backup failed: '.$e->getMessage());
        }
    }

    // ...

    public function download($id)
    {
        $backup = Backup::findOrFail($id);

        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);

        if (! $disk->exists($backup->path)) {
            \Log::error('Backup file not found at path: '.$backup->path);

            return redirect()->back()->with('error', 'File not found on server.');
        }

        return $disk->download($backup->path, $backup->filename);
    }

    public function destroy($id)
    {
        $backup = Backup::findOrFail($id);

        $disk = Storage::disk(config('backup.backup.destination.disks')[0]);

        if ($disk->exists($backup->path)) {
            $disk->delete($backup->path);
        }

        $backup->delete();

        return redirect()->back()->with('success', 'Backup deleted successfully.');
    }
}
