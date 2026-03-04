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
    
    // Tablo yoksa otomatik oluştur (Geliştirilmiş UX oylama sistemi için)
    $db->exec("CREATE TABLE IF NOT EXISTS bulmaca_istatistikleri (
        problem_id VARCHAR(50) PRIMARY KEY,
        basarili INT DEFAULT 0,
        basarisiz INT DEFAULT 0
    )");

} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Veritabanı bağlantı hatası: ' . $e->getMessage()]);
    exit;
}

// Sadece POST veya GET isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'GET') {
    
    // Hangi işlemi yapmak istediğimizi belirten gizli bir 'action' parametresi (Eğer yoksa varsayılan olarak form_gonder kabul ederiz)
    $action = $_REQUEST['action'] ?? 'form_gonder';

    if ($action === 'vote') {
        // Ziyaretçi oylama sistemi
        $problem_id = $_POST['problem_id'] ?? '';
        $vote = $_POST['vote'] ?? ''; // 'basarili' veya 'basarisiz'

        if(empty($problem_id) || empty($vote)) {
            echo json_encode(['success' => false, 'message' => 'Eksik parametre.']);
            exit;
        }

        try {
            // Önce bu problem DB'de kayıtlı mı kontrol et
            $stmt = $db->prepare("SELECT * FROM bulmaca_istatistikleri WHERE problem_id = ?");
            $stmt->execute([$problem_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if(!$row) {
                // Kayıt yoksa oluştur
                $insertStmt = $db->prepare("INSERT INTO bulmaca_istatistikleri (problem_id, basarili, basarisiz) VALUES (?, 0, 0)");
                $insertStmt->execute([$problem_id]);
            }

            // Oyu artır
            if ($vote === 'basarili') {
                $updateStmt = $db->prepare("UPDATE bulmaca_istatistikleri SET basarili = basarili + 1 WHERE problem_id = ?");
            } else {
                $updateStmt = $db->prepare("UPDATE bulmaca_istatistikleri SET basarisiz = basarisiz + 1 WHERE problem_id = ?");
            }
            $updateStmt->execute([$problem_id]);

            // Yeni istatistikleri çek
            $stmt->execute([$problem_id]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $total = $stats['basarili'] + $stats['basarisiz'];
            $failRate = $total > 0 ? round(($stats['basarisiz'] / $total) * 100) : 0;
            $successRate = $total > 0 ? round(($stats['basarili'] / $total) * 100) : 0;

            echo json_encode([
                'success' => true, 
                'fail_rate' => $failRate,
                'success_rate' => $successRate,
                'total_votes' => $total
            ]);
            exit;

        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Oylama kaydedilemedi: ' . $e->getMessage()]);
            exit;
        }

    } else if ($action === 'get_stats') {
        // İstatistikleri okuma sistemi
        $problem_id = $_GET['problem_id'] ?? '';
        
        try {
            $stmt = $db->prepare("SELECT * FROM bulmaca_istatistikleri WHERE problem_id = ?");
            $stmt->execute([$problem_id]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            if($stats) {
                $total = $stats['basarili'] + $stats['basarisiz'];
                $failRate = $total > 0 ? round(($stats['basarisiz'] / $total) * 100) : 0;
                $successRate = $total > 0 ? round(($stats['basarili'] / $total) * 100) : 0;
                
                echo json_encode([
                    'success' => true, 
                    'fail_rate' => $failRate,
                    'success_rate' => $successRate,
                    'total_votes' => $total
                ]);
            } else {
                echo json_encode(['success' => true, 'fail_rate' => 0, 'success_rate' => 0, 'total_votes' => 0]);
            }
            exit;

        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Okuma hatası: ' . $e->getMessage()]);
            exit;
        }

    } else {
        // VERCEL / İLETİŞİM FORMU (Mevcut yapı korundu)
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
             echo json_encode(['success' => false, 'message' => 'Dosya yükleme sadece POST kabul eder.']);
             exit;
        }

        $isim = $_POST['isim'] ?? '';
        $mesaj = $_POST['mesaj'] ?? '';
        
        $fotograf_url = '';

        if (isset($_FILES['fotograf'])) {
            if ($_FILES['fotograf']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'fotograflar/';
                
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_info = pathinfo($_FILES['fotograf']['name']);
                $ext = strtolower($file_info['extension']);
                $izin_verilenler = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($ext, $izin_verilenler)) {
                    $yeni_dosya_adi = uniqid('foto_') . '.' . $ext;
                    $hedef_yol = $upload_dir . $yeni_dosya_adi;

                    if (move_uploaded_file($_FILES['fotograf']['tmp_name'], $hedef_yol)) {
                        $fotograf_url = 'https://yunus.hacettepe.edu.tr/~nizameddin.alyaprak/' . $hedef_yol;
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Fotoğraf sunucuya kaydedilemedi.']);
                        exit;
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Geçersiz dosya formatı.']);
                    exit;
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Dosya yükleme hatası koda bak.']);
                exit;
            }
        }

        try {
            $stmt = $db->prepare("INSERT INTO kullanici_bilgileri (isim, mesaj, fotograf_url) VALUES (?, ?, ?)");
            $stmt->execute([$isim, $mesaj, $fotograf_url]);
            
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
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Bu adrese sadece POST veya GET isteği atılabilir.']);
}
?>
