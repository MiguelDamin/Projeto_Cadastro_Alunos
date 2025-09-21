<?php
// Define o tempo de vida do cookie da sessão para 2 horas
$tempo_limite_sessao = 7200;
session_set_cookie_params($tempo_limite_sessao);

// 1. Inicia a sessão
session_start();

// 2. Limpa todas as variáveis da sessão
session_unset();

// 3. Destrói a sessão
session_destroy();

// 4. Redireciona o usuário para a página de login
header("Location: login.php");
exit();
?>