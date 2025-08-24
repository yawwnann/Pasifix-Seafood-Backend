<?php

namespace App\Filament\Resources\PesananResource\Pages;

use App\Filament\Resources\PesananResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Textarea as FormTextarea;
use Filament\Forms\Components\TextInput;

class EditPesanan extends EditRecord
{
    protected static string $resource = PesananResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            
            // Quick Status Actions - untuk mengubah status dengan cepat
            Actions\Action::make('konfirmasiLunas')
                ->label('Tandai Lunas')
                ->color('success')
                ->icon('heroicon-o-check-badge')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Pembayaran Pesanan')
                ->modalDescription('Yakin ingin menandai pesanan ini LUNAS dan status pembayaran juga LUNAS?')
                ->modalSubmitActionLabel('Ya, Tandai Lunas')
                ->action(function () {
                    /** @var \App\Models\Pesanan $pesanan */
                    $pesanan = $this->record;
                    $pesanan->status = 'lunas';
                    $pesanan->status_pembayaran = 'lunas';
                    $pesanan->save();
                    Notification::make()->title('Pembayaran Dikonfirmasi Lunas')->success()->send();
                    $this->refreshFormData([]);
                })
                ->visible(
                    fn(): bool => $this->record &&
                    ($this->record->status === 'menunggu_konfirmasi_pembayaran' || $this->record->status === 'baru') &&
                    !empty($this->record->payment_proof_path) &&
                    $this->record->status_pembayaran !== 'lunas'
                ),

            Actions\Action::make('tolakPembayaran')
                ->label('Tolak Pembayaran')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->form([
                    FormTextarea::make('alasan_penolakan')
                        ->label('Alasan Penolakan')
                        ->required()->rows(3)->maxLength(255),
                ])
                ->action(function (array $data) {
                    /** @var \App\Models\Pesanan $pesanan */
                    $pesanan = $this->record;
                    $pesanan->status = 'dibatalkan';
                    $pesanan->status_pembayaran = 'gagal';
                    $pesanan->catatan_admin = ($pesanan->catatan_admin ? $pesanan->catatan_admin . "\n" : '') . "Pembayaran ditolak: " . $data['alasan_penolakan'];
                    $pesanan->save();
                    Notification::make()->title('Pembayaran Ditolak')->danger()->send();
                    $this->refreshFormData([]);
                })
                ->visible(
                    fn(): bool => $this->record &&
                    ($this->record->status === 'menunggu_konfirmasi_pembayaran' || $this->record->status === 'baru') &&
                    !empty($this->record->payment_proof_path) &&
                    $this->record->status_pembayaran !== 'lunas' &&
                    !in_array($this->record->status, ['dibatalkan', 'pembayaran_ditolak'])
                ),

            Actions\Action::make('tandaiDiproses')
                ->label('Tandai Diproses')
                ->color('info')
                ->icon('heroicon-o-cog-8-tooth')
                ->requiresConfirmation()
                ->modalHeading('Tandai Pesanan Diproses')
                ->action(function () {
                    /** @var \App\Models\Pesanan $pesanan */
                    $pesanan = $this->record;
                    $pesanan->status = 'diproses';
                    $pesanan->save();
                    Notification::make()->title('Pesanan Ditandai Diproses')->success()->send();
                    $this->refreshFormData([]);
                })
                ->visible(
                    fn(): bool => $this->record &&
                    ($this->record->status_pembayaran === 'lunas' || $this->record->status === 'lunas') &&
                    !in_array($this->record->status, ['diproses', 'dikirim', 'selesai', 'dibatalkan'])
                ),

            Actions\Action::make('tandaiDikirim')
                ->label('Tandai Dikirim')
                ->color('primary')
                ->icon('heroicon-o-truck')
                ->requiresConfirmation()
                ->modalHeading('Tandai Pesanan Dikirim')
                ->modalDescription('Pastikan nomor resi sudah diisi di form sebelum menandai pesanan sebagai dikirim.')
                ->action(function () {
                    /** @var \App\Models\Pesanan $pesanan */
                    $pesanan = $this->record;
                    $pesanan->status = 'dikirim';
                    $pesanan->save();
                    Notification::make()->title('Pesanan Ditandai Dikirim')->success()->send();
                    $this->refreshFormData([]);
                })
                ->visible(fn(): bool => $this->record && $this->record->status === 'diproses'),

            Actions\Action::make('tandaiSelesai')
                ->label('Tandai Selesai')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('Tandai Pesanan Selesai')
                ->action(function () {
                    /** @var \App\Models\Pesanan $pesanan */
                    $pesanan = $this->record;
                    $pesanan->status = 'selesai';
                    $pesanan->save();
                    Notification::make()->title('Pesanan Ditandai Selesai')->success()->send();
                    $this->refreshFormData([]);
                })
                ->visible(fn(): bool => $this->record && $this->record->status === 'dikirim'),

            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var \App\Models\Pesanan $pesananRecord */
        $pesananRecord = $this->getRecord();
        $pesananRecord->loadMissing('items');

        $itemsDataFormatted = [];
        if ($pesananRecord->relationLoaded('items') && $pesananRecord->items->isNotEmpty()) {
            foreach ($pesananRecord->items as $ikanDalamPesanan) {
                $pivotData = $ikanDalamPesanan->pivot;
                if ($pivotData) {
                    $itemsDataFormatted[] = [
                        'ikan_id' => $ikanDalamPesanan->id,
                        'jumlah' => $pivotData->jumlah,
                        'harga_saat_pesan' => $pivotData->harga_saat_pesan,
                    ];
                }
            }
        }
        $data['items'] = $itemsDataFormatted;

        $total = 0;
        foreach ($itemsDataFormatted as $item) {
            $jumlah = $item['jumlah'] ?? 0;
            $harga = $item['harga_saat_pesan'] ?? 0;
            if (is_numeric($jumlah) && is_numeric($harga)) {
                $total += $jumlah * $harga;
            }
        }
        $data['total_harga'] = $total;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            /** @var \App\Models\Pesanan $record */
            $itemsDataFromForm = $data['items'] ?? [];
            $pesananDataToUpdate = Arr::except($data, ['items']);

            $calculatedTotal = 0;
            $pivotDataForSync = [];
            if (is_array($itemsDataFromForm)) {
                foreach ($itemsDataFromForm as $item) {
                    $ikanId = $item['ikan_id'] ?? null;
                    $jumlah = $item['jumlah'] ?? 0;
                    $harga = $item['harga_saat_pesan'] ?? 0;

                    if ($ikanId && is_numeric($jumlah) && $jumlah > 0 && is_numeric($harga)) {
                        $calculatedTotal += $jumlah * $harga;
                        $pivotDataForSync[$ikanId] = [
                            'jumlah' => $jumlah,
                            'harga_saat_pesan' => $harga,
                        ];
                    }
                }
            }
            $pesananDataToUpdate['total_harga'] = $calculatedTotal;

            $record->fill($pesananDataToUpdate);
            $record->save();

            if (is_array($itemsDataFromForm)) {
                $record->items()->sync($pivotDataForSync);
            }

            $record->refresh()->load('items');

            Notification::make()
                ->title('Pesanan berhasil diperbarui')
                ->success()
                ->send();

            return $record;
        });
    }
}