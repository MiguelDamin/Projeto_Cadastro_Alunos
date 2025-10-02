<?php
// Script temporário para verificar e configurar perfis de usuário
require 'conexao.php';

echo "<h2>🔧 Verificando estrutura da tabela usuarios...</h2>";

try {
    // Verifica estrutura atual da tabela usuarios
    $stmt = $pdo->query("DESCRIBE usuarios");
    $colunas = $stmt->fetchAll();
    
    echo "<h3>📋 Estrutura atual da tabela usuarios:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
    
    $tem_coluna_perfil = false;
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td>{$coluna['Field']}</td>";
        echo "<td>{$coluna['Type']}</td>";
        echo "<td>{$coluna['Null']}</td>";
        echo "<td>{$coluna['Key']}</td>";
        echo "<td>{$coluna['Default']}</td>";
        echo "</tr>";
        
        if ($coluna['Field'] === 'perfil') {
            $tem_coluna_perfil = true;
        }
    }
    echo "</table>";
    
    if (!$tem_coluna_perfil) {
        echo "<p>❌ A coluna 'perfil' não existe. Criando agora...</p>";
        
        // Cria a coluna perfil
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN perfil ENUM('administrador', 'diretor', 'secretaria', 'professor', 'aluno') NOT NULL DEFAULT 'aluno'");
        echo "<p>✅ Coluna 'perfil' criada com sucesso!</p>";
    } else {
        echo "<p>✅ A coluna 'perfil' já existe!</p>";
    }
    
    // Mostra usuários atuais
    echo "<h3>👥 Usuários cadastrados:</h3>";
    $usuarios = $pdo->query("SELECT id, nome_completo, email, perfil FROM usuarios")->fetchAll();
    
    if (empty($usuarios)) {
        echo "<p>Nenhum usuário encontrado.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Perfil</th></tr>";
        foreach ($usuarios as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['nome_completo']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td><strong>{$user['perfil']}</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<br><h3>🔧 Para alterar o perfil de um usuário, execute:</h3>";
        echo "<pre><code>UPDATE usuarios SET perfil = 'professor' WHERE id = 1;</code></pre>";
        echo "<p><strong>Perfis disponíveis:</strong> administrador, diretor, secretaria, professor, aluno</p>";
    }
    
    echo "<br><p><strong>🎉 Verificação concluída! Agora você pode deletar este arquivo.</strong></p>";
    
} catch (PDOException $e) {
    echo "<p>❌ Erro: " . $e->getMessage() . "</p>";
}
?>