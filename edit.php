<?php 
require_once 'classes.php'; 

// Load Data dari JSON
$daftar_buku = loadData();

// Ambil data berdasarkan index yang dikirim dari link 'Edit'
if (!isset($_GET['index']) || !isset($daftar_buku[$_GET['index']])) {
    header("Location: index.php");
    exit();
}

$index = $_GET['index'];
// Ambil objek buku dari array hasil loadData()
$buku = $daftar_buku[$index];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Buku</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f9; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        input, select, textarea { width: 100%; padding: 10px; margin: 5px 0 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { background: #f39c12; color: white; padding: 12px; border: none; width: 100%; border-radius: 5px; cursor: pointer; font-size: 16px; }
    </style>
</head>
<body>

<div class="container">
    <h2 style="margin-top:0;">Edit Buku</h2>
    <a href="index.php" style="text-decoration:none; color:#777;">&larr; Batal & Kembali</a>
    <hr>

    <form action="proses.php" method="POST" enctype="multipart/form-data">
        <!-- Kirim ID dan Tipe Aksi ke Proses.php -->
        <input type="hidden" name="action_type" value="edit">
        <input type="hidden" name="index_id" value="<?= $index ?>">
        
        <!-- Simpan data lama jika user tidak upload baru -->
        <input type="hidden" name="old_gambar" value="<?= $buku->gambar ?>">
        <input type="hidden" name="old_pdf" value="<?= $buku->pdf ?>">

        <label>Judul</label>
        <input type="text" name="judul" value="<?= $buku->judul ?>" required>

        <label>Penulis</label>
        <input type="text" name="penulis" value="<?= $buku->penulis ?>" required>

        <label>Status</label>
        <select name="status">
            <option value="belum" <?= $buku->status == 'belum' ? 'selected' : '' ?>>Belum Dibaca</option>
            <option value="ongoing" <?= $buku->status == 'ongoing' ? 'selected' : '' ?>>Sedang Dibaca</option>
            <option value="completed" <?= $buku->status == 'completed' ? 'selected' : '' ?>>Selesai</option>
        </select>

        <label>Rating</label>
        <select name="rating">
            <?php for($i=1; $i<=5; $i++): ?>
                <option value="<?= $i ?>" <?= $buku->rating == $i ? 'selected' : '' ?>><?= $i ?> Bintang</option>
            <?php endfor; ?>
        </select>

        <!-- Override Cover URL jika user pakai API di tempat lain (Optional, di sini manual saja) -->
        <input type="hidden" name="cover_url">

        <label>Ganti Cover (Biarkan kosong jika tetap)</label>
        <input type="file" name="cover_upload">
        <?php if($buku->gambar): ?>
            <small>Cover saat ini: <a href="<?= $buku->gambar ?>" target="_blank">Lihat</a></small><br><br>
        <?php endif; ?>

        <label>Ganti E-Book PDF (Biarkan kosong jika tetap)</label>
        <input type="file" name="pdf_upload" accept=".pdf">
        <?php if($buku->pdf): ?>
            <small>PDF saat ini: Tersedia</small><br><br>
        <?php endif; ?>

        <label>Catatan</label>
        <textarea name="catatan" rows="3"><?= $buku->catatan ?></textarea>

        <button type="submit">Update Perubahan</button>
    </form>
</div>

</body>
</html>