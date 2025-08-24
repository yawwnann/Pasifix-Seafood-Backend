<?php

namespace App\Filament\Resources\PesananResource\Pages;

use App\Filament\Resources\PesananResource;
use App\Models\Pesanan; // Pastikan model Pesanan di-import
use Filament\Actions; // Namespace utama untuk Actions
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Textarea as FormTextarea; // Alias untuk Textarea di form modal
use Filament\Forms\Components\TextInput; // Untuk nomor resi
use Filament\Notifications\Notification;

class ViewPesanan extends ViewRecord
{
    protected static string $resource = PesananResource::class;

    // HAPUS atau KOMENTARI baris ini agar Filament menggunakan form() dari PesananResource
    // protected static string $view = 'filament.resources.pesanan-resource.pages.view-pesanan';

    protected function getHeaderActions(): array
    {
        return [
            // Halaman View hanya untuk melihat detail pesanan
            // Semua aksi edit dan perubahan status dipindahkan ke halaman Edit
            Actions\EditAction::make()
                ->label('Edit Pesanan')
                ->icon('heroicon-o-pencil-square'),
        ];
    }
}