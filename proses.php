<?php
require_once 'classes.php'; // Panggil file class & helper functions

// Pastikan folder upload ada
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// Muat data yang ada di JSON saat ini
$daftar_buku = loadData(); 

// --- LOGIC HAPUS (DELETE) ---
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $index = $_GET['index'];
    
    if (isset($daftar_buku[$index])) {
        // Hapus data array
        array_splice($daftar_buku, $index, 1);
        
        // SIMPAN PERUBAHAN KE JSON
        saveData($daftar_buku);
    }
    
    // Redirect kembali ke index
    header("Location: index.php");
    exit();
}

// --- LOGIC SIMPAN / UPDATE (POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Ambil data dari form
    $judul = htmlspecialchars($_POST['judul']);
    $penulis = htmlspecialchars($_POST['penulis']);
    
    // --- VALIDASI PENULIS (TIDAK BOLEH ANGKA) ---
    if (preg_match('/[0-9]/', $penulis)) {
        echo "<script>
            alert('Gagal! Nama Penulis tidak boleh mengandung angka (0-9). Hanya huruf yang diperbolehkan.');
            window.history.back(); 
        </script>";
        exit(); // Stop script PHP agar data tidak tersimpan
    }
    // ---------------------------------------------

    $status = $_POST['status'];
    $rating = $_POST['rating'];
    $catatan = htmlspecialchars($_POST['catatan']);
    
    // 1. Handle Gambar
    $gambar = "https://via.placeholder.com/150?text=No+Cover";
    
    // Cek apakah pakai gambar lama (saat edit)
    if (isset($_POST['old_gambar']) && !empty($_POST['old_gambar'])) {
        $gambar = $_POST['old_gambar'];
    }
    // Cek apakah ada link dari Google Books
    if (!empty($_POST['cover_url'])) {
        $gambar = $_POST['cover_url'];
    }
    // Cek Upload Manual
    if (isset($_FILES['cover_upload']) && $_FILES['cover_upload']['error'] == 0) {
        $target_file = "uploads/" . time() . "_img_" . basename($_FILES["cover_upload"]["name"]);
        if (move_uploaded_file($_FILES["cover_upload"]["tmp_name"], $target_file)) {
            $gambar = $target_file;
        }
    }

    // 2. Handle PDF
    $pdf = null;
    if (isset($_POST['old_pdf'])) $pdf = $_POST['old_pdf'];
    
    if (isset($_FILES['pdf_upload']) && $_FILES['pdf_upload']['error'] == 0) {
        $ext = pathinfo($_FILES["pdf_upload"]["name"], PATHINFO_EXTENSION);
        if (strtolower($ext) == 'pdf') {
            $target_pdf = "uploads/" . time() . "_book_" . basename($_FILES["pdf_upload"]["name"]);
            if (move_uploaded_file($_FILES["pdf_upload"]["tmp_name"], $target_pdf)) {
                $pdf = $target_pdf;
            }
        }
    }

    // 3. Simpan ke Array & JSON
    if ($_POST['action_type'] == 'edit') {
        // --- UPDATE ---
        $idx = $_POST['index_id'];
        
        if (isset($daftar_buku[$idx])) {
            $bukuEdit = $daftar_buku[$idx];
            
            // Update property
            $bukuEdit->judul = $judul;
            $bukuEdit->penulis = $penulis;
            $bukuEdit->status = $status;
            $bukuEdit->rating = $rating;
            $bukuEdit->catatan = $catatan;
            $bukuEdit->gambar = $gambar;
            $bukuEdit->pdf = $pdf;

            $daftar_buku[$idx] = $bukuEdit;
        }

    } else {
        // --- CREATE BARU ---
        $bukuBaru = new Buku($judul, $penulis, $status, $rating, $gambar, $catatan, $pdf);
        $daftar_buku[] = $bukuBaru;
    }

    // SIMPAN SEMUA PERUBAHAN KE FILE JSON
    saveData($daftar_buku);

    // Selesai, kembalikan ke index
    header("Location: index.php");
    exit();
}
?>