<?php

// Inclui o arquivo de conexão com o banco de dados
require_once 'conexao.php'; // Certifique-se de que o caminho para conexao.php está correto

// Inicia a sessão (necessário se você usar sessão para id do usuário, etc.)
if (session_status() === PHP_SESSION_NONE) {
    // Define o tempo de vida do cookie da sessão para 2 horas
    $tempo_limite_sessao = 7200;
    session_set_cookie_params($tempo_limite_sessao);

    session_start();
}

// --- BUSCAR O ÚLTIMO ALUNO CADASTRADO (PARA TESTE) ---
try {
    // Seleciona o aluno com o maior ID (presumindo que seja o mais recente)
    $stmt = $pdo->prepare("SELECT nome_completo, caminho_foto FROM alunos ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $aluno = $stmt->fetch(PDO::FETCH_ASSOC); // Busca os dados como um array associativo

} catch (PDOException $e) {
    die("Erro ao buscar aluno: " . $e->getMessage());
}

// --- PREPARAR CAMINHO DA FOTO ---
$foto_src = 'caminho/para/avatar_padrao.png'; // Caminho para uma imagem padrão caso não tenha foto
if ($aluno && !empty($aluno['caminho_foto'])) {
    // Se o aluno tem uma foto, constrói o caminho completo
    $foto_src = 'uploads/fotos_alunos/' . htmlspecialchars($aluno['caminho_foto']);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Foto do Aluno</title>
    <link rel="stylesheet" href="style.css"> <style>
        /* Estilos específicos para este teste, você pode adicioná-los no seu style.css principal */
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
            color: #333;
        }
        .container-teste {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 50px auto;
            text-align: center;
        }
        h1 {
            color: #0056b3;
            margin-bottom: 30px;
        }
        .aluno-card {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            padding: 20px;
            border: 1px solid #eee;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .foto-aluno-redonda {
            width: 80px;         /* Tamanho da moldura */
            height: 80px;        /* Tamanho da moldura */
            border-radius: 50%;  /* DEIXA A IMAGEM REDONDA! */
            object-fit: cover;   /* Garante que a imagem preencha a moldura sem distorcer */
            border: 3px solid #007bff; /* Borda colorida */
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5); /* Sombra para destacar */
        }
        .nome-aluno {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
        }
        .no-photo-message {
            color: #888;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container-teste">
        <h1>Teste de Exibição de Foto do Aluno</h1>

        <?php if ($aluno): ?>
            <div class="aluno-card">
                <img src="<?php echo $foto_src; ?>" alt="Foto de <?php echo htmlspecialchars($aluno['nome_completo']); ?>" class="foto-aluno-redonda">
                <span class="nome-aluno"><?php echo htmlspecialchars($aluno['nome_completo']); ?></span>
            </div>
        <?php else: ?>
            <p class="no-photo-message">Nenhum aluno encontrado ou foto não cadastrada.</p>
        <?php endif; ?>

        <p style="margin-top: 40px;"><small>Este é um arquivo de teste e pode ser removido após a verificação.</small></p>
    </div>
</body>
</html>