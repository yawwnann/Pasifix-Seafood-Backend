<?php

namespace App\Filament\Resources\IkanResource\Pages;

use App\Filament\Resources\IkanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use Cloudinary\Cloudinary;
    
class EditIkan extends EditRecord
{
    protected static string $resource = IkanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        Log::info('MULAI_MUTATE_EDIT', $data);
        
        // Initialize Cloudinary once
        $cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
            'url' => [
                'secure' => true
            ]
        ]);
        
        if (isset($data['gambar_upload']) && $data['gambar_upload'] instanceof \Illuminate\Http\UploadedFile) {
            try {
                $result = $cloudinary->uploadApi()->upload($data['gambar_upload']->getRealPath(), [
                    'folder' => 'produk'
                ]);
                $data['gambar'] = $result['secure_url'] ?? null;
                @unlink($data['gambar_upload']->getRealPath());
                Log::info('CLOUDINARY_UPLOAD_SUCCESS', ['url' => $data['gambar']]);
            } catch (\Exception $e) {
                Log::error('CLOUDINARY_UPLOAD_ERROR', ['error' => $e->getMessage()]);
                $data['gambar'] = null;
            }
        } elseif (isset($data['gambar_upload']) && is_string($data['gambar_upload']) && file_exists(storage_path('app/public/' . $data['gambar_upload']))) {
            try {
                $result = $cloudinary->uploadApi()->upload(storage_path('app/public/' . $data['gambar_upload']), [
                    'folder' => 'produk'
                ]);
                $data['gambar'] = $result['secure_url'] ?? null;
                @unlink(storage_path('app/public/' . $data['gambar_upload']));
                Log::info('CLOUDINARY_UPLOAD_SUCCESS', ['url' => $data['gambar']]);
            } catch (\Exception $e) {
                Log::error('CLOUDINARY_UPLOAD_ERROR', ['error' => $e->getMessage()]);
                $data['gambar'] = null;
            }
        } else {
            Log::info('CLOUDINARY_UPLOAD_SKIP', ['gambar_upload' => $data['gambar_upload'] ?? null]);
        }
        
        return $data;
    }
}
