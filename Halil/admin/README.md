# İçerik yönetimi

Panel adresi `/admin/` (veya `/admin/login.php`). Mevcut yönetici hesapları korunur.

- **Projeler:** başlık, yıl / sıralama tarihi, kapak, künye, açıklama ve galeri yönetilir. Galeri yolları satır satır düzenlenebilir; önizlemelerin yanındaki oklarla sıralama yapılabilir ve görseller kaldırılabilir. Çoklu dosya seçimi galeriye yeni görseller ekler. Yeni projelerin detay adresi otomatik oluşturulur; mevcut adresler korunur.
- **Ödüller:** başlık, tarih, ödül / finalist türü ve isteğe bağlı proje bağlantısı yönetilir.
- **Stüdyo:** kategori olarak Ekibimiz veya Katkıda Bulunanlar seçilir. Ekip üyeleri için unvan, avatar harfleri / rengi veya fotoğraf düzenlenebilir.
- Sıralama tarihi yeniden eskiye uygulanır. Tarih boşsa yıl kullanılır; stüdyodaki tarihsiz eski kayıtlar mevcut sırasını korur. Aynı tarihli yeni kayıtlar önce görünür; mevcut kayıtlar için Sıra alanı kullanılabilir.

## Sunucu

PHP 8.1+ ve Fileinfo gerekir. Siteyi HTTP üzerinden açın; panel PHP çalıştırabilen sunucu ister. İçeriğin tek kaynağı `assets/content.json` dosyasıdır. `admin/data/users.json` yalnızca hesapları tutar; bu klasörün HTTP erişimi engellenmelidir (Apache için `.htaccess` mevcut; Nginx için eşdeğer deny kuralı gerekir).

PHP süreci `assets/content.json`, `assets/uploads/`, `admin/data/` `projects.html` dosyasına ve yeni proje HTML dosyaları oluşturmak için sitenin kök dizinine yazabilmelidir. Dosya yüklemeleri JPG, PNG ve WebP olarak kabul edilir. `admin/.user.ini` dosyası tek görsel için 20 MB, toplam istek için 128 MB ve 100 dosya sınırını tanımlar; sunucunun üst sınırları da buna uygun olmalıdır.

Yayına alırken değişen HTML/PHP/JS dosyalarını ve `assets/content.json` dosyasını birlikte yükleyin. Sonraki kod güncellemelerinde canlıdaki `assets/content.json`, `admin/data/users.json` ve `assets/uploads/` dosyalarını koruyun ve yedekleyin. Eski `admin/data/content.json` kayıtları yeni ortak dosyaya taşındı.
