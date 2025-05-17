<?php
// File: app/Filament/Resources/PesananResource/Pages/EditPesanan.php

namespace App\Filament\Resources\PesananResource\Pages;

use App\Filament\Resources\PesananResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model; // Penting
use Illuminate\Support\Facades\Log;   // Untuk Debug

class EditPesanan extends EditRecord
{
    protected static string $resource = PesananResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    // Ini method krusial untuk memastikan data items terisi di Repeater saat Edit
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var \App\Models\Pesanan $pesananRecord */
        $pesananRecord = $this->getRecord();
        $pesananRecord->loadMissing('items'); // Pastikan relasi 'items' sudah dimuat

        Log::info('[EditPesanan] Mutating form data before fill for Pesanan ID: ' . $pesananRecord->id);

        $itemsDataFormatted = [];
        if ($pesananRecord->relationLoaded('items') && $pesananRecord->items->isNotEmpty()) {
            foreach ($pesananRecord->items as $ikanItem) { // $ikanItem adalah instance model Ikan
                $itemsDataFormatted[] = [
                    'ikan_id' => $ikanItem->id,
                    'jumlah' => $ikanItem->pivot->jumlah,
                    'harga_saat_pesan' => $ikanItem->pivot->harga_saat_pesan,
                ];
            }
            Log::info('[EditPesanan] Formatted items data: ', $itemsDataFormatted);
        } else {
            Log::info('[EditPesanan] No items found or relation not loaded for Pesanan ID: ' . $pesananRecord->id);
        }
        $data['items'] = $itemsDataFormatted; // Umpankan data items yang sudah diformat ke form

        // Anda bisa juga mengisi ulang total_harga di sini jika perlu,
        // meskipun Repeater dan updateTotalPrice seharusnya menanganinya saat form interaktif.
        // $total = 0;
        // foreach ($itemsDataFormatted as $item) { /* ... kalkulasi ... */ }
        // $data['total_harga'] = $total;

        Log::info('[EditPesanan] Final data to be filled: ', $data);
        return $data;
    }

    // Method handleRecordUpdate Anda yang sudah ada
    // protected function handleRecordUpdate(Model $record, array $data): Model { ... }
}