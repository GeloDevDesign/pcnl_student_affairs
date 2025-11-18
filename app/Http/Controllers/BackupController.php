<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use Illuminate\Http\Request;
use ZipArchive;

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
        $filename = 'db-backup-'.now()->format('Y-m-d-His').'.sql';
        $zipname = $filename.'.zip';
        $path = 'backups/'.$zipname;

        $tempSqlPath = storage_path('app/temp/'.$filename);
        $zipFilePath = storage_path('app/backups/'.$zipname);

        // Create directories
        foreach (['temp', 'backups'] as $dir) {
            $fullDir = storage_path('app/'.$dir);
            if (! is_dir($fullDir)) {
                mkdir($fullDir, 0755, true);
            }
        }

        // === HOSTINGER-FRIENDLY CREDENTIALS ===
        $host = env('DB_HOST');        // e.g. mysql.hostinger.com or 127.0.0.1
        $port = env('DB_PORT', '3307');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD', '');   // often contains special chars!
        $database = env('DB_DATABASE');

        // === THE MAGIC COMMAND THAT WORKS EVERYWHERE ===
        
        $command = '"C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe"';


        $command .= ' -h '.escapeshellarg($host);
        $command .= ' -P '.escapeshellarg($port);
        $command .= ' -u '.escapeshellarg($username);

        // Critical: safely pass password even with special chars
        if (filled(trim($password))) {
            $command .= ' --password='.escapeshellarg($password);
        }

        $command .= ' --single-transaction --routines --triggers --quick --lock-tables=false';
        $command .= ' '.escapeshellarg($database);
        $command .= ' > '.escapeshellarg($tempSqlPath);

        // Optional: log the exact command (remove later if you want)
        \Log::info('mysqldump command', ['command' => $command]);

        $output = [];
        $returnVar = 0;
        exec($command.' 2>&1', $output, $returnVar);

        \Log::info('mysqldump result', ['return' => $returnVar, 'output' => $output]);

        if ($returnVar !== 0 || ! file_exists($tempSqlPath) || filesize($tempSqlPath) === 0) {
            if (file_exists($tempSqlPath)) {
                unlink($tempSqlPath);
            }

            return redirect()->back()->with('error', 'Backup failed – check logs (common on Hostinger: wrong DB_HOST or special chars in password)');
        }

        // ZIP it
        $zip = new ZipArchive;
        if ($zip->open($zipFilePath, ZipArchive::CREATE) !== true) {
            unlink($tempSqlPath);

            return redirect()->back()->with('error', 'Failed to create ZIP');
        }
        $zip->addFile($tempSqlPath, $filename);
        $zip->close();
        unlink($tempSqlPath);

        // Save record
        Backup::create([
            'filename' => $zipname,
            'path' => $path,
            'size' => filesize($zipFilePath),
            'backed_up_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Database backup created successfully!');
    }

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
