<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IkanResource;
use App\Http\Resources\KategoriResource;
use App\Models\Ikan;
use App\Models\KategoriIkan;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class IkanController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:100',
            'sort_by' => 'nullable|string|in:harga,created_at,nama_ikan',
            'sort_direction' => 'nullable|string|in:asc,desc',
            'status_ketersediaan' => 'nullable|string|in:tersedia,habis',
            'kategori_slug' => 'nullable|string|exists:kategori_ikan,slug'
        ]);

        $searchQuery = $request->query('q');
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDirection = $request->query('sort_direction', 'desc');
        $statusKetersediaan = $request->query('status_ketersediaan');
        $kategoriSlug = $request->query('kategori_slug');

        $ikanQuery = Ikan::with('kategori');

        // Filter berdasarkan status ketersediaan
        if ($statusKetersediaan) {
            $ikanQuery->where('status_ketersediaan', 'LIKE', "%{$statusKetersediaan}%");
        }

        if ($kategoriSlug) {
            $ikanQuery->whereHas('kategori', function (Builder $query) use ($kategoriSlug) {
                $query->where('slug', $kategoriSlug);
            });
        }

        if ($searchQuery) {
            $ikanQuery->where(function (Builder $query) use ($searchQuery) {
                $query->where('nama_ikan', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('deskripsi', 'LIKE', "%{$searchQuery}%")
                    ->orWhereHas('kategori', function (Builder $subQuery) use ($searchQuery) {
                        $subQuery->where('nama_kategori', 'LIKE', "%{$searchQuery}%");
                    });
            });
        }

        // Sorting
        $allowedSorts = ['harga', 'created_at', 'nama_ikan'];
        $sortField = in_array($sortBy, $allowedSorts) ? $sortBy : 'created_at';
        $sortDir = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        $ikanQuery->orderBy($sortField, $sortDir);

        // Secondary sort untuk konsistensi
        if ($sortField !== 'nama_ikan') {
            $ikanQuery->orderBy('nama_ikan', 'asc');
        }

        $ikan = $ikanQuery->paginate(12)->withQueryString();

        return IkanResource::collection($ikan);
    }

    public function show(Ikan $ikan)
    {
        $ikan->loadMissing('kategori');

        return new IkanResource($ikan);
    }

    public function daftarKategori()
    {
        $kategori = KategoriIkan::orderBy('nama', 'asc')->get();

        return KategoriResource::collection($kategori);
    }
}
