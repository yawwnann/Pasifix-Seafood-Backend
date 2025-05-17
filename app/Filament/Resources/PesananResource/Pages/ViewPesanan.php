<?php

namespace App\Filament\Resources\PesananResource\Pages;

use App\Filament\Resources\PesananResource;
use App\Models\Pesanan;
use Filament\Actions; // Namespace utama untuk Actions
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Textarea as FormTextarea;
use Filament\Forms\Components\TextInput; // Untuk nomor resi di aksi 'tandaiDikirim'
use Filament\Notifications\Notification;

class ViewPesanan extends ViewRecord
{
    protected static string $resource = PesananResource::class;

    // Hapus atau komentari baris ini jika Anda tidak menggunakan Blade view kustom untuk halaman ini
    // protected static string $view = 'filament.resources.pesanan-resource.pages.view-pesanan';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(), // <-- TOMBOL EDIT STANDAR DIHAPUS/DIKOMENTARI

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
                    $pesanan->status_pembayaran = 'lunas'; // Pastikan kolom ini ada
                    $pesanan->save();
                    Notification::make()->title('Pembayaran Dikonfirmasi Lunas')->success()->send();
                    $this->refreshFormData([]); // Refresh data form di halaman view
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
                    $pesanan->status = 'dibatalkan'; // Atau 'pembayaran_ditolak'
                    $pesanan->status_pembayaran = 'gagal'; // Atau 'ditolak'
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
                ->form([
                    TextInput::make('nomor_resi')
                        ->label('Nomor Resi Pengiriman (Opsional)')
                        ->nullable(),
                ])
                ->action(function (array $data) {
                    /** @var \App\Models\Pesanan $pesanan */
                    $pesanan = $this->record;
                    $pesanan->status = 'dikirim';
                    $catatanResi = !empty($data['nomor_resi']) ? "No. Resi: " . $data['nomor_resi'] : "Pesanan telah dikirim tanpa nomor resi terpisah.";
                    $pesanan->catatan_admin = ($pesanan->catatan_admin ? $pesanan->catatan_admin . "\n" : '') . $catatanResi;
                    $pesanan->save();
                    Notification::make()->title('Pesanan Ditandai Dikirim')->success()->send();
                    $this->refreshFormData([]);
                    // TODO: Kirim notifikasi ke pelanggan bahwa pesanan dikirim
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
                    // Biasanya jika sudah dikirim, status pembayaran sudah lunas
                    // $pesanan->status_pembayaran = 'lunas';
                    $pesanan->save();
                    Notification::make()->title('Pesanan Ditandai Selesai')->success()->send();
                    $this->refreshFormData([]);
                })
                ->visible(fn(): bool => $this->record && $this->record->status === 'dikirim'),

            // Actions\DeleteAction::make(), // Tombol delete standar jika masih diperlukan
        ];
    }
}