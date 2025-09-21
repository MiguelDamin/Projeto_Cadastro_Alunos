<?php
// Define o tipo de conteúdo como JSON e permite o acesso de outras origens (CORS)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Para produção, restrinja ao seu domínio: header('Access-Control-Allow-Origin: https://seusite.com');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// O browser envia uma requisição OPTIONS antes do POST para verificar as permissões de CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// --- DEPENDÊNCIAS ---
// Inclui o autoloader do Composer para carregar a biblioteca JWT
require_once __DIR__ . '/../vendor/autoload.php'; 
require_once __DIR__ . '/../conexao.php';

use Firebase\JWT\JWT;

// --- CONFIGURAÇÃO ---
// ESTA CHAVE DEVE SER SECRETA, LONGA E COMPLEXA!
// A chave agora é carregada a partir de uma variável de ambiente (definida no arquivo .env)
$secret_key = $_ENV['JWT_SECRET_KEY'];
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutos

// --- LÓGICA DO LOGIN ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Pega os dados do corpo da requisição (assumindo que o frontend enviará JSON)
    $data = json_decode(file_get_contents("php://input"));

    $email = $data->email ?? null;
    $senha = $data->senha ?? null;

    if (!$email || !$senha) {
        http_response_code(400); // Bad Request
        echo json_encode(['sucesso' => false, 'erro' => 'E-mail e senha são obrigatórios.']);
        exit;
    }

    try {
        $sql = "SELECT id, nome_completo, email, senha_hash, perfil, ativo, failed_login_attempts, last_failed_login_at FROM usuarios WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Verifica se a conta está temporariamente bloqueada
        if ($user && $user['failed_login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
            $last_attempt_time = strtotime($user['last_failed_login_at']);
            if (time() - $last_attempt_time < LOCKOUT_TIME) {
                http_response_code(429); // Too Many Requests
                echo json_encode(['sucesso' => false, 'erro' => 'Conta bloqueada por excesso de tentativas. Tente novamente mais tarde.']);
                exit;
            }
        }

        if ($user && password_verify($senha, $user['senha_hash'])) {
            if ($user['ativo'] == 1) {
                // Se o login for bem-sucedido, zera as tentativas de falha
                if ($user['failed_login_attempts'] > 0) {
                    $sql_reset = "UPDATE usuarios SET failed_login_attempts = 0, last_failed_login_at = NULL WHERE id = ?";
                    $stmt_reset = $pdo->prepare($sql_reset);
                    $stmt_reset->execute([$user['id']]);
                }

                // --- SUCESSO: GERAR O TOKEN JWT ---
                $issuedat_claim = time(); // Hora que o token foi gerado
                $expire_claim = $issuedat_claim + 7200; // Expira em 2 horas (7200 segundos)

                $payload = [
                    'iat' => $issuedat_claim,
                    'exp' => $expire_claim,
                    'data' => [ // Dados que você quer guardar no token
                        'id' => $user['id'],
                        'nome' => $user['nome_completo'],
                        'perfil' => $user['perfil']
                    ]
                ];

                $jwt = JWT::encode($payload, $secret_key, 'HS256');

                http_response_code(200); // OK
                echo json_encode([
                    'sucesso' => true,
                    'mensagem' => 'Login bem-sucedido!',
                    'token' => $jwt,
                    'usuario' => [
                        'id' => $user['id'],
                        'nome' => $user['nome_completo'],
                        'perfil' => $user['perfil']
                    ]
                ]);
                exit;

            } else {
                // Usuário inativo
                http_response_code(403); // Forbidden
                echo json_encode(['sucesso' => false, 'erro' => 'Usuário inativo. Contate o administrador.']);
                exit;
            }
        } else {
            // Se o login falhou, incrementa o contador de falhas se o usuário existir
            if ($user) {
                $sql_fail = "UPDATE usuarios SET failed_login_attempts = failed_login_attempts + 1, last_failed_login_at = NOW() WHERE id = ?";
                $stmt_fail = $pdo->prepare($sql_fail);
                $stmt_fail->execute([$user['id']]);
            }

            // Credenciais inválidas
            http_response_code(401); // Unauthorized
            echo json_encode(['sucesso' => false, 'erro' => 'E-mail ou senha inválidos.']);
            exit;
        }

    } catch (PDOException $e) {
        http_response_code(500); // Internal Server Error
        // Não exponha o erro real em produção, apenas em logs
        error_log("Erro de login: " . $e->getMessage());
        echo json_encode(['sucesso' => false, 'erro' => 'Erro no servidor ao processar o login.']);
        exit;
    }

} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['sucesso' => false, 'erro' => 'Método não permitido.']);
    exit;
}