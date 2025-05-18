<?php
// File: app/Http/Resources/PesananResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
// use App\Http\Resources\UserResource; // Uncomment jika Anda punya dan menggunakan UserResource

class PesananResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Memastikan relasi 'items' dan 'user' (jika ada) sudah dimuat
        // PesananApiController Anda seharusnya sudah melakukan ->with(['user', 'items'])
        // $this->loadMissing(['items', 'user']); // Alternatif jika tidak di-load di controller

        return [
            'id' => $this->id,
            'nama_pelanggan' => $this->nama_pelanggan,
            'nomor_whatsapp' => $this->nomor_whatsapp,
            'alamat_pengiriman' => $this->alamat_pengiriman,
            'total_harga' => (float) $this->total_harga,
            'tanggal_pesan' => $this->tanggal_pesan ? $this->tanggal_pesan->format('Y-m-d H:i:s') : ($this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null),
            'status' => $this->status,
            'catatan' => $this->catatan,
            'nomor_resi' => $this->nomor_resi,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,

            // Field yang dibutuhkan oleh PesananDetailPage.jsx
            'status_pembayaran' => $this->status_pembayaran, // Pastikan kolom ini ada di tabel 'pesanan'
            'payment_proof_url' => $this->payment_proof_path, // URL Cloudinary untuk bukti bayar pesanan
            'metode_pembayaran' => $this->metode_pembayaran, // Pastikan kolom ini ada

            // Relasi user (jika ada dan dibutuhkan)
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }, null),

            // Sertakan detail item ikan yang dipesan
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($ikan) { // $ikan di sini adalah instance model Ikan
                    return [
                        'id' => $ikan->id,
                        'ikan_id' => $ikan->id,
                        'nama_ikan' => $ikan->nama_ikan,
                        'slug' => $ikan->slug,
                        'gambar_utama' => $ikan->gambar_utama, // Untuk gambar ikan per item
                        'jumlah' => $ikan->pivot->jumlah,
                        'harga_saat_pesan' => (float) $ikan->pivot->harga_saat_pesan,
                        'subtotal' => (float) ($ikan->pivot->jumlah * $ikan->pivot->harga_saat_pesan),
                    ];
                });
            }, []),
        ];
    }
}