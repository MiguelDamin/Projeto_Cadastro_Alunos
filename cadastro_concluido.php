<?php
// Inclui o cabeçalho (header + sidebar)
require 'templates/header.php';
require 'conexao.php';

// --- BUSCAR DADOS DO ALUNO RECÉM-CADASTRADO ---
$aluno_id = $_GET['id'] ?? null;
$aluno = null;

if ($aluno_id) {
    try {
        $sql = "SELECT 
                    a.nome_completo, 
                    a.caminho_foto, 
                    r.nome_completo AS nome_responsavel,
                    r.id AS id_responsavel
                FROM alunos a
                LEFT JOIN responsaveis r ON a.id_responsavel_principal = r.id
                WHERE a.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$aluno_id]);
        $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Em caso de erro, não quebra a página, apenas não exibe os dados.
        error_log("Erro ao buscar aluno recém-cadastrado: " . $e->getMessage());
    }
}

// --- PREPARAR CAMINHO DA FOTO ---
$foto_src = 'assets/avatar_padrao.png'; // Caminho para uma imagem padrão
if ($aluno && !empty($aluno['caminho_foto'])) {
    $foto_path = 'uploads/fotos_alunos/' . htmlspecialchars($aluno['caminho_foto']);
    if (file_exists($foto_path)) {
        $foto_src = $foto_path;
    }
}

?>

<head>
    <title>Cadastro Concluído - Sistema Escolar</title>
    <style>
        .container-concluido {
            text-align: center;
            background-color: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            max-width: 600px;
            margin: 40px auto;
        }
        .icone-sucesso {
            font-size: 5rem;
            color: #28a745;
            margin-bottom: 20px;
            animation: pop-in 0.5s ease-out;
        }
        .container-concluido h2 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 10px;
        }
        .aluno-info {
            margin-top: 30px;
            margin-bottom: 30px;
            font-size: 1.2rem;
        }
        .aluno-info img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #e9ecef;
            margin-bottom: 15px;
        }
        /* .botoes-acao {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        } */
        @keyframes pop-in { from { transform: scale(0.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    </style>
</head>

<div class="container-concluido">
    <div class="icone-sucesso"><i class="fas fa-check-circle"></i></div>
    <h2>Cadastro Concluído!</h2>
    <p>O aluno foi cadastrado no sistema com sucesso.</p>

    <?php if ($aluno): ?>
    <div class="aluno-info">
        <img src="<?php echo $foto_src; ?>" alt="Foto do Aluno">
        <div><strong><?php echo htmlspecialchars($aluno['nome_completo']); ?></strong></div>
        <?php if (!empty($aluno['nome_responsavel'])): ?>
            <div class="responsavel-concluido">
                <i class="fas fa-user-shield"></i>
                <span>Responsável: <?php echo htmlspecialchars($aluno['nome_responsavel']); ?></span>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="botoes-acao">
        <a href="painel.php" class="btn-secondary"><i class="fas fa-home"></i> Voltar ao Painel</a>
        <a href="cadastro_geral.php?reset=1" class="btn-primary"><i class="fas fa-plus-circle"></i> Novo Cadastro</a>
        <?php if (!empty($aluno['id_responsavel'])): ?>
            <a href="cadastro_geral.php?id_resp=<?php echo $aluno['id_responsavel']; ?>" class="btn-primary"><i class="fas fa-user-friends"></i> Outro Aluno (Mesmo Responsável)</a>
        <?php endif; ?>
    </div>
</div>

<?php
// Inclui o rodapé
require 'templates/footer.php';

