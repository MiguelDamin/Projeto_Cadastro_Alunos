<?php
// Inclui o cabeçalho e a conexão
require_once 'templates/header.php';
require_once 'conexao.php';

// --- 1. VALIDAÇÃO E BUSCA DOS DADOS PRINCIPAIS ---

// Pega o ID da turma da URL de forma segura. Se não houver, interrompe a execução.
$id_turma = (int)($_GET['id'] ?? 0);
if ($id_turma === 0) {
    die("<div class='form-card'><p class='alert alert-danger'>Erro: ID da turma não fornecido.</p></div>");
}

try {
    // Consulta principal que busca os detalhes da turma e os nomes do professor e da sala usando LEFT JOIN.
    $sql_turma = "SELECT 
                    t.*, 
                    p.nome_completo AS nome_professor_regente,
                    s.nome_sala AS nome_sala
                FROM turmas AS t
                LEFT JOIN professores AS p ON t.id_professor_regente = p.id
                LEFT JOIN salas AS s ON t.id_sala_aula = s.id
                WHERE t.id = ?";
    
    $stmt_turma = $pdo->prepare($sql_turma);
    $stmt_turma->execute([$id_turma]);
    $turma = $stmt_turma->fetch(PDO::FETCH_ASSOC);

    // Se a turma não for encontrada, exibe uma mensagem de erro.
    if (!$turma) {
        die("<div class='form-card'><p class='alert alert-danger'>Erro: Turma com ID {$id_turma} não encontrada.</p></div>");
    }

    // --- 2. BUSCAR DADOS ASSOCIADOS (ALUNOS E HORÁRIOS) ---

    // Busca todos os alunos matriculados nesta turma.
    $stmt_alunos = $pdo->prepare(
        "SELECT a.id, a.nome_completo, a.caminho_foto 
         FROM alunos a 
         JOIN matriculas m ON a.id = m.id_aluno 
         WHERE m.id_turma = ? ORDER BY a.nome_completo"
    );
    $stmt_alunos->execute([$id_turma]);
    $alunos_matriculados = $stmt_alunos->fetchAll(PDO::FETCH_ASSOC);

    // Busca a grade de horários completa, juntando com disciplinas e professores.
    $stmt_horarios = $pdo->prepare(
        "SELECT th.dia_semana, th.hora_inicio, th.hora_fim, d.nome_disciplina, p.nome_completo AS nome_professor
         FROM turmas_horarios th 
         JOIN disciplinas d ON th.id_disciplina = d.id 
         JOIN professores p ON th.id_professor = p.id 
         WHERE th.id_turma = ? ORDER BY th.hora_inicio, th.dia_semana"
    );
    $stmt_horarios->execute([$id_turma]);
    $horarios_salvos = $stmt_horarios->fetchAll(PDO::FETCH_ASSOC);

    // Reorganiza o array de horários para ser facilmente consumido na tabela HTML.
    $grade_formatada = [];
    foreach ($horarios_salvos as $h) {
        $grade_formatada[$h['hora_inicio']][$h['dia_semana']] = $h;
    }
    
    // Define os horários das aulas para construir a grade (como fizemos no modal).
    $horarios_aula = [
        ['inicio' => '07:30', 'fim' => '08:20'], ['inicio' => '08:20', 'fim' => '09:10'],
        ['inicio' => '09:10', 'fim' => '10:00'], ['intervalo' => '10:00 - 10:20'],
        ['inicio' => '10:20', 'fim' => '11:10'], ['inicio' => '11:10', 'fim' => '12:00']
    ];

} catch (PDOException $e) {
    die("Erro ao carregar dados da turma: " . $e->getMessage());
}
?>

<div class="page-top-header">
    <div>
        <h2>Detalhes da Turma: <?php echo htmlspecialchars($turma['nome_turma']); ?></h2>
        <p>Visão completa das informações, alunos e horários da turma.</p>
    </div>
    <a href="gerenciar_turmas.php" class="back-link"><i class="fas fa-arrow-left"></i> Voltar para a Lista de Turmas</a>
</div>

<div class="gerenciar-turma-grid">

    <div class="form-card">
        <h3><i class="fas fa-info-circle"></i> Informações Gerais</h3>
        <ul class="lista-detalhes">
            <li><strong>Código:</strong> <span><?php echo htmlspecialchars($turma['codigo_turma']); ?></span></li>
            <li><strong>Ano Letivo:</strong> <span><?php echo htmlspecialchars($turma['ano_letivo']); ?></span></li>
            <li><strong>Período:</strong> <span><?php echo htmlspecialchars($turma['periodo']); ?></span></li>
            <li><strong>Nível de Ensino:</strong> <span><?php echo htmlspecialchars($turma['nivel_ensino']); ?></span></li>
            <li><strong>Série/Ano:</strong> <span><?php echo htmlspecialchars($turma['serie_ano']); ?></span></li>
            <li><strong>Sala Principal:</strong> <span><?php echo htmlspecialchars($turma['nome_sala'] ?? 'Não definida'); ?></span></li>
            <li><strong>Professor Regente:</strong> <span><?php echo htmlspecialchars($turma['nome_professor_regente'] ?? 'Não definido'); ?></span></li>
            <li><strong>Status:</strong> <span class="badge status-<?php echo strtolower(str_replace(' ', '-', $turma['status'])); ?>"><?php echo htmlspecialchars($turma['status']); ?></span></li>
        </ul>
    </div>

    <div class="form-card">
        <h3><i class="fas fa-users"></i> Alunos Matriculados (<?php echo count($alunos_matriculados); ?>)</h3>
        <div class="lista-alunos-detalhes">
            <?php if (count($alunos_matriculados) > 0): ?>
                <?php foreach ($alunos_matriculados as $aluno): ?>
                    <div class="aluno-item">
                        <img src="<?php echo !empty($aluno['caminho_foto']) ? 'uploads/fotos_alunos/' . htmlspecialchars($aluno['caminho_foto']) : 'caminho/para/avatar_padrao.png'; ?>" alt="Foto do Aluno" class="foto-aluno-lista">
                        <span><?php echo htmlspecialchars($aluno['nome_completo']); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhum aluno matriculado nesta turma ainda.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="form-card full-width-card">
        <h3><i class="fas fa-calendar-alt"></i> Grade de Horários</h3>
        <table class="grade-horarios">
            <thead>
                <tr>
                    <th>Horário</th>
                    <th>Segunda-feira</th><th>Terça-feira</th><th>Quarta-feira</th>
                    <th>Quinta-feira</th><th>Sexta-feira</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($horarios_aula as $horario): ?>
                    <?php if (isset($horario['intervalo'])): ?>
                        <tr class="linha-intervalo"><td colspan="6">INTERVALO</td></tr>
                    <?php else: 
                        $hora_inicio = $horario['inicio'];
                        $hora_fim = $horario['fim'];
                    ?>
                        <tr>
                            <td><?php echo $hora_inicio . ' - ' . $hora_fim; ?></td>
                            <?php for ($dia = 2; $dia <= 6; $dia++): // 2=Segunda, 6=Sexta ?>
                                <td class="slot-preenchido">
                                    <?php if (isset($grade_formatada[$hora_inicio][$dia])): 
                                        $aula = $grade_formatada[$hora_inicio][$dia];
                                    ?>
                                        <strong><?php echo htmlspecialchars($aula['nome_disciplina']); ?></strong>
                                        <small><?php echo htmlspecialchars($aula['nome_professor']); ?></small>
                                    <?php endif; ?>
                                </td>
                            <?php endfor; ?>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>