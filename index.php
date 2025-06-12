<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>QRIS Dinamis Generator</title>
</head>
<body>
    <form method="POST">
        <label>QRIS Statis:<br>
            <textarea name="qris" rows="6" cols="60" required></textarea>
        </label><br><br>

        <label>Nominal (dalam Rupiah):<br>
            <input type="number" name="nominal" required>
        </label><br><br>

        <button type="submit">Buat QRIS Dinamis</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $qris = trim($_POST['qris']);
        $nominal = trim($_POST['nominal']);

        // Hapus CRC lama dan ubah ke format dinamis
        $qris = substr($qris, 0, -4);
        $step1 = str_replace("010211", "010212", $qris);
        $step2 = explode("5802ID", $step1);

        if (count($step2) < 2) {
            echo "<p style='color:red;'>QRIS tidak valid atau tidak mengandung '5802ID'.</p>";
            exit;
        }

        // Tambahkan nominal ke tag 54
        $uang = "54".sprintf("%02d", strlen($nominal)).$nominal;
        $uang .= "5802ID";

        // Gabungkan ulang QRIS
        $fix = trim($step2[0]).$uang.trim($step2[1]);
        $fix .= ConvertCRC16($fix);

        // Tampilkan hasil
        echo "<h3>QRIS Dinamis:</h3>";
        echo "<textarea rows='6' cols='60'>$fix</textarea><br>";

        // Buat QR Code
        require_once 'phpqrcode/qrlib.php';
        $filename = 'qris.png';
        QRcode::png($fix, $filename, 'H', 6, 2);
        echo "<img src='$filename' alt='QRIS Dinamis'>";
    }

    // Fungsi CRC16
    function ConvertCRC16($str) {
        $crc = 0xFFFF;
        for ($c = 0; $c < strlen($str); $c++) {
            $crc ^= ord($str[$c]) << 8;
            for ($i = 0; $i < 8; $i++) {
                $crc = ($crc & 0x8000) ? ($crc << 1) ^ 0x1021 : $crc << 1;
            }
        }
        $hex = strtoupper(dechex($crc & 0xFFFF));
        return str_pad($hex, 4, '0', STR_PAD_LEFT);
    }
    ?>
</body>
</html>
