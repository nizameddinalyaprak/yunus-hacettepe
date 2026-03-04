<?php
// CORS Ayarları (Vercel'den veya başka domainden gelen isteklere izin vermek için çok önemlidir)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// OPTIONS (Preflight) isteği ise işlemi hemen sonlandır ve başarılı yanıt dön
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Veritabanı Bilgileri
$host = 'localhost';
$dbname = 'nizameddinalyaprakdb';
$username = 'nizameddinalyaprak';
$password = '8Gy#KuMi';

// Veritabanına Bağlan
try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Veritabanı bağlantı hatası: ' . $e->getMessage()]);
    exit;
}

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Gelen form verilerini al (React/Vercel'den gelecek olan veriler)
    $isim = $_POST['isim'] ?? '';
    $mesaj = $_POST['mesaj'] ?? '';
    // Farklı alanlarınız varsa buraya çoğaltabiliriz: $ogrenci_no = $_POST['ogrenci_no'] ?? '';
    
    $fotograf_url = '';

    // Fotoğraf yüklendiyse işlemi yap
    if (isset($_FILES['fotograf'])) {
        if ($_FILES['fotograf']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'fotograflar/';
            
            // Eğer sunucuda 'fotograflar' klasörü yoksa kod ile otomatik oluştur (izinleri 0777 yani tam yetkili yap)
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Dosyanın uzantısını al (jpg, png vb.) ve benzersiz bir dosya adı üret (çakışma olmasın diye)
            $file_info = pathinfo($_FILES['fotograf']['name']);
            $ext = strtolower($file_info['extension']);
            $izin_verilenler = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($ext, $izin_verilenler)) {
                $yeni_dosya_adi = uniqid('foto_') . '.' . $ext;
                $hedef_yol = $upload_dir . $yeni_dosya_adi;

                // Fotoğrafı geçici klasörden asıl klasöre ($hedef_yol) taşı
                if (move_uploaded_file($_FILES['fotograf']['tmp_name'], $hedef_yol)) {
                    // Başarılıysa, veritabanına yazılacak olan fotoğrafın tam Hacettepe linkini oluştur
                    $fotograf_url = 'https://yunus.hacettepe.edu.tr/~nizameddin.alyaprak/' . $hedef_yol;
                } else {
                    echo json_encode(['success' => false, 'message' => 'Fotoğraf sunucuya kaydedilemedi. Klasör izinlerini kontrol edin. Lütfen FileZilla üzerinden "fotograflar" adlı bir klasör açıp izinlerini 777 yapın.']);
                    exit;
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Sadece resim dosyalarına (.jpg, .png, .gif) izin verilir.']);
                exit;
            }
        } else {
            // PHP sunucu tabanlı dosya yükleme hataları
            $hata_kodu = $_FILES['fotograf']['error'];
            $mesaj = 'Dosya yükleme hatası. Hata kodu: ' . $hata_kodu;
            if($hata_kodu == UPLOAD_ERR_INI_SIZE) {
                $mesaj = 'Seçtiğiniz fotoğraf sunucunun kabul ettiği maksimum boyuttan daha büyük.';
            }
            echo json_encode(['success' => false, 'message' => $mesaj]);
            exit;
        }
    }

    // Verileri Veritabanı Tablosuna Kaydet (kullanicilar tablosuna)
    try {
        $stmt = $db->prepare("INSERT INTO kullanici_bilgileri (isim, mesaj, fotograf_url) VALUES (?, ?, ?)");
        $stmt->execute([$isim, $mesaj, $fotograf_url]);
        
        // Vercel'e sonucun başarılı olduğunu bildir
        echo json_encode([
            'success' => true, 
            'message' => 'Kayıt başarıyla oluşturuldu.',
            'data' => [
                'isim' => $isim,
                'mesaj' => $mesaj,
                'fotograf_url' => $fotograf_url
            ]
        ]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Veritabanı kayıt hatası: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Bu adrese sadece POST isteği atılabilir.']);
}
?>
