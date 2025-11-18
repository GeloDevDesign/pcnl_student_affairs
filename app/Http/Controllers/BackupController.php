<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use Illuminate\Http\Request;
use ZipArchive;

class BackupController extends Controller
{
    public function index()
    {
        $backups = Backup::latest()->get();


        return inertia('backups/index', [
            'pageTitle' => 'Database Backups',
            'backups' => $backups->map(fn ($b) => [
                'id' => $b->id,
                'filename' => $b->filename,
                'date' => $b->created_at->format('M d, Y h:i A'),
                'size' => $b->getSizeAttribute(),
                'download_url' => route('backups.download', $b->id),
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $filename = 'db-backup-'.now()->format('Y-m-d-His').'.sql';
        $zipname = $filename.'.zip';
        $path = 'backups/'.$zipname;

        // Get raw SQL dump
        $mysqlPath = env('DB_HOST');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $database = env('DB_DATABASE');

        // Simple mysqldump using shell (works on Hostinger)
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            $username,
            $password,
            $mysqlPath,
            $database,
            storage_path('app/temp/'.$filename)
        );

        // Create temp folder if not exists
        if (! is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        // Run dump
        exec($command);

        // Compress to ZIP
        $zip = new ZipArchive;
        if ($zip->open(storage_path('app/'.$path), ZipArchive::CREATE) === true) {
            $zip->addFile(storage_path('app/temp/'.$filename), $filename);
            $zip->close();

            // Delete temp SQL file
            unlink(storage_path('app/temp/'.$filename));
        }

        // Save to database
        Backup::create([
            'filename' => $zipname,
            'path' => $path,
            'size' => filesize(storage_path('app/'.$path)),
        ]);

        return redirect()->back()->with('success', 'Database backup created successfully!');
    }

    public function download($id)
    {
        $backup = Backup::findOrFail($id);
        $filePath = storage_path('app/'.$backup->path);

        if (! file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        return response()->download($filePath, $backup->filename);
    }

    public function destroy($id)
    {
        $backup = Backup::findOrFail($id);

        if (file_exists(storage_path('app/'.$backup->path))) {
            unlink(storage_path('app/'.$backup->path)); 
        }

        $backup->delete();

        return redirect()->back()->with('success', 'Backup deleted successfully.');
    }
}
