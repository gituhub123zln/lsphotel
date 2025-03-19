<?php
// memeriksa apakah parameter indexarray ada di URL (misalnya, ?indexarray=1). Jika ada, maka nilai dari indexarray disimpan dalam variabel $id
$id = isset($_GET['indexarray']) ? $_GET['indexarray'] : 0; // Default ke 0 jika tidak ada

// Daftar kamar hotel beserta harga sewanya per hari
$kamar = array(
    ["Standar", 500000, "standar.jpg"],
    ["deluxe", 750000, "deluxe.jpg"],
    ["family", 1000000, "family.jpg"],
);


//  Mengecek apakah ada input kamar yang dikirimkan lewat form (dengan metode POST). Jika ada, maka akan dipilih kamar yang dipilih oleh pengguna.
//  Jika tidak ada, maka nilai default dari array $kamar[$id][0]
$pilih_kamar = isset($_POST['kamar']) ? $_POST['kamar'] : $kamar[$id][0]; // Pastikan 'gedung' ada dalam POST

// Mendapatkan harga gedung yang dipilih menggunakan array_column(array asosiatif) untuk memetakan nama ke harga, harga gedung disimpan dlm $pilih_gedung
$pilih_harga = array_column($kamar, 1, 0)[$pilih_kamar] ?? 0; // Tambahkan pengecekan jika $pilih_gedung tidak valid

// Mengecek apakah opsi breakfest dipilih oleh pengguna
$breakfest = isset($_POST['breakfest']);

// Mengambil durasi sewa dari input pengguna, default kosong jika tidak ada
$durasi = isset($_POST['durasi']) ? $_POST['durasi'] : ''; // Gunakan null coalescing operator untuk durasi

// Inisialisasi total pembayaran
$total_bayar = 0;

// Array untuk menyimpan pesan error validasi, jika ada yang salah 
$errors = [];

// Mengecek apakah form telah dikirim (dengan metode POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi: Durasi harus angka dan lebih dari 0
    if (!is_numeric($durasi)) {
        $errors[] = "Durasi harus berupa angka";
    }

    if (!is_numeric($_POST['identitas'])) {
        $errors[] = "Identitas harus berupa angka";
    }

    // Validasi: Nomor identitas harus 16 digit angka
    if (strlen($_POST['identitas']) !== 16) {
        $errors[] = "isian salah... Nomor Identitas harus 16 digit angka.";
    }

    // Jika tidak ada error validasi, hitung total pembayaran
    if (empty($errors)) {
        // Menghitung biaya sewa kamar total berdasarkan durasi
        $total_harga_kamar = $pilih_harga * $durasi;

        // Memberikan diskon 10% jika durasi sewa 3 hari atau lebih
        $diskon = ($durasi >= 3) ? 0.1 * $total_harga_kamar : 0;

        // Menghitung biaya tambahan untuk breakfest jika dipilih (Rp 80.000 per hari)
        $biaya_breakfest = $breakfest ? 80000 * $durasi : 0;

        // Menghitung total pembayaran setelah dikurangi diskon dan ditambah biaya breakfest
        $total_bayar = $total_harga_kamar - $diskon + $biaya_breakfest;
    }

    // Jika tombol "Simpan" ditekan, tampilkan alert dan redirect
    if (isset($_POST['simpan'])) {
        $nama = $_POST['nama']; // Mengambil input nama dari form
        $identitas = $_POST['identitas']; // Mengambil input nomor identitas dari form
        $gender = $_POST['gender']; // Mengambil input jenis kelamin dari form
        $kamar = $_POST['kamar']; // Mengambil input jenis kamar yang dipilih dari form
        $check = $breakfest ? 'Ya' : 'Tidak'; // Mengecek apakah pengguna memilih opsi breakfest

        // Membuat array untuk menyimpan detail pesanan
        // Array Asosiatif 
        $pesanan = [
            "Nama" => $nama,
            "Nomor Identitas" => $identitas,
            "Jenis Kelamin" => $gender,
            "Jenis kamar" => $kamar,
            "Breakfest" => $check,
            "Durasi" => $durasi,
            "Diskon" => $diskon,
            "Total Bayar" => number_format($total_bayar, 0, ',', '.') // Format angka untuk tampilan lebih rapi
        ];

        // Menyimpan pesanan ke dalam sesi agar data tetap tersimpan
        $_SESSION['pesanan'][] = $pesanan;

        // Membuat string detail pesanan untuk ditampilkan dalam alert
        $detail_pesanan = "Pesanan Berhasil!\n\n";
        foreach ($pesanan as $key => $value) { // Looping untuk menyusun detail pesanan
            $detail_pesanan .= "$key: $value\n"; // Menambahkan setiap item pesanan ke dalam string
        }

        // Menampilkan alert dengan detail pesanan dan mengarahkan kembali ke halaman utama
        echo "<script>
            alert(`$detail_pesanan`);
            window.location.href = 'index.php';
        </script>";
        exit(); // Menghentikan eksekusi kode setelah redirect
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form Pemesanan Kamar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-primary text-white text-center">
                <h5>Form Pemesanan Kamar</h5>
            </div>
            <div class="card-body">
                <!-- Menampilkan error jika ada -->
                <?php if ($errors) { ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error) { ?>
                                <li><?= $error ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>

                <!-- Form Pemesanan -->
                <form method="POST">
                    <!-- Input Nama Pemesan -->
                    <input type="text" class="form-control mb-3" name="nama" placeholder="Nama Pemesan" value="<?= $_POST['nama'] ?? '' ?>" required>

                    <!-- Input Jenis Kelamin -->
                    <div class="mb-3">
                        <label class="form-label">Jenis Kelamin</label><br>
                        <input class="form-check-input" type="radio" name="gender" value="Laki-laki" <?= ($_POST['gender'] ?? '') === 'Laki-laki' ? 'checked' : '' ?>> Laki-laki
                        <input class="form-check-input ms-3" type="radio" name="gender" value="Perempuan" <?= ($_POST['gender'] ?? '') === 'Perempuan' ? 'checked' : '' ?>> Perempuan
                    </div>

                    <!-- Input Nomor Identitas -->
                    <input type="text" class="form-control mb-3" name="identitas" placeholder="Nomor Identitas (16 digit)" value="<?= $_POST['identitas'] ?? '' ?>" required>

                    <!-- Dropdown Pilihan Kamar-->
                    <select class="form-select mb-3" name="kamar" onchange="this.form.submit()">
                        <?php foreach ($kamar as $indexarray => $nilai) { ?>
                            <option value="<?= $nilai[0] ?>" <?= ($nilai[0] === $pilih_kamar) ? 'selected' : '' ?>>
                                <?= $nilai[0] ?>
                            </option>
                        <?php } ?>
                    </select>

                    <!-- Input Harga Kamar (Readonly) -->
                    <input type="text" class="form-control mb-3" name="harga" value="<?= number_format($pilih_harga, 0, ',', '.') ?>" readonly>

                    <!-- Input Tanggal Sewa -->
                    <input type="date" class="form-control mb-3" name="tanggal" value="<?= $_POST['tanggal'] ?? '' ?>" required>

                    <!-- Input Durasi Sewa -->
                    <input type="number" class="form-control mb-3" name="durasi" placeholder="Durasi Menginap (hari)" value="<?= $durasi ?>" required>

                    <!-- Checkbox untuk memilih Catering -->
                    <div class="mb-3">
                        <input class="form-check-input" type="checkbox" name="breakfest" <?= $breakfest ? 'checked' : '' ?>> Termasuk Breakfest (Rp 80.000/hari)
                    </div>

                    <!-- Menampilkan Total Bayar -->
                    <input type="text" class="form-control mb-3" id="total" value="<?= $total_bayar ? number_format($total_bayar, 0, ',', '.') : '' ?>" placeholder="Total Bayar" readonly>

                    <!-- Tombol untuk menghitung total -->
                    <button type="submit" class="btn btn-primary">Hitung Total</button>

                    <!-- Tombol Simpan -->
                    <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>

                    <!-- Tombol Reset -->
                    <button type="reset" class="btn btn-danger">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>