# Hacettepe Üniversitesi - Veritabanı ve Navicat Bağlantı Rehberi

Bu dosya, `yunus.hacettepe.edu.tr` üzerindeki MySQL veritabanınıza (nizameddinalyaprakdb) sorunsuzca dışarıdan bağlanabilmeniz için bugüne kadar öğrendiğimiz ve uyguladığımız tüm teknik bilgileri, şifreleri ve yöntemleri içermektedir.

> ⚠️ **GÜVENLİK UYARISI:** Bu dosya hassas şifreler içerir. Herkese açık konumlara (GitHub vb.) gönderilmemesi için `.gitignore` dosyasına eklenmiştir ve sadece sizin bilgisayarınızda (local) kalmalıdır.

---

## 🔑 Ana Veritabanı Kimlik Bilgileri

- **Sunucu / Host:** `localhost` (Hacettepe sunucusunun içinden bağlantı kurulduğu için her zaman localhost'tur).
- **Veritabanı Adı:** `nizameddinalyaprakdb`
- **Kullanıcı Adı:** `nizameddinalyaprak`
- **Şifre:** `8Gy#KuMi`
- **Port:** `3306` (Varsayılan MySQL portu)

*(Not: PHP kodlarınızda (`api.php` gibi) her zaman bu bilgileri kullanacaksınız.)*

---

## 🛠 Navicat / DBeaver ile Masaüstünden Bağlanma Yöntemi (HTTP Tunnel)

Üniversite sunucusu, siber saldırıları engellemek için dışarıdan gelen (SSH 22 veya MySQL 3306) bağlantılarına kapalıdır. Bu yüzden Navicat'te **"HTTP Tunnel"** yöntemini kullanmak zorundayız.

### Adım 1: Tünel Dosyasını Hazırlama
1. Navicat uygulamasının kurulum klasöründe (Mac için: `Uygulamalar -> Navicat (Sağ Tık: Paket İçeriğini Göster) -> Contents -> Resources`) bulunan **`ntunnel_mysql.php`** dosyasını kopyalayın.
2. Bu dosyayı FTP veya Git aracılığıyla Hacettepe sunucusundaki `public_html/` ana dizininize atın.
3. Dosyanın tarayıcıdaki konumu şu olmalıdır: `https://yunus.hacettepe.edu.tr/~nizameddin.alyaprak/ntunnel_mysql.php`

### Adım 2: Navicat Bağlantı Ayarları
Navicat'te yeni bir MySQL bağlantısı (*Connection*) oluşturun ve şu adımları izleyin:

**1. HTTP Sekmesi:**
- `Use HTTP tunnel` kutucuğunu **işaretleyin**.
- `Tunnel URL` kısmına şunu yazın:
  `https://yunus.hacettepe.edu.tr/~nizameddin.alyaprak/ntunnel_mysql.php`

**2. General (Genel) Sekmesi:**
- **Host:** `localhost`
- **Port:** `3306`
- **Username:** `nizameddinalyaprak`
- **Password:** `8Gy#KuMi`

**3. SSH Sekmesi:**
- Eğer önceden işaretlendiyse, `Use SSH tunnel` kutucuğundaki işareti **kaldırın**.

Bu ayarları yaptıktan sonra "Test Connection" butonuna bastığınızda "Connection Successful" uyarısı alacaksınız. Artık sorgularınızı, tablo oluşturma/düzenleme işlemlerinizi rahatça sürükle-bırak ile yapabilirsiniz.

---

## 💡 Bilmeniz Gereken Önemli MySQL Detayları

Navicat veya başka bir sistemden girdiğinizde 3 adet veritabanı göreceksiniz:

1. **`nizameddinalyaprakdb`**: Sizin asıl veritabanınızdır. Tüm projeleriniz, bulmaca oylamalarınız, kullanıcı bilgileriniz buradadır. Tam okuma ve yazma yetkiniz vardır.

2. **`information_schema`**: MySQL'in kendi iç kataloğudur (Sistem Günlüğü). Veritabanınızda kaç tablo var, kolon isimleri nedir, veri tipleri nedir gibi "sistemsel metadata" bilgilerini tutar. Sadece okunabilirdir (Read-only). Bu tabloları siz oluşturmadınız; MySQL, sizin kurduğunuz düzeni takip etmek için kendisi oluşturdu. Normal sitenizde bir işinize yaramaz, tamamen yazılımsal altyapıdır.

3. **`performance_schema`**: MySQL'in performans doktorudur. Sunucudaki darboğazları, en çok geciken SQL sorgularını ve bellek kullanımlarını izler. Sadece sunucu yöneticisi (root) erişebilir. Hata alırsanız sebebi "Root olmadığınız için görmenize gerek yok" mantığıdır. Endişelenecek bir durum yoktur.

Tüm projelerinizde (Bulmacalar, EVRP vb.) daima ve sadece kendi veritabanınız (`nizameddinalyaprakdb`) ile çalışacağınızı unutmayın.
