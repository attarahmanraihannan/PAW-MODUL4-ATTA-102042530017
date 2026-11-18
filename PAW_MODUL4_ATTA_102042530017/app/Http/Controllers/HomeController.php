<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Menampilkan halaman katalog buku dengan fitur pencarian, filter, sorting, dan paginasi.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $penerbitFilter = $request->input('penerbit');
        $statusFilter = $request->input('status');
        $sort = $request->input('sort');

        $query = Buku::query();

        // 1. Pencarian berdasarkan judul, penulis, atau penerbit
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('penulis', 'like', "%{$search}%")
                  ->orWhere('penerbit', 'like', "%{$search}%");
            });
        }

        // 2. Filter berdasarkan penerbit
        if ($penerbitFilter) {
            $query->where('penerbit', $penerbitFilter);
        }

        // 2. Filter berdasarkan ketersediaan stok
        if ($statusFilter === 'tersedia') {
            $query->where('stok', '>', 0);
        } elseif ($statusFilter === 'habis') {
            $query->where('stok', '<=', 0);
        }

        // 3. Pengurutan data (sorting)
        if ($sort === 'judul') {
            $query->orderBy('judul', 'asc');
        } elseif ($sort === 'tahun') {
            $query->orderBy('tahun_terbit', 'desc');
        } elseif ($sort === 'stok') {
            $query->orderBy('stok', 'desc');
        } else {
            $query->latest(); // Default terbaru
        }

        // 4. Paginasi 8 data dengan withQueryString()
        $buku = $query->paginate(8)->withQueryString();

        // 5. Ambil list penerbit unik untuk opsi dropdown filter
        $listPenerbit = Buku::select('penerbit')->distinct()->pluck('penerbit');

        // 6. Return view 'home' dengan data buku dan list penerbit
        return view('home', compact('buku', 'search', 'listPenerbit'));
        }

    /**
     * Menampilkan halaman detail informasi sebuah buku untuk publik/mahasiswa.
     */
        public function show(Buku $buku)
        {
            return view('home_show', compact('buku'));
        }
}
