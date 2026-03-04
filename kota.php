<?php
$dizin = "./"; // public_html klasörünü temsil eder
$toplam_alan = disk_total_space($dizin);
$bos_alan = disk_free_space($dizin);
$kullanilan_alan = $toplam_alan - $bos_alan;

// Bayt cinsinden gelen veriyi MB veya GB'a çeviren fonksiyon
function formatBoyut($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' Bayt';
    }
}

echo "<div style='font-family: Arial; margin: 50px; padding: 20px; border: 1px solid #ccc; border-radius: 10px; width: 300px;'>";
echo "<h2 style='color: #2c3e50;'>Disk Alanı Durumu</h2>";
echo "<p><b>Toplam Tanımlı Alan:</b> <span style='color: blue;'>" . formatBoyut($toplam_alan) . "</span></p>";
echo "<p><b>Şu An Kullanılan:</b> <span style='color: red;'>" . formatBoyut($kullanilan_alan) . "</span></p>";
echo "<p><b>Kalan Boş Alan:</b> <span style='color: green;'>" . formatBoyut($bos_alan) . "</span></p>";
echo "</div>";
?>
