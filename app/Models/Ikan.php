<?php
// app/Models/Ikan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ikan extends Model
{
    use HasFactory;
    protected $table = 'ikan'; // Pastikan nama tabel di database adalah 'ikan'

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kategori_id',
        'nama_ikan',
        'slug',
        'deskripsi',
        'harga',
        'stok',
        'status_ketersediaan',
        'gambar_utama',
    ];

    protected $casts = [
        'harga' => 'integer', // Pastikan harga disimpan sebagai integer (misal: dalam sen/rupiah tanpa desimal)
        'stok' => 'integer',
    ];

    /**
     * Get the category that owns the Ikan.
     */
    public function kategori(): BelongsTo
    {
        // Pastikan 'kategori_id' adalah foreign key di tabel 'ikan'
        // dan 'id' adalah primary key di tabel 'kategori_ikan'
        return $this->belongsTo(KategoriIkan::class, 'kategori_id');
    }

    /**
     * The pesanan that belong to the Ikan.
     */
    public function pesanan(): BelongsToMany
    {
        return $this->belongsToMany(Pesanan::class, 'ikan_pesanan')
            ->withPivot('jumlah', 'harga_saat_pesan')
            ->withTimestamps();
    }
}