<?php
// Hata göstermeyi kapatalım ki JSON dönüşü bozulmasın
error_reporting(0);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Sadece POST metodu kabul edilir."]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$name = isset($data['name']) ? htmlspecialchars($data['name']) : '';
$phone = isset($data['phone']) ? htmlspecialchars($data['phone']) : '';
$message = isset($data['message']) ? nl2br(htmlspecialchars($data['message'])) : '';

if (!$name || !$phone || !$message) {
    http_response_code(400);
    echo json_encode(["error" => "Lütfen tüm alanları doldurun."]);
    exit();
}

// --- ŞIK MAİL TASARIMI (HTML) ---
$htmlContent = "
<div style='font-family: Arial, sans-serif; padding: 25px; max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #eaeaea; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);'>
    <div style='text-align: center; margin-bottom: 20px;'>
        <h2 style='color: #eab308; margin: 0; font-size: 24px;'>⚡ Kagosan Elektrik</h2>
        <p style='color: #666; font-size: 14px; margin-top: 5px;'>Web sitesinden yeni bir iletişim talebi geldi.</p>
    </div>
    
    <div style='background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 20px;'>
        <p style='margin: 0 0 10px 0; color: #333;'><strong>Müşteri Adı:</strong> <span style='color: #000;'>$name</span></p>
        <p style='margin: 0; color: #333;'><strong>Telefon No:</strong> <a href='tel:$phone' style='color: #3b82f6; text-decoration: none; font-weight: bold;'>$phone</a></p>
    </div>
    
    <div style='background: #ffffff; padding: 20px; border-left: 5px solid #eab308; border-radius: 4px; border: 1px solid #eee;'>
        <h4 style='margin: 0 0 10px 0; color: #333; font-size: 14px;'>Mesaj / Arıza Durumu:</h4>
        <p style='margin: 0; color: #444; line-height: 1.6; font-size: 15px;'>$message</p>
    </div>
    
    <div style='text-align: center; margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px;'>
        <p style='font-size: 12px; color: #aaa; margin: 0;'>Bu e-posta Kagosan Elektrik İletişim Formu aracılığıyla otomatik gönderilmiştir.</p>
    </div>
</div>
";

// Resend API Ayarları
$apiKey = 're_Xndf2rXq_AgCsnxnPydb6eoAp4HMfq9hM';

$payload = json_encode([
    "from" => "Kagosan Elektrik <onboarding@resend.dev>",
    "to" => ["iletisim@vexeizkaue.resend.app"], 
    "subject" => "Yeni Müşteri Talebi: $name",
    "html" => $htmlContent
]);

$ch = curl_init('https://api.resend.com/emails');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 || $httpCode === 201) {
    echo json_encode(["success" => true, "message" => "Mail başarıyla gönderildi."]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Mail gönderilemedi.", "details" => json_decode($response)]);
}
?>
