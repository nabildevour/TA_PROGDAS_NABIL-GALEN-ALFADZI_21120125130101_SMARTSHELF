<?php 
require_once 'classes.php'; 

// --- LOGIC PHP UNTUK SEARCH & FILL ---
$hasil_pencarian = [];
$pesan_api = "";

// 1. Jika User melakukan Pencarian
if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {
    $keyword = urlencode($_GET['keyword']);
    $url = "https://www.googleapis.com/books/v1/volumes?q=$keyword&maxResults=5";
    
    // Ambil data dari API Google menggunakan PHP native
    $response = @file_get_contents($url);
    
    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['items']) && count($data['items']) > 0) {
            $hasil_pencarian = $data['items'];
        } else {
            $pesan_api = "Buku tidak ditemukan.";
        }
    } else {
        $pesan_api = "Gagal mengambil data (Cek koneksi internet).";
    }
}

// 2. Siapkan Variabel Default untuk Form
$isi_judul = "";
$isi_penulis = "";
$isi_cover = "";

// 3. Jika User Mengklik "Pilih"
if (isset($_GET['fill_judul'])) {
    $isi_judul = $_GET['fill_judul'];
    $isi_penulis = isset($_GET['fill_penulis']) ? $_GET['fill_penulis'] : '';
    $isi_cover = isset($_GET['fill_cover']) ? $_GET['fill_cover'] : '';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Buku</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f9; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        input, select, textarea { width: 100%; padding: 10px; margin: 5px 0 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        /* Style khusus tombol cari agar beda dgn submit */
        .btn-cari { background: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block;}
        .btn-submit { background: #27ae60; color: white; padding: 12px; border: none; width: 100%; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        
        .api-box { background: #eaf2f8; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px dashed #3498db; }
        
        .search-result-item { display: flex; gap: 15px; background: white; border: 1px solid #ddd; padding: 10px; margin-top: 10px; border-radius: 5px; align-items: center; }
        .search-result-item img { width: 50px; height: 75px; object-fit: cover; border-radius: 3px; }
        .search-result-info { flex: 1; }
        .search-result-info h4 { margin: 0 0 5px; font-size: 16px; color: #333; }
        .search-result-info p { margin: 0; font-size: 13px; color: #666; }
        
        .btn-pilih { background: #e67e22; color: white; padding: 5px 15px; border-radius: 3px; text-decoration: none; font-size: 13px; }
        .btn-pilih:hover { background: #d35400; }
    </style>
</head>
<body>

<div class="container">
    <h2 style="margin-top:0;">Tambah Buku Baru</h2>
    <a href="index.php" style="text-decoration:none; color:#777;">&larr; Kembali ke Daftar</a>
    <hr>

    <!-- BAGIAN 1: FORM PENCARIAN GOOGLE (Method GET) -->
    <div class="api-box">
        <label><b>Cari Buku (Google Books API - PHP Mode)</b></label>
        <form action="" method="GET" style="display:flex; gap:10px; margin-bottom:0;">
            <input type="text" name="keyword" placeholder="Ketik judul buku..." value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>" required>
            <button type="submit" class="btn-cari">Cari</button>
        </form>

        <!-- Menampilkan Hasil Pencarian PHP -->
        <?php if ($pesan_api): ?>
            <p style="color:red; font-size:14px;"><?= $pesan_api ?></p>
        <?php endif; ?>

        <?php if (!empty($hasil_pencarian)): ?>
            <div style="margin-top:10px;">
                <small>Hasil Pencarian:</small>
                <?php foreach ($hasil_pencarian as $item): 
                    $info = $item['volumeInfo'];
                    $judul = isset($info['title']) ? $info['title'] : 'Tanpa Judul';
                    $penulis = isset($info['authors']) ? implode(', ', $info['authors']) : 'Unknown';
                    $img = isset($info['imageLinks']['thumbnail']) ? $info['imageLinks']['thumbnail'] : '';
                    
                    // Siapkan URL untuk tombol PILIH (Mengirim data kembali ke halaman ini via GET)
                    $link_pilih = "?fill_judul=" . urlencode($judul) . 
                                  "&fill_penulis=" . urlencode($penulis) . 
                                  "&fill_cover=" . urlencode($img);
                ?>
                    <div class="search-result-item">
                        <img src="<?= $img ?: 'https://via.placeholder.com/50' ?>" alt="cover">
                        <div class="search-result-info">
                            <h4><?= $judul ?></h4>
                            <p><?= $penulis ?></p>
                        </div>
                        <a href="<?= $link_pilih ?>" class="btn-pilih">Pilih</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- BAGIAN 2: FORM INPUT UTAMA (Method POST ke proses.php) -->
    <form action="proses.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action_type" value="create">
        
        <label>Judul</label>
        <input type="text" name="judul" value="<?= htmlspecialchars($isi_judul) ?>" required>

        <label>Penulis</label>
        <input type="text" name="penulis" value="<?= htmlspecialchars($isi_penulis) ?>" required>

        <label>Status</label>
        <select name="status">
            <option value="belum">Belum Dibaca</option>
            <option value="ongoing">Sedang Dibaca</option>
            <option value="completed">Selesai</option>
        </select>

        <label>Rating</label>
        <select name="rating">
            <option value="1">1 Bintang</option>
            <option value="2">2 Bintang</option>
            <option value="3">3 Bintang</option>
            <option value="4">4 Bintang</option>
            <option value="5">5 Bintang</option>
        </select>

        <!-- Hidden input untuk link gambar API -->
        <input type="hidden" name="cover_url" value="<?= htmlspecialchars($isi_cover) ?>">
        
        <?php if($isi_cover): ?>
            <div style="margin-bottom: 15px; background: #eee; padding: 10px; border-radius: 5px; text-align: center;">
                <p style="margin:0 0 5px 0; font-size:12px; color:green;">Cover Terpilih dari Google:</p>
                <img src="<?= $isi_cover ?>" style="height:120px; border:1px solid #ccc;">
            </div>
        <?php endif; ?>
        
        <label>Upload Cover (Manual - Jika tidak pakai API)</label>
        <input type="file" name="cover_upload">

        <label>Upload E-Book (PDF)</label>
        <input type="file" name="pdf_upload" accept=".pdf">

        <label>Catatan</label>
        <textarea name="catatan" rows="3"></textarea>

        <button type="submit" class="btn-submit">Simpan Buku</button>
    </form>
</div>

</body>
</html>