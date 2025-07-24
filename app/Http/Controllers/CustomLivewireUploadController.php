<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Livewire\WithFileUploads;

class CustomLivewireUploadController extends Controller
{
    use WithFileUploads;

    public function handle(Request $request): array
    {
        $disk = config('livewire.temporary_file_upload.disk', config('filesystems.default'));
        $filePaths = [];
        foreach ($request->file('files', []) as $file) {
            $filePaths[] = $file->store('livewire-tmp', $disk);
        }
        return ['paths' => $filePaths];
    }
}