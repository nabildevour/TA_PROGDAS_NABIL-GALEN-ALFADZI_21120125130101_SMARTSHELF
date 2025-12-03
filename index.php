<?php
require_once 'classes.php'; // Wajib include

// LOAD DATA DARI JSON (Bukan Session lagi)
$daftar_buku = loadData();

$filterStatus = isset($_GET['filter']) ? $_GET['filter'] : 'all';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koleksi Buku (JSON Storage)</title>
    <!-- CSS Internal sederhana agar file tidak terlalu banyak -->
    <style>
        body { font-family: sans-serif; background: #f4f4f9; padding: 20px; }
        .navbar { display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .btn { padding: 8px 15px; border-radius: 5px; text-decoration: none; color: white; font-size: 14px; }
        .btn-add { background: #4a90e2; }
        .btn-filter { background: white; color: #333; border: 1px solid #ddd; margin-right: 5px; }
        .btn-filter.active { background: #4a90e2; color: white; border-color: #4a90e2; }
        
        /* Grid Layout */
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
        .card { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .card img { width: 100%; height: 250px; object-fit: cover; }
        .card-body { padding: 15px; }
        .badge { padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .actions { margin-top: 10px; display: flex; gap: 5px; }
        .btn-edit { background: #f1c40f; color: #333; flex: 1; text-align: center; }
        .btn-del { background: #e74c3c; flex: 1; text-align: center; }
        .btn-dl { background: #34495e; display: block; text-align: center; margin-top: 5px; }
        
        /* Style Tambahan untuk Catatan */
        .catatan-box {
            background: #fff8e1;
            border: 1px dashed #f39c12;
            padding: 8px;
            margin: 10px 0;
            border-radius: 5px;
            font-size: 12px;
            color: #555;
            font-style: italic;
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <div class="navbar">
        <h2 style="margin:0; color:#4a90e2;">📚 Pustaka JSON</h2>
        <div>
            <!-- Karena tidak pakai logout button di req sebelumnya, cuma tombol tambah saja -->
            <a href="tambah.php" class="btn btn-add">+ Tambah Buku</a>
        </div>
    </div>

    <!-- FILTER -->
    <div style="margin-bottom: 20px;">
        <a href="index.php?filter=all" class="btn btn-filter <?= $filterStatus == 'all' ? 'active' : '' ?>">Semua</a>
        <a href="index.php?filter=belum" class="btn btn-filter <?= $filterStatus == 'belum' ? 'active' : '' ?>">Belum</a>
        <a href="index.php?filter=ongoing" class="btn btn-filter <?= $filterStatus == 'ongoing' ? 'active' : '' ?>">Sedang Baca</a>
        <a href="index.php?filter=completed" class="btn btn-filter <?= $filterStatus == 'completed' ? 'active' : '' ?>">Selesai</a>
    </div>

    <!-- LIST BUKU -->
    <div class="grid">
        <?php
        $adaData = false;
        if (!empty($daftar_buku)) {
            foreach ($daftar_buku as $key => $buku) {
                // Filter Logic
                if ($filterStatus != 'all' && $buku->status != $filterStatus) continue;
                $adaData = true;

                // Cek gambar URL vs Lokal
                $img = (strpos($buku->gambar, 'http') === 0) ? $buku->gambar : $buku->gambar;
        ?>
            <div class="card">
                <img src="<?= $img ?>" onerror="this.src='https://via.placeholder.com/150'">
                <div class="card-body">
                    <span class="badge" style="background: <?= $buku->getStatusColor() ?>"><?= $buku->status ?></span>
                    <h4 style="margin: 10px 0 5px;"><?= $buku->judul ?></h4>
                    <p style="margin:0; font-size: 13px; color:#666;"><?= $buku->penulis ?></p>
                    <div style="color: gold; margin: 5px 0;">
                        <?= str_repeat("★", $buku->rating) . str_repeat("☆", 5 - $buku->rating) ?>
                    </div>
                    
                    <!-- BAGIAN BARU: MENAMPILKAN CATATAN -->
                    <?php if (!empty($buku->catatan)): ?>
                        <div class="catatan-box">
                            "<?= nl2br(htmlspecialchars($buku->catatan)) ?>"
                        </div>
                    <?php endif; ?>
                    <!-- END BAGIAN CATATAN -->

                    <?php if($buku->pdf && file_exists($buku->pdf)): ?>
                        <a href="<?= $buku->pdf ?>" class="btn btn-dl" target="_blank">Akses PDF</a>
                    <?php endif; ?>

                    <div class="actions">
                        <!-- Menggunakan index array sebagai ID edit/delete -->
                        <a href="edit.php?index=<?= $key ?>" class="btn btn-edit">Edit</a>
                        <a href="proses.php?action=delete&index=<?= $key ?>" class="btn btn-del" onclick="return confirm('Hapus?')">Hapus</a>
                    </div>
                </div>
            </div>
        <?php 
            }
        } 
        
        if (!$adaData) echo "<p style='color:grey;'>Tidak ada buku yang ditampilkan.</p>";
        ?>
    </div>

</body>
</html>