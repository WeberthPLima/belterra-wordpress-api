<?php
// endpoint-form.php
// Endpoint simples para receber dados do formulário e enviar por e-mail via SMTP Gmail

// Permitir CORS para testes (ajuste para produção)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// Recebe os dados do formulário (espera JSON)
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados inválidos']);
    exit;
}

// Dados do formulário
$nome = isset($input['nome']) ? $input['nome'] : '';
$email = isset($input['email']) ? $input['email'] : '';
$mensagem = isset($input['mensagem']) ? $input['mensagem'] : '';

if (empty($nome) || empty($email) || empty($mensagem)) {
    http_response_code(400);
    echo json_encode(['error' => 'Preencha todos os campos obrigatórios']);
    exit;
}

// Carrega PHPMailer
require_once __DIR__ . '/wp-includes/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/wp-includes/PHPMailer/SMTP.php';
require_once __DIR__ . '/wp-includes/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Configurações do servidor SMTP Gmail
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'ibelinstitutobelterra@gmail.com';
    $mail->Password = 'k6#eW?lTznVO?[Qu:';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Remetente e destinatário
    // $mail->setFrom('ibelinstitutobelterra@gmail.com', 'Instituto Belterra');
    $mail->setFrom('weberth@insertweb.com.br', 'Instituto Belterra');
    // $mail->addAddress('comunicacao@institutobelterra.org');
    $mail->addReplyTo($email, $nome);

    // Conteúdo
    $mail->isHTML(true);
    $mail->Subject = 'Novo contato do formulário';
    $mail->Body    = "<b>Nome:</b> {$nome}<br><b>E-mail:</b> {$email}<br><b>Mensagem:</b><br>" . nl2br(htmlspecialchars($mensagem));
    $mail->AltBody = "Nome: {$nome}\nE-mail: {$email}\nMensagem:\n{$mensagem}";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Mensagem enviada com sucesso!']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao enviar e-mail: ' . $mail->ErrorInfo]);
}
