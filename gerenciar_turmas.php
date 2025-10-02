<?php
// Inclui o cabeçalho, que já faz a verificação de login e inicia a sessão.
require_once 'templates/header.php';
require_once 'conexao.php';

// --- LÓGICA DE BUSCA E FILTRO (Esta parte continua a mesma, pois já é robusta) ---

$busca = trim($_GET['busca'] ?? '');
$filtro_ano = (int)($_GET['ano'] ?? 0);
$filtro_status = trim($_GET['status'] ?? '');

$sql_base = "SELECT 
                t.*, 
                p.nome_completo AS nome_professor,
                (SELECT COUNT(*) FROM matriculas m WHERE m.id_turma = t.id) AS total_alunos
            FROM turmas AS t
            LEFT JOIN professores AS p ON t.id_professor_regente = p.id";

$where_clauses = [];
$params = [];

if (!empty($busca)) {
    $where_clauses[] = "(t.nome_turma LIKE :busca OR t.codigo_turma LIKE :busca)";
    $params[':busca'] = '%' . $busca . '%';
}
if ($filtro_ano > 0) {
    $where_clauses[] = "t.ano_letivo = :ano";
    $params[':ano'] = $filtro_ano;
}
if (!empty($filtro_status)) {
    $where_clauses[] = "t.status = :status";
    $params[':status'] = $filtro_status;
}

if (!empty($where_clauses)) {
    $sql_base .= " WHERE " . implode(' AND ', $where_clauses);
}

$sql_base .= " ORDER BY t.ano_letivo DESC, t.nome_turma ASC";

try {
    $stmt = $pdo->prepare($sql_base);
    $stmt->execute($params);
    $turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $anos_letivos = $pdo->query("SELECT DISTINCT ano_letivo FROM turmas ORDER BY ano_letivo DESC")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Erro ao buscar turmas: " . $e->getMessage());
}
?>

<div class="page-header">
    <h2>Gerenciar Turmas</h2>
    <p>Visualize, busque e administre todas as turmas cadastradas.</p>
</div>

<div class="form-card">
    <form method="GET" action="gerenciar_turmas.php" class="filter-form">
        <div class="form-row">
            <div class="form-group flex-3">
                <label for="busca">Buscar por Nome ou Código</label>
                <input type="text" name="busca" id="busca" placeholder="Digite para buscar..." value="<?php echo htmlspecialchars($busca); ?>">
            </div>
            <div class="form-group">
                <label for="ano">Ano Letivo</label>
                <select name="ano" id="ano">
                    <option value="">Todos</option>
                    <?php foreach ($anos_letivos as $ano): ?>
                        <option value="<?php echo $ano; ?>" <?php echo ($filtro_ano == $ano) ? 'selected' : ''; ?>><?php echo $ano; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status">
                    <option value="">Todos</option>
                    <option value="Em Planejamento" <?php echo ($filtro_status == 'Em Planejamento') ? 'selected' : ''; ?>>Em Planejamento</option>
                    <option value="Aberta" <?php echo ($filtro_status == 'Aberta') ? 'selected' : ''; ?>>Aberta</option>
                    <option value="Em Andamento" <?php echo ($filtro_status == 'Em Andamento') ? 'selected' : ''; ?>>Em Andamento</option>
                    <option value="Encerrada" <?php echo ($filtro_status == 'Encerrada') ? 'selected' : ''; ?>>Encerrada</option>
                </select>
            </div>
            <div class="form-actions filter-actions">
                <button type="submit" class="btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
                <a href="gerenciar_turmas.php" class="btn-secondary">Limpar</a>
            </div>
        </div>
    </form>
</div>

<div class="form-card">
    <div class="table-header-actions">
        <span>Exibindo <?php echo count($turmas); ?> resultado(s).</span>
        <a href="cadastro_turma.php?reset=1" class="btn-primary"><i class="fas fa-plus"></i> Cadastrar Nova Turma</a>
    </div>

    <table class="table-gerenciamento">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nome da Turma</th>
                <th>Ano</th>
                <th>Turno</th>
                <th>Professor</th>
                <th>Alunos</th>
                <th>Status</th>
                <th style="width: 120px;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($turmas) > 0): ?>
                <?php foreach ($turmas as $turma): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($turma['codigo_turma']); ?></td>
                        <td><?php echo htmlspecialchars($turma['nome_turma']); ?></td>
                        <td><?php echo htmlspecialchars($turma['ano_letivo']); ?></td>
                        <td><?php echo htmlspecialchars($turma['periodo']); ?></td>
                        <td><?php echo htmlspecialchars($turma['nome_professor'] ?? 'N/D'); ?></td>
                        <td><?php echo $turma['total_alunos'] . ' / ' . ($turma['numero_maximo_alunos'] ?? '∞'); ?></td>
                        <td>
                            <span class="badge status-<?php echo strtolower(str_replace(' ', '-', $turma['status'])); ?>">
                                <?php echo htmlspecialchars($turma['status']); ?>
                            </span>
                        </td>
                        <td class="actions-cell">
                            <a href="detalhes_turma.php?id=<?php echo $turma['id']; ?>" class="btn-action" title="Ver Detalhes"><i class="fas fa-eye"></i></a>
                            <a href="editar_turma.php?id=<?php echo $turma['id']; ?>" class="btn-action" title="Editar Turma"><i class="fas fa-pencil-alt"></i></a>
                            <a href="excluir_turma.php?id=<?php echo $turma['id']; ?>" class="btn-action btn-delete" title="Excluir Turma" onclick="return confirm('Tem certeza que deseja excluir esta turma? Esta ação não pode ser desfeita.');">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px;">Nenhuma turma encontrada com os filtros atuais.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'templates/footer.php'; ?>