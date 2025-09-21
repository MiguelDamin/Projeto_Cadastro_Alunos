<?php 
require 'templates/header.php'; 
require 'conexao.php';

// Processar cadastro de nova turma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_turma'])) {
    $nome_turma = $_POST['nome_turma'];
    $ano_letivo = $_POST['ano_letivo'];
    $periodo = $_POST['periodo'];
    
    try {
        // Usando apenas os campos que existem na sua tabela
        $sql = "INSERT INTO turmas (nome_turma, ano_letivo, periodo) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome_turma, $ano_letivo, $periodo]);
        
        $sucesso = "Turma cadastrada com sucesso!";
    } catch (PDOException $e) {
        $erro = "Erro ao cadastrar: " . $e->getMessage();
    }
}

// Buscar turmas existentes - adaptado para sua estrutura
try {
    // Primeiro vamos tentar buscar só da tabela turmas
    $turmas = $pdo->query("SELECT * FROM turmas ORDER BY ano_letivo DESC, nome_turma ASC")->fetchAll();
} catch (PDOException $e) {
    $turmas = [];
    $erro = "Erro ao carregar turmas: " . $e->getMessage();
}
?>

<h2>Cadastrar Turma</h2>

<?php if (isset($sucesso)): ?>
    <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
        ✅ <?php echo $sucesso; ?>
    </div>
<?php endif; ?>

<?php if (isset($erro)): ?>
    <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
        ❌ <?php echo $erro; ?>
    </div>
<?php endif; ?>

<!-- Formulário -->
<form method="POST" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
    <h3>Nova Turma</h3>
    
    <div class="form-row">
        <div class="form-group">
            <label for="nome_turma">Nome da Turma:</label>
            <select name="nome_turma" id="nome_turma" required>
                <option value="">Selecione...</option>
                <option value="1° Ano A">1° Ano A</option>
                <option value="1° Ano B">1° Ano B</option>
                <option value="1° Ano C">1° Ano C</option>
                <option value="2° Ano A">2° Ano A</option>
                <option value="2° Ano B">2° Ano B</option>
                <option value="2° Ano C">2° Ano C</option>
                <option value="3° Ano A">3° Ano A</option>
                <option value="3° Ano B">3° Ano B</option>
                <option value="3° Ano C">3° Ano C</option>
            </select>
        </div>
        <div class="form-group">
            <label for="ano_letivo">Ano Letivo:</label>
            <select name="ano_letivo" id="ano_letivo" required>
                <option value="">Selecione...</option>
                <option value="2024">2024</option>
                <option value="2025" selected>2025</option>
                <option value="2026">2026</option>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="periodo">Período:</label>
            <select name="periodo" id="periodo" required>
                <option value="">Selecione...</option>
                <option value="Matutino">Matutino</option>
                <option value="Vespertino">Vespertino</option>
                <option value="Noturno">Noturno</option>
                <option value="Integral">Integral</option>
            </select>
        </div>
    </div>

    <button type="submit" name="cadastrar_turma">Cadastrar Turma</button>
</form>

<!-- Lista de Turmas -->
<h3>Turmas Cadastradas</h3>

<?php if (empty($turmas)): ?>
    <p style="text-align: center; color: #666; padding: 20px;">
        Nenhuma turma cadastrada ainda.
    </p>
<?php else: ?>
    <table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
        <thead>
            <tr style="background: #007bff; color: white;">
                <th style="padding: 12px; border: 1px solid #ddd;">ID</th>
                <th style="padding: 12px; border: 1px solid #ddd;">Nome da Turma</th>
                <th style="padding: 12px; border: 1px solid #ddd;">Ano Letivo</th>
                <th style="padding: 12px; border: 1px solid #ddd;">Período</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($turmas as $turma): ?>
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">
                        <?php echo $turma['id']; ?>
                    </td>
                    <td style="padding: 12px; border: 1px solid #ddd; font-weight: bold;">
                        <?php echo htmlspecialchars($turma['nome_turma']); ?>
                    </td>
                    <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">
                        <?php echo htmlspecialchars($turma['ano_letivo']); ?>
                    </td>
                    <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">
                        <?php echo htmlspecialchars($turma['periodo']); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<style>
select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 1rem;
}

table tr:nth-child(even) {
    background-color: #f8f9fa;
}

table tr:hover {
    background-color: #e9ecef;
}
</style>

<?php require 'templates/footer.php'; ?>