# İndirme Yönetim Sistemi Pro v2.1
**DLE CMS için Dış Link İndirme Takip ve Yönetim Sistemi**

---

## Özellikler

- ✅ Dış link (Yandex, Google Drive, Mega vb.) indirme yönlendirmesi
- ✅ İndirme sayacı (ana sayaç tablosu)
- ✅ Detaylı log kaydı (kullanıcı adı, IP, tarih)
- ✅ Ara sayfa — 5 saniyelik geri sayım ile otomatik yönlendirme
- ✅ Dosya şifresi gösterimi
- ✅ Alternatif link desteği (2 adet)
- ✅ Üye olmayanlara erişim engeli
- ✅ Admin panelinde istatistik sayfası (grafik, saat/gün analizi, kullanıcı bazlı)

---

## Kurulum

### 1. Plugin Kurulumu

1. `indirme-yonetim-sistemi-pro-v2.xml` dosyasını DLE admin panelinden yükleyin.
   - **Admin Panel → Eklentiler → Eklenti Yükle**
2. Eklentiyi **etkinleştirin** — bu işlem `download_stats` ve `download_log` tablolarını otomatik oluşturur.

---

### 2. download.php Dosyası

`download.php` dosyasını sitenizin **kök dizinine** (public_html) yükleyin.

> ⚠️ Bu dosya DLE'nin kendi `engine/download.php` dosyasından **farklıdır**. Kök dizine gidecek.

---

### 3. İlave Alan (xfields) Tanımlamaları

Admin panelinden aşağıdaki 4 ilave alanı oluşturun:

**Admin Panel → Ayarlar → İlave Alanlar → Yeni Alan Ekle**

| Alan Adı | Alan Tipi | Açıklama |
|---|---|---|
| `download` | Tek satır metin | Ana indirme linki **(Zorunlu)** |
| `filepass` | Tek satır metin | Dosya Şifresi |
| `download2` | Tek satır metin | Alternatif Link 1 |
| `download3` | Tek satır metin | Alternatif Link 2 |

> ⚠️ Alan adlarını **birebir** yukarıdaki gibi yazın. Büyük/küçük harf fark eder.  
> Örnek: `filepass` doğru — `file_pass`, `FilePass`, `sifre` yanlış.

---

### 4. Şablon (fullstory.tpl) Entegrasyonu

`fullstory.tpl` dosyanızda indirme butonunu aşağıdaki gibi ekleyin:

```html
[xfgiven_download]
<a href="/download.php?id={news-id}" target="_blank">
    Dosyayı İndir (İndirilme: {download-count})
</a>
[/xfgiven_download]
```

> `[xfgiven_download]` bloğu, `download` ilave alanı **dolu olan** yazılarda butonu gösterir, **boş olanlarda gizler**.  
> `{download-count}` etiketi eklenti tarafından otomatik olarak doldurulur.

---

### 5. Önbelleği Temizleme

Tüm adımları tamamladıktan sonra:

**Admin Panel → Genel Ayarlar → Önbelleği Temizle**

---

## Kullanım

### Yazı Eklerken / Düzenlerken

Yazı ekleme sayfasının alt kısmındaki ilave alanları doldurun:

| Alan | Zorunlu | Örnek |
|---|---|---|
| `download` | ✅ Evet | `https://yadi.sk/d/xxxxx` |
| `filepass` | ❌ Hayır | `dlehub.com.tr` |
| `download2` | ❌ Hayır | `https://drive.google.com/xxxxx` |
| `download3` | ❌ Hayır | `https://mega.nz/xxxxx` |

> `download2`, `download3` ve `filepass` alanları **boş bırakılırsa** ara sayfada görünmez.

---

## Ara Sayfa Önizleme

Kullanıcı **İndir** butonuna tıkladığında açılan sayfa:

```
┌─────────────────────────────────────┐
│                                     │
│         ◌ (dönen animasyon)         │
│                                     │
│      Dosyanız Hazırlanıyor          │
│  Lütfen bekleyin, yönlendiriliyors. │
│                                     │
│        5  saniye sonra başlayacak   │
│                                     │
│  ┌─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─┐  │
│  │ 🔑 Dosya Şifresi:  dlehub    │  │
│  └─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─┘  │
│                                     │
│  [ ⬇ ANA LİNK (İNDİRMEYİ BAŞLAT) ] │
│  [ 🔗 Alternatif Link 1           ] │
│  [ 🔗 Alternatif Link 2           ] │
│                                     │
│  ─────────────────────────────────  │
│           ← Geri Dön               │
└─────────────────────────────────────┘
```

**Davranış Kuralları:**

| Durum | Sonuç |
|---|---|
| Kullanıcı giriş yapmamış | "Erişim Engellendi" sayfası gösterilir |
| `filepass` alanı boş | "Bu dosyanın şifresi yok" yazısı görünür |
| `download2` boş | Alternatif Link 1 butonu çıkmaz |
| `download3` boş | Alternatif Link 2 butonu çıkmaz |
| 5 saniye geçti | Otomatik olarak ana linke yönlendirir |

---

## Admin Paneli — İstatistik Sayfası

**Admin Panel → Ana Sayfa → İndirme İstatistikleri**

### Özet Kartlar (Sayfanın Üstü)

| Kart | Açıklama |
|---|---|
| Toplam Sayaç | `download_stats` tablosundaki toplam indirme sayısı |
| Log Kaydı | `download_log` tablosundaki toplam satır sayısı |
| Takip Edilen | Kaç farklı yazının indirildiği |
| Üye İndirdi | Kaç farklı üyenin indirme yaptığı |

### Sekmeler

| Sekme | İçerik |
|---|---|
| **En Çok İndirilen** | İndirme sayısına göre sıralı ilk 50 yazı |
| **Trend Grafik** | Son 30 günlük günlük indirme grafiği (Chart.js) |
| **Saat / Gün Analizi** | Saatlik dağılım bar grafik + Haftanın günleri doughnut grafik |
| **Kullanıcı Bazlı** | En çok indiren üyeler + Son 100 indirme logu |

> 📌 Trend grafik ve saat/gün analizi `download_log` tablosundan beslenir.  
> Plugin kurulumundan **sonraki** indirmeler için veri birikmeye başlar.

---

## Güncelleme (v1.0 → v2.0+)

Eğer sistemin v1.0 sürümü kuruluysa:

1. Yeni XML dosyasını admin panelinden yükleyin.
2. **Eklentiyi Güncelle** butonuna tıklayın — `download_log` tablosu otomatik oluşturulur.
3. Kök dizindeki `download.php` dosyasını yeni sürümle değiştirin.
4. `download2`, `download3`, `filepass` ilave alanlarını (henüz yoksa) ekleyin.
5. Önbelleği temizleyin.

---

## Önemli Notlar

- **Üye Kontrolü:** Sistem, giriş yapmamış kullanıcıları otomatik olarak engeller.
- **Otomatik Yönlendirme:** Ara sayfa 5 saniye beklettikten sonra `download` alanındaki linke otomatik yönlendirir.
- **İstatistikler:** Grafik ve log verilerine Admin Paneli → İndirme İstatistikleri menüsünden ulaşabilirsiniz.
- **Log Temizleme:** `download_log` tablosu zamanla büyür. Gerekirse eski kayıtları temizleyebilirsiniz:
  ```sql
  DELETE FROM dle_download_log WHERE downloaded_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
  ```

---

## Teknik Gereksinimler

- PHP 7.4+
- DLE 17+
- MySQL 5.7+ / MariaDB 10.2+

---

## Dosya Listesi

| Dosya | Konum | Açıklama |
|---|---|---|
| `download.php` | `/` (kök dizin) | Ana indirme yönlendirme ve ara sayfa |
| `indirme-yonetim-sistemi-pro-v2.xml` | — | DLE eklenti paketi |
| `engine/inc/download_stats.php` | Plugin tarafından oluşturulur | Admin istatistik sayfası |
| `engine/modules/tracker.php` | Plugin tarafından oluşturulur | Sayaç fonksiyonu |

---

*DLEHub.com.tr — DLE CMS Türkiye*
