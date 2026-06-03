<?php
// Gelen isteklerin içeriğini JSON olarak ayarlıyoruz ve dışarıdan (başka domainlerden) gelen isteklere izin veriyoruz
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Sitedeki JavaScript'ten gelen JSON verisini alıyoruz
$data = json_decode(file_get_contents("php://input"), true);

// Verileri değişkenlere aktarıyoruz
$name = isset($data['name']) ? trim($data['name']) : '';
$phone = isset($data['phone']) ? trim($data['phone']) : '';
$message_body = isset($data['message']) ? trim($data['message']) : '';

// Boş alan kontrolü
if(empty($name) || empty($phone) || empty($message_body)) {
    echo json_encode(["success" => false, "message" => "Lütfen tüm zorunlu alanları doldurun."]);
    exit;
}

// ==========================================
// DİKKAT: BURAYA KENDİ BİLGİLERİNİ GİR
// ==========================================

// 1. Resend.com üzerinden aldığın API Anahtarı (API Key)
$api_key = 're_b4ndpqLY_EsaMY8KU7uX994R8svoqwHFq'; 

// 2. Sana mail atıldığında bu mail kime gitsin istiyorsun? Kendi gmail adresini yazabilirsin.
$to_email = 'komeditv66@gmail.com'; 

// ==========================================


// Mailin tasarımı (Gmail'inde sana bu şekilde gözükecek)
$date = date('d.m.Y - H:i');
$html_content = "
    <div style='max-width: 600px; margin: 0 auto; font-family: Segoe UI, Arial, sans-serif;'>
        
        <!-- Üst Banner -->
        <div style='background: linear-gradient(135deg, #1a1a2e, #16213e); padding: 30px; border-radius: 16px 16px 0 0; text-align: center;'>
            <h1 style='color: #eab308; font-size: 24px; margin: 0 0 5px 0;'>⚡ Mamak Elektrik</h1>
            <p style='color: #94a3b8; font-size: 14px; margin: 0;'>Web Sitesinden Yeni Müşteri Mesajı</p>
        </div>

        <!-- İçerik -->
        <div style='background: #ffffff; padding: 30px; border-left: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb;'>
            
            <!-- Bilgi Kartları -->
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <td style='padding: 15px; background: #f8fafc; border-radius: 10px; border: 1px solid #e5e7eb; vertical-align: top;'>
                        <p style='color: #64748b; font-size: 12px; margin: 0 0 4px 0; text-transform: uppercase; letter-spacing: 1px;'>Müşteri Adı</p>
                        <p style='color: #1e293b; font-size: 18px; font-weight: 700; margin: 0;'>{$name}</p>
                    </td>
                </tr>
                <tr><td style='padding: 6px;'></td></tr>
                <tr>
                    <td style='padding: 15px; background: #f8fafc; border-radius: 10px; border: 1px solid #e5e7eb; vertical-align: top;'>
                        <p style='color: #64748b; font-size: 12px; margin: 0 0 4px 0; text-transform: uppercase; letter-spacing: 1px;'>Telefon Numarası</p>
                        <p style='margin: 0;'><a href='tel:{$phone}' style='color: #2563eb; font-size: 18px; font-weight: 700; text-decoration: none;'>{$phone}</a></p>
                    </td>
                </tr>
            </table>

            <!-- Mesaj Kutusu -->
            <div style='margin-top: 20px; padding: 20px; background: #fffbeb; border-left: 4px solid #eab308; border-radius: 0 10px 10px 0;'>
                <p style='color: #92400e; font-size: 12px; margin: 0 0 8px 0; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;'>Müşteri Mesajı / Arıza Durumu</p>
                <p style='color: #1e293b; font-size: 15px; line-height: 1.6; margin: 0;'>" . nl2br(htmlspecialchars($message_body)) . "</p>
            </div>

            <!-- Hızlı Aksiyonlar -->
            <div style='margin-top: 25px; text-align: center;'>
                <a href='tel:{$phone}' style='display: inline-block; padding: 12px 30px; background: #2563eb; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; margin-right: 10px;'>📞 Hemen Ara</a>
                <a href='https://wa.me/9{$phone}' style='display: inline-block; padding: 12px 30px; background: #25D366; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px;'>💬 WhatsApp Yaz</a>
            </div>
        </div>

        <!-- Alt Bilgi -->
        <div style='background: #f1f5f9; padding: 20px; border-radius: 0 0 16px 16px; text-align: center; border: 1px solid #e5e7eb; border-top: none;'>
            <p style='color: #94a3b8; font-size: 12px; margin: 0;'>Bu mesaj mamakelektrik.com iletişim formu üzerinden gönderildi.</p>
            <p style='color: #cbd5e1; font-size: 11px; margin: 5px 0 0 0;'>{$date}</p>
        </div>
    </div>
";

// Resend API'sine gönderilecek paket
$post_data = json_encode([
    // Resend'e kayıt olurken domain doğrulamadıysan varsayılan olarak bu 'onboarding' adresi zorunludur.
    // Domainini resend'e bağlarsan "iletisim@mamakelektrik.com" yapabilirsin.
    "from" => "Acil Elektrik <onboarding@resend.dev>",
    "to" => [$to_email],
    "subject" => "Yeni Müşteri Mesajı: " . $name,
    "html" => $html_content
]);

// cURL ile Resend sunucularına bağlantı kuruyoruz
$ch = curl_init('https://api.resend.com/emails');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $api_key,
    'Content-Type: application/json'
]);

// İsteği çalıştır ve cevabı al
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Resend'den dönen HTTP koduna göre (200 veya 201 başarılı demektir)
if ($httpcode == 200 || $httpcode == 201) {
    echo json_encode(["success" => true, "message" => "Mail başarıyla gönderildi."]);
} else {
    echo json_encode(["success" => false, "error" => $response, "message" => "Mail gönderilirken sistemsel bir hata oluştu."]);
}
?>
