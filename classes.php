<?php
// Kita tidak lagi butuh session_start() untuk penyimpanan data buku,
// tapi jika Anda butuh untuk flash message/login, boleh tetap ada.
// session_start(); 

// --- KONFIGURASI FILE JSON ---
define('DB_FILE', 'data.json');

// --- OOP INHERITANCE ---

// Parent Class
class Koleksi {
    public $judul;
    public $gambar;
    public $catatan;

    public function __construct($judul, $gambar, $catatan) {
        $this->judul = $judul;
        $this->gambar = $gambar;
        $this->catatan = $catatan;
    }
}

// Child Class
class Buku extends Koleksi {
    public $penulis;
    public $status; // completed, ongoing, belum
    public $rating;
    public $pdf;

    public function __construct($judul, $penulis, $status, $rating, $gambar, $catatan, $pdf = null) {
        parent::__construct($judul, $gambar, $catatan);
        $this->penulis = $penulis;
        $this->status = $status;
        $this->rating = $rating;
        $this->pdf = $pdf;
    }

    // Method untuk warna status
    public function getStatusColor() {
        switch ($this->status) {
            case 'completed': return '#d4edda'; // Hijau
            case 'ongoing': return '#fff3cd';   // Kuning
            default: return '#f8d7da';          // Merah
        }
    }
}

// --- FUNGSI BANTUAN JSON (PENGGANTI SESSION) ---

// 1. Fungsi Membaca Data dari JSON dan Mengubahnya jadi Objek Buku
function loadData() {
    if (!file_exists(DB_FILE)) {
        return [];
    }

    $jsonContent = file_get_contents(DB_FILE);
    $dataArray = json_decode($jsonContent, true);

    $listBuku = [];
    if (is_array($dataArray)) {
        foreach ($dataArray as $item) {
            // Kita harus membuat ulang Objek (Re-instantiate) agar method getStatusColor() bisa dipakai
            // Pastikan urutan parameter sesuai dengan __construct di class Buku
            $pdf = isset($item['pdf']) ? $item['pdf'] : null;
            
            $bukuObj = new Buku(
                $item['judul'],
                $item['penulis'],
                $item['status'],
                $item['rating'],
                $item['gambar'],
                $item['catatan'],
                $pdf
            );
            $listBuku[] = $bukuObj;
        }
    }
    return $listBuku;
}

// 2. Fungsi Menyimpan Data Array Objek ke JSON
function saveData($listBuku) {
    // Ubah array objek menjadi JSON yang rapi (Pretty Print)
    $jsonContent = json_encode($listBuku, JSON_PRETTY_PRINT);
    file_put_contents(DB_FILE, $jsonContent);
}
?>