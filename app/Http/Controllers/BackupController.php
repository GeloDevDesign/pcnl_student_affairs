<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use Illuminate\Http\Request;
use ZipArchive;

class BackupController extends Controller
{
    public function index()
    {

        $backups = Backup::latest()->paginate(20);

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
        $filename = 'db-backup-'.now()->format('Y-m-d-His').'.sql';
        $zipname = $filename.'.zip';
        $path = 'backups/'.$zipname;

        $zipDirectory = storage_path('app/backups'); // storage/app/backups

        if (! is_dir($zipDirectory)) {
            mkdir($zipDirectory, 0755, true);
        }

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

        // Create temp folder if not exists (This part is already correct)
        if (! is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        // Run dump
        exec($command);

        // Compress to ZIP
        // Use the defined $zipDirectory in the path
        $zipFilePath = $zipDirectory.'/'.$zipname;

        $zip = new ZipArchive;
        // Open the file using the full, fixed path
        if ($zip->open($zipFilePath, ZipArchive::CREATE) === true) {
            $zip->addFile(storage_path('app/temp/'.$filename), $filename);
            $zip->close();

            // Delete temp SQL file
            unlink(storage_path('app/temp/'.$filename));
        }

        // Save to database
        Backup::create([
            'filename' => $zipname,
            'path' => $path, // This path is relative to storage/app, which is correct for the DB record
            'size' => filesize($zipFilePath), // Use the explicit, resolved file path here
            'backed_up_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Database backup created successfully!');
    }

    // app/Http/Controllers/BackupController.php

    // Ensure you are using the Storage facade if you switch to that method:
    // use Illuminate\Support\Facades\Storage;

    // ...

    public function download($id)
    {
        $backup = Backup::findOrFail($id);


        $filePath = storage_path('app/'.$backup->path);

        if (! file_exists($filePath)) {
         
            \Log::error('Backup file not found at path: '.$filePath);

            return redirect()->back()->with('error', 'File not found on server.');
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
