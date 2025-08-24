<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PesananResource\Pages;
// use App\Filament\Resources\PesananResource\RelationManagers\ItemsRelationManager;
use App\Models\Pesanan;
use App\Models\Ikan;
use App\Models\KategoriIkan;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select as FormSelect;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Grid;
use Illuminate\Support\HtmlString;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Forms\Components\Actions\Action as FormComponentAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

if (!function_exists('App\Filament\Resources\formatFilamentRupiah')) {
    function formatFilamentRupiah($number)
    {
        if ($number === null || is_nan((float) $number))
            return 'Rp 0';
        return 'Rp ' . number_format((float) $number, 0, ',', '.');
    }
}

class PesananResource extends Resource
{
    protected static ?string $model = Pesanan::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $modelLabel = 'Pesanan';
    protected static ?string $pluralModelLabel = 'Manajemen Pesanan';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'id';

    public static function getStatusPesananOptions(): array
    {
        return [
            'baru' => 'Baru',
            'menunggu_konfirmasi_pembayaran' => 'Menunggu Konfirmasi Pembayaran',
            'lunas' => 'Lunas (Pembayaran Dikonfirmasi)',
            'diproses' => 'Diproses',
            'dikirim' => 'Dikirim',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
        ];
    }
    public static function getStatusPembayaranOptions(): array
    {
        return [
            'pending' => 'Pending',
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'lunas' => 'Lunas',
            'gagal' => 'Gagal',
            'expired' => 'Kadaluarsa',
            'dibatalkan' => 'Dibatalkan',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    Section::make('Informasi Dasar Pesanan')
                        ->columns(2)->columnSpan(2)
                        ->schema([
                            TextInput::make('id')->label('ID Pesanan')->disabled()->dehydrated(false)->visibleOn('view'),
                            TextInput::make('nama_pelanggan')->required()->maxLength(255)
                                ->disabled(fn(string $operation): bool => $operation === 'view'),
                            TextInput::make('nomor_whatsapp')->label('Nomor WhatsApp')->tel()->maxLength(20)->required()
                                ->disabled(fn(string $operation): bool => $operation === 'view'),
                            Select::make('user_id')->label('User Terdaftar (Opsional)')->relationship('user', 'name')
                                ->searchable()->preload()->placeholder('Pilih User Akun')
                                ->helperText('Kosongkan jika pesanan dari user non-akun.')
                                ->disabled(fn(string $operation): bool => $operation === 'view'),
                            DatePicker::make('tanggal_pesan')->label('Tanggal Pesan')->default(now())
                                ->disabled(fn(string $operation): bool => $operation === 'view'),
                            Textarea::make('alamat_pengiriman')->label('Alamat Pengiriman')->rows(3)
                                ->required(fn(string $operation): bool => $operation === 'create')
                                ->columnSpanFull()->disabledOn('view'),
                            Textarea::make('catatan')->label('Catatan Pelanggan')->rows(3)->nullable()->columnSpanFull()
                                ->disabledOn('view'),
                        ]),

                    Section::make('Status & Pembayaran')
                        ->columnSpan(1)
                        ->schema([
                            TextInput::make('total_harga')->label('Total Keseluruhan')->numeric()->prefix('Rp')->readOnly(),
                            Select::make('status')->label('Status Pesanan')
                                ->options(self::getStatusPesananOptions())->required()->default('baru')->native(false)
                                // Di halaman Edit, admin bisa mengubah status ini
                                // Di halaman View, status ini read-only untuk melihat detail saja
                                ->disabled(
                                    fn(string $operation, ?Pesanan $record): bool =>
                                    $operation === 'view' || // Selalu disable di view
                                    ($operation === 'create') ||
                                    (isset($record) && in_array($record->status, ['selesai', 'dibatalkan']))
                                ),
                            Select::make('status_pembayaran')->label('Status Pembayaran')
                                ->options(self::getStatusPembayaranOptions())->placeholder('Pilih Status Pembayaran')->native(false)
                                ->disabled(
                                    fn(string $operation, ?Pesanan $record): bool =>
                                    $operation === 'view' || // Selalu disable di view
                                    ($operation === 'create' && !$record?->status_pembayaran) ||
                                    (isset($record) && in_array($record->status_pembayaran, ['lunas', 'gagal', 'expired', 'dibatalkan']))
                                ),
                            Textarea::make('catatan_admin')
                                ->label('Catatan Internal Admin') // Label sebelumnya 'Catatan Admin'
                                ->rows(4) // Saya menaikkan rows dari 3 ke 4 di contoh terakhir
                                ->nullable()
                                ->columnSpanFull()
                                // Logika disabled ini akan membuat field bisa diedit di halaman 'edit'
                                // dan hanya disabled di halaman 'view'
                                ->disabled(fn(string $operation): bool => $operation === 'view'),

                        ]),
                ]),

                Section::make('Item Ikan Dipesan')
                    ->collapsible()
                    ->schema([
                        Repeater::make('items')
                            ->label(fn(string $operation) => $operation === 'view' ? '' : 'Item Ikan')
                            // ->relationship() // DIKOMENTARI: Mengandalkan mutateFormDataBeforeFill di EditPesanan.php
                            ->schema([
                                Select::make('ikan_id')->label('Pilih Ikan')
                                    ->options(function (Get $get) {
                                        $currentItems = $get('../../items') ?? [];
                                        $existingIkanIdsInRepeater = collect($currentItems)->pluck('ikan_id')->filter()->all();
                                        return Ikan::query()
                                            ->where('stok', '>', 0)
                                            ->orWhereIn('id', $existingIkanIdsInRepeater)
                                            ->orderBy('nama_ikan')->pluck('nama_ikan', 'id');
                                    })
                                    ->required()->reactive()->searchable()->preload()
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        $ikan = Ikan::find($state);
                                        $set('harga_saat_pesan', $ikan?->harga ?? 0);
                                    })
                                    ->distinct()->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->columnSpan(['md' => 4]),
                                TextInput::make('jumlah')->label('Jumlah')->numeric()->required()->minValue(1)->default(1)->reactive()
                                    ->columnSpan(['md' => 2]),
                                TextInput::make('harga_saat_pesan')->label('Harga Satuan')->numeric()->prefix('Rp')->required()
                                    ->disabled()->dehydrated()
                                    ->columnSpan(['md' => 2]),
                            ])
                            ->columns(8)
                            ->defaultItems(fn(string $operation) => $operation === 'create' ? 1 : 0)
                            ->addActionLabel('Tambah Item Ikan')
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn(Get $get, Set $set) => self::updateTotalPrice($get, $set))
                            ->deleteAction(
                                fn(FormComponentAction $action) => $action
                                    ->after(fn(Get $get, Set $set) => self::updateTotalPrice($get, $set))
                                    ->requiresConfirmation()
                            )
                            ->reorderable(false)->columnSpanFull()->hiddenOn('view'),

                        Placeholder::make('items_view_display')
                            ->label(fn(string $operation) => $operation === 'view' ? 'Rincian Item Dipesan' : '')
                            ->content(function (?Pesanan $record): HtmlString {
                                if (!$record || !$record->items || $record->items->isEmpty()) {
                                    return new HtmlString('<div class="text-sm text-gray-500 dark:text-gray-400 italic py-2">Tidak ada item dalam pesanan ini.</div>');
                                }
                                $html = '<ul class="mt-2 border border-gray-200 dark:border-white/10 rounded-md divide-y divide-gray-200 dark:divide-white/10">';
                                foreach ($record->items as $itemIkan) {
                                    $namaIkan = e($itemIkan->nama_ikan);
                                    $jumlah = e($itemIkan->pivot->jumlah);
                                    $harga = formatFilamentRupiah($itemIkan->pivot->harga_saat_pesan);
                                    $subtotal = formatFilamentRupiah($itemIkan->pivot->jumlah * $itemIkan->pivot->harga_saat_pesan);
                                    $gambarUrl = $itemIkan->gambar_utama ? 'https://res.cloudinary.com/dm3icigfr/image/upload/w_60,h_60,c_thumb,q_auto,f_auto/' . e($itemIkan->gambar_utama) : asset('images/placeholder_small.png');
                                    $html .= "<li class=\"flex items-center justify-between py-3 px-4 text-sm hover:bg-gray-50 dark:hover:bg-white/5\">";
                                    $html .= "<div class=\"flex items-center\">";
                                    $html .= "<img src=\"{$gambarUrl}\" alt=\"{$namaIkan}\" class=\"w-10 h-10 rounded-md object-cover mr-3 flex-shrink-0\"/>";
                                    $html .= "<div><span class=\"font-medium text-gray-900 dark:text-white\">{$namaIkan}</span><br><span class=\"text-gray-500 dark:text-gray-400\">{$jumlah} x {$harga}</span></div>";
                                    $html .= "</div>";
                                    $html .= "<span class=\"font-medium text-gray-900 dark:text-white\">{$subtotal}</span>";
                                    $html .= "</li>";
                                }
                                $html .= '</ul>';
                                return new HtmlString($html);
                            })->visibleOn('view')->columnSpanFull(),
                    ]),

                Section::make('Informasi Pengiriman & Bukti Bayar')
                    ->collapsible()
                    ->columns(1)
                    ->schema([
                        // Nomor Resi - hanya tampil di view, di edit akan ada input field
                        Placeholder::make('nomor_resi_display')
                            ->label('Nomor Resi Pengiriman')
                            ->content(fn(?Pesanan $record): string => $record?->nomor_resi ?: 'Belum ada nomor resi.')
                            ->visible(fn(?Pesanan $record, string $operation): bool => $operation === 'view' && !empty($record?->nomor_resi)),

                        // Input field untuk nomor resi - hanya tampil di edit
                        TextInput::make('nomor_resi')
                            ->label('Nomor Resi Pengiriman')
                            ->maxLength(255)
                            ->nullable()
                            ->helperText('Masukkan nomor resi pengiriman jika pesanan sudah dikirim')
                            ->visible(fn(string $operation): bool => $operation === 'edit'),

                        Placeholder::make('payment_proof_display')
                            ->label(fn(?Pesanan $record, string $operation): string => ($operation !== 'create' && !empty($record?->payment_proof_path)) ? 'Bukti Pembayaran Diunggah' : '')
                            ->content(function (?Pesanan $record): HtmlString {
                                if ($record && $record->payment_proof_path) {
                                    $url = e($record->payment_proof_path);
                                    $imgTag = '<img src="' . $url . '" alt="Bukti Pembayaran" style="max-width: 100%; max-height: 300px; border: 1px solid #e2e8f0; border-radius: 0.375rem; margin-top: 0.5rem; object-fit: contain; background-color: #f9fafb;" />';
                                    $linkTag = '<p style="margin-top: 0.5rem;"><a href="' . $url . '" target="_blank" rel="noopener noreferrer" style="color: #2563eb; text-decoration: underline;">Lihat gambar ukuran penuh</a></p>';
                                    return new HtmlString($imgTag . $linkTag);
                                }
                                return new HtmlString('');
                            })
                            ->visible(fn(?Pesanan $record, string $operation): bool => $operation !== 'create' && !empty($record?->payment_proof_path)),
                    ])->columnSpanFull(),
            ]);
    }

    public static function updateTotalPrice(Get $get, Set $set): void
    {
        $itemsData = $get('items') ?? [];
        $total = 0;
        if (is_array($itemsData)) {
            foreach ($itemsData as $item) {
                $jumlah = $item['jumlah'] ?? 0;
                $harga = $item['harga_saat_pesan'] ?? 0;
                if (is_numeric($jumlah) && is_numeric($harga)) {
                    $total += $jumlah * $harga;
                }
            }
        }
        $set('total_harga', $total);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tanggal_pesan')->dateTime('d M Y')->sortable()->label('Tgl Pesan'),
                TextColumn::make('nama_pelanggan')->searchable()->sortable(),
                TextColumn::make('total_harga')->money('IDR')->sortable()->label('Total'),
                TextColumn::make('nomor_resi')->label('No. Resi')->searchable()->default('-')->copyable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn(Pesanan $record): string => $record->formatted_status)
                    ->color(fn(Pesanan $record): string => match (strtolower($record->status ?? '')) {
                        'baru', 'pending' => 'gray',
                        'menunggu_konfirmasi_pembayaran' => 'warning',
                        'lunas', 'lunas (pembayaran dikonfirmasi)' => 'success',
                        'diproses' => 'info',
                        'dikirim' => 'primary',
                        'selesai' => 'success',
                        'dibatalkan', 'batal' => 'danger',
                        default => 'gray',
                    })->searchable(),
                TextColumn::make('status_pembayaran')->label('Sts. Bayar')->badge()
                    ->formatStateUsing(fn(Pesanan $record): string => $record->formatted_status_pembayaran)
                    ->color(fn(?string $state): string => match (strtolower($state ?? '')) {
                        'pending', 'menunggu_pembayaran' => 'warning',
                        'lunas' => 'success',
                        'gagal', 'failed', 'expired', 'dibatalkan' => 'danger',
                        default => 'gray',
                    })->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')->label('Akun User')->searchable()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(self::getStatusPesananOptions()),
                SelectFilter::make('status_pembayaran')->options(self::getStatusPembayaranOptions()),
            ])
            ->actions([
                ViewAction::make()->iconButton()->color('gray'),
                EditAction::make()->iconButton(), // Tombol Edit tetap ada di tabel
                // Aksi lain bisa ditambahkan di sini atau di ViewPesanan.php -> getHeaderActions()
            ])

            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make(),]),])
            ->defaultSort('tanggal_pesan', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPesanans::route('/'),
            'create' => Pages\CreatePesanan::route('/create'),
            'view' => Pages\ViewPesanan::route('/{record}'),
            'edit' => Pages\EditPesanan::route('/{record}/edit'),
        ];
    }
}