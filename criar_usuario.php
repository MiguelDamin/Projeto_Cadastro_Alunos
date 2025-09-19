<?php
// --- CONFIGURAÇÃO DO NOVO USUÁRIO ---
$nome_completo = 'Andrey Dev';
$email_login = 'andrey30r@gmail.com';
$senha_plana = 'Andrey11!'; // Use uma senha forte no seu projeto real!
$perfil = 'administrador'; 

// Gera um hash seguro para a senha
$senha_hash = password_hash($senha_plana, PASSWORD_DEFAULT);

// --- EXIBIÇÃO DA PÁGINA ---
echo "<h1>Gerador de Usuário para o Sistema</h1>";
echo "<p>Copie e execute o comando SQL abaixo para inserir um usuário na sua tabela 'usuarios'.</p>";
echo "<hr>";

// Monta a string SQL com as colunas corretas da nova tabela 'usuarios'
$sql = "INSERT INTO usuarios (nome_completo, email, senha_hash, perfil) 
        VALUES ('{$nome_completo}', '{$email_login}', '{$senha_hash}', '{$perfil}');";

echo "<h3>Comando SQL para Inserção:</h3>";
echo "<pre><code>" . htmlspecialchars($sql) . "</code></pre>";
?>