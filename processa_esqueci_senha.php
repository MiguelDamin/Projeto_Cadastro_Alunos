<?php
require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Verifica se o e-mail existe no banco
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Gera um token seguro e único
        $token = bin2hex(random_bytes(16));
        
        // Gera um hash do token para guardar no banco
        $token_hash = hash("sha256", $token);
        
        // Define um tempo de expiração (ex: 1 hora a partir de agora)
        $expiry = date("Y-m-d H:i:s", time() + 3600);

        // Atualiza o registro do usuário com o token e a data de expiração
        $sql = "UPDATE usuarios SET reset_token_hash = ?, reset_token_expires_at = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$token_hash, $expiry, $user['id']]);
        
        // Monta o link de redefinição
        $reset_link = "http://localhost/MeusProjetos/Projeto_Cadastro_Alunos/redefinir_senha.php?token=$token";

        // --- LÓGICA DE ENVIO DE E-MAIL ---
        // ATENÇÃO: A função mail() do PHP pode não funcionar no localhost sem configuração.
        // Em um projeto real, use uma biblioteca como PHPMailer com um servidor SMTP.
        $to = $email;
        $subject = "Redefinicao de Senha - Sistema Escolar";
        $message = "Clique no link a seguir para redefinir sua senha: " . $reset_link;
        $headers = "From: no-reply@sistemaescolar.com";
        
        // mail($to, $subject, $message, $headers); // Descomente em um servidor real

        // PARA TESTE NO LOCALHOST, VAMOS APENAS EXIBIR O LINK
        echo "<h1>Link de Redefinição (Apenas para teste)</h1>";
        echo "<p>Em um sistema real, este link seria enviado para o seu e-mail.</p>";
        echo "<p><a href='$reset_link'>$reset_link</a></p>";
        
        // header("Location: esqueci_senha.php?sucesso=1"); // Redireciona na versão final
        exit;

    } else {
        header("Location: esqueci_senha.php?erro=Email não encontrado.");
        exit;
    }
}