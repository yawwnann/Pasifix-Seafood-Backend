<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IkanResource\Pages;
use App\Models\Ikan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Forms\Set;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
// use Cloudinary\Cloudinary; // Tidak perlu di-import jika hanya digunakan di fungsi statis terpisah
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder; // Pastikan ini di-import!

class IkanResource extends Resource
{
    protected static ?string $model = Ikan::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $modelLabel = 'Ikan';
    protected static ?string $pluralModelLabel = 'Daftar Ikan';
    protected static ?string $navigationGroup = 'Manajemen Katalog';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('kategori_id')
                    ->relationship('kategori', 'nama_kategori')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Kategori Ikan'),
                Forms\Components\TextInput::make('nama_ikan')
                    ->required()
                    ->maxLength(150)
                    ->live(debounce: 500)
                    ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state))),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->unique(Ikan::class, 'slug', ignoreRecord: true)
                    ->maxLength(170),
                Forms\Components\RichEditor::make('deskripsi')
                    ->nullable()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('harga')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('stok')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                Forms\Components\Select::make('status_ketersediaan')
                    ->options([
                        'Tersedia' => 'Tersedia',
                        'Habis' => 'Habis',
                        'Pre-Order' => 'Pre-Order',
                    ])
                    ->required()
                    ->default('Tersedia'),
                FileUpload::make('gambar_utama')
                    ->label('Gambar Utama (Max 1MB)')
                    ->image()
                    ->disk('cloudinary')
                    ->directory('ikan_images')
                    ->visibility('public')
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // Kita akan memindahkan eager loading ke getEloquentQuery()
            // ->query(function (Builder $query) { // HAPUS ATAU KOMENTARI BARIS INI
            //     $query->with(['kategori']);    // HAPUS ATAU KOMENTARI BARIS INI
            // })                                // HAPUS ATAU KOMENTARI BARIS INI
            ->deferLoading() // Tetap gunakan ini untuk initial render yang lebih cepat
            ->columns([
                TextColumn::make('nama_ikan')
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('gambar_utama')
                    ->label('Gambar')
                    ->disk('cloudinary')
                    ->width(80)
                    ->height(60)
                    ->defaultImageUrl(url('/images/placeholder.png')),
                TextColumn::make('kategori.nama_kategori')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('harga')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('stok')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status_ketersediaan')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Tersedia' => 'success',
                        'Habis' => 'danger',
                        'Pre-Order' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kategori_id')
                    ->relationship('kategori', 'nama_kategori')
                    ->label('Filter Kategori'),
                SelectFilter::make('status_ketersediaan')
                    ->options([
                        'Tersedia' => 'Tersedia',
                        'Habis' => 'Habis',
                        'Pre-Order' => 'Pre-Order',
                    ])
                    ->label('Filter Status'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    // --- BAGIAN PERBAIKAN UTAMA: TAMBAHKAN METODE INI ---
    // Ini adalah cara yang benar untuk meng-override query dasar tabel di Filament v2.x
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['kategori']);
    }
    // --- AKHIR BAGIAN PERBAIKAN UTAMA ---

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIkans::route('/'),
            'create' => Pages\CreateIkan::route('/create'),
            'edit' => Pages\EditIkan::route('/{record}/edit'),
        ];
    }
}