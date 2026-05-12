<?php

function register_contato_api_route() {
    register_rest_route('api', '/contato', array(
        'methods'  => 'GET',
        'callback' => 'get_contato_page_data',
        'permission_callback' => '__return_true',
    ));
}

add_action('rest_api_init', 'register_contato_api_route');

// Novo endpoint POST para envio de contato
function register_contato_form_api_route() {
    register_rest_route('api', '/contato-form', array(
        'methods'  => 'POST',
        'callback' => 'handle_contato_form_submission',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'register_contato_form_api_route');

function handle_contato_form_submission($request) {
    $params = $request->get_json_params();
    $nome = isset($params['nome']) ? $params['nome'] : '';
    $email = isset($params['email']) ? $params['email'] : '';
    $mensagem = isset($params['mensagem']) ? $params['mensagem'] : '';
    $titulo = isset($params['titulo']) ? $params['titulo'] : '';

    if (empty($nome) || empty($email) || empty($mensagem)) {
        return new WP_REST_Response(['error' => 'Preencha todos os campos obrigatórios'], 400);
    }

    // Carrega PHPMailer
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
        require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
        require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Configurações do servidor SMTP Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ibelinstitutobelterra@gmail.com';
        $mail->Password = 'pesy ejov phjd hzeu';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Remetente e destinatário
        $mail->setFrom('ibelinstitutobelterra@gmail.com', 'Instituto Belterra');
        $mail->addAddress('comunicacao@institutobelterra.org');
        // $mail->addAddress('weberth@insertweb.com.br');
        $mail->addReplyTo($email, $nome);

        // Conteúdo
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Contato do Site: ' . $titulo;
        $mail->Body    = "<b>Nome:</b> {$nome}<br><b>E-mail:</b> {$email}<br><b>Título:</b> {$titulo}<br><b>Mensagem:</b><br>" . nl2br(htmlspecialchars($mensagem));
        $mail->AltBody = "Nome: {$nome}\nE-mail: {$email}\nMensagem:\n{$mensagem}";

        $mail->send();
        return new WP_REST_Response(['success' => true, 'message' => 'Mensagem enviada com sucesso!'], 200);
    } catch (Exception $e) {
        return new WP_REST_Response(['error' => 'Erro ao enviar e-mail: ' . $mail->ErrorInfo], 500);
    }
}

function get_contato_page_data($request) {
    $post_id = 130;
    $post = get_post($post_id);

    if (!$post) {
        return new WP_Error('no_page', 'Página não encontrada', array('status' => 404));
    }

    $acf_fields = function_exists('get_fields') ? get_fields($post_id) : null;

    $item = array(
        'id'       => $post->ID,
        'title'    => get_the_title($post_id),
        'slug'     => $post->post_name,
        'content'  => $post->post_content,
        'acf'      => $acf_fields ? $acf_fields : new stdClass(),
    );

    if (has_post_thumbnail($post_id)) {
        $item['featured_image'] = get_the_post_thumbnail_url($post_id, 'full');
    } else {
        $item['featured_image'] = null;
    }

    return new WP_REST_Response($item, 200);
}

function register_configs_api_route() {
    register_rest_route('api', '/configs', array(
        'methods'  => 'GET',
        'callback' => 'get_configs_page_data',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'register_configs_api_route');

function get_configs_page_data($request) {
    $post_id = 130;
    $post = get_post($post_id);

    if (!$post) {
        return new WP_Error('no_page', 'Página não encontrada', array('status' => 404));
    }

    $acf_fields = function_exists('get_fields') ? get_fields($post_id) : null;
    
    // Filter specific ACF fields
    $filtered_acf = array();
    $target_fields = array('instagram', 'facebook', 'youtube', 'linkedin');
    
    if ($acf_fields) {
        foreach ($target_fields as $field) {
            if (isset($acf_fields[$field])) {
                $filtered_acf[$field] = $acf_fields[$field];
            } else {
                 $filtered_acf[$field] = ''; // Return empty string if field not found
            }
        }
    } else {
        // If no ACF fields at all, still return the structure with empty values
        foreach ($target_fields as $field) {
            $filtered_acf[$field] = '';
        }
    }

    // Return ONLY the filtered ACF fields as requested
    return new WP_REST_Response($filtered_acf, 200);
}
