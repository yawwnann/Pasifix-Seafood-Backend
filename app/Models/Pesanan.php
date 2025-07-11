<?php
// File: app/Models/Pesanan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Ikan; // Pastikan model Ikan ada di App\Models\Ikan
use App\Models\User; // Pastikan model User ada di App\Models\User
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Pesanan extends Model
{
    use HasFactory;

    protected $table = 'pesanan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'nama_pelanggan',
        'nomor_whatsapp',
        'alamat_pengiriman',
        'total_harga',
        'tanggal_pesan',
        'status', // Ini status pemrosesan pesanan (misal: 'baru', 'diproses', 'dikirim')
        'catatan',
        // Kolom baru untuk integrasi Midtrans
        'metode_pembayaran',
        'status_pembayaran',
        'catatan_admin',
        'nomor_resi',  // (misal: 'pending', 'paid', 'failed', 'settlement')
        'ada_perubahan_status_belum_dilihat',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_pesan' => 'date',
        'total_harga' => 'integer',
        'ada_perubahan_status_belum_dilihat' => 'boolean',
    ];

    /**
     * Relasi Many-to-Many ke model Ikan melalui tabel pivot 'ikan_pesanan'.
     * Nama method relasi ini HARUS 'items' agar cocok dengan Repeater::make('items')->relationship().
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Ikan::class, 'ikan_pesanan')
            ->withPivot('jumlah', 'harga_saat_pesan') // Ini sudah benar
            ->withTimestamps(); // Jika ada timestamps di pivot
    }

    /**
     * Relasi BelongsTo ke model User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getPaymentProofThumbnailAttribute(): ?string
    {
        if ($this->payment_proof_path) {
            // Asumsi payment_proof_path adalah URL Cloudinary lengkap
            if (Str::contains($this->payment_proof_path, '/upload/')) {
                return Str::replaceFirst('/upload/', '/upload/w_80,h_80,c_thumb,q_auto,f_auto/', $this->payment_proof_path);
            }
            return $this->payment_proof_path;
        }
        return null;
    }

    public function getFormattedStatusAttribute(): string // Untuk tampilan status pesanan yang lebih baik
    {
        if (!$this->status)
            return 'N/A';
        return ucwords(str_replace('_', ' ', $this->status));
    }

    public function getFormattedStatusPembayaranAttribute(): string // Untuk status pembayaran
    {
        if (!$this->status_pembayaran)
            return 'N/A'; // Pastikan kolom status_pembayaran ada
        return ucwords(str_replace('_', ' ', $this->status_pembayaran));
    }
}