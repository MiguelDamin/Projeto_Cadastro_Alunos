<?php
session_start();
require_once 'templates/header.php';
require_once 'conexao.php';

// --- LÓGICA DE CONTROLE DO WIZARD ---
if (isset($_GET['reset'])) {
    unset($_SESSION['cadastro_turma_dados']);
    header('Location: cadastro_turma.php');
    exit;
}
// Permite voltar para um passo específico
if (isset($_GET['step'])) {
    $_SESSION['cadastro_turma_dados']['step'] = (int)$_GET['step'];
}
$step = $_SESSION['cadastro_turma_dados']['step'] ?? 1;

// Pega dados da sessão para repopular o formulário
$dados_turma = $_SESSION['cadastro_turma_dados'] ?? [];

// --- BUSCAR DADOS DO BANCO PARA OS DROPDOWNS ---
try {
    // Vamos verificar cada query, uma por uma.
    $niveis_ensino = $pdo->query("SELECT id, nome FROM niveis_ensino ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    $series = $pdo->query("SELECT id, nome FROM series ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    $salas = $pdo->query("SELECT id, nome_sala FROM salas ORDER BY nome_sala ASC")->fetchAll(PDO::FETCH_ASSOC);
    $disciplinas = $pdo->query("SELECT id, nome_disciplina FROM disciplinas ORDER BY nome_disciplina ASC")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // MUDANÇA IMPORTANTE AQUI:
    // Em vez de falhar silenciosamente, vamos parar o script e mostrar o erro exato.
    die("<h1>Erro ao Carregar Dados do Banco</h1><p>Ocorreu um erro ao tentar buscar as informações necessárias para o formulário. Verifique se todas as tabelas (niveis_ensino, series, salas, disciplinas) e suas colunas existem no banco de dados.</p><p><strong>Mensagem do Erro:</strong> " . $e->getMessage() . "</p>");
}
?>

<div class="form-card">
    <h2 class="form-title">Cadastro de Nova Turma</h2>
    <p class="step-indicator">Etapa <?php echo $step; ?> de 5</p>

    <?php if ($step == 1): ?>
        <form action="processa_cadastro_turma.php" method="POST">
            <input type="hidden" name="step" value="1">
            <fieldset>
                <legend>1. Identificação da Turma</legend>
                <div class="form-row">
                    <div class="form-group flex-2">
                        <label for="nome_turma">Nome da Turma *</label>
                        <input type="text" name="nome_turma" id="nome_turma" required data-codigo="nome" value="<?php echo htmlspecialchars($dados_turma['nome_turma'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="ano_letivo">Ano Letivo *</label>
                        <input type="number" name="ano_letivo" id="ano_letivo" value="<?php echo htmlspecialchars($dados_turma['ano_letivo'] ?? date('Y')); ?>" required data-codigo="ano">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="codigo_turma">Código da Turma (sugestão)</label>
                        <input type="text" name="codigo_turma" id="codigo_turma" readonly value="<?php echo htmlspecialchars($dados_turma['codigo_turma'] ?? ''); ?>">
                    </div>
                </div>
            </fieldset>
            <div class="form-actions"><button type="submit" class="btn-primary">Próximo</button></div>
        </form>
    <?php endif; ?>

    <?php if ($step == 2): ?>
        <div class="summary-box">
            <strong>Resumo Etapa 1:</strong> Turma "<?php echo htmlspecialchars($dados_turma['nome_turma']); ?>" / <?php echo htmlspecialchars($dados_turma['ano_letivo']); ?>.
            <a href="cadastro_turma.php?step=1">(Alterar)</a>
        </div>
        <form action="processa_cadastro_turma.php" method="POST">
            <input type="hidden" name="step" value="2">
            <fieldset>
                <legend>2. Estrutura da Turma</legend>
                <div class="form-row">
                    <div class="form-group">
                        <label for="id_nivel_ensino">Nível de Ensino *</label>
                        <select name="id_nivel_ensino" id="id_nivel_ensino" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($niveis_ensino as $nivel): ?>
                                <option value="<?php echo $nivel['id']; ?>" <?php echo (($dados_turma['id_nivel_ensino'] ?? '') == $nivel['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($nivel['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="id_serie">Série / Ano *</label>
                        <select name="id_serie" id="id_serie" required>
                             <option value="">Selecione...</option>
                            <?php foreach ($series as $serie): ?>
                                <option value="<?php echo $serie['id']; ?>" <?php echo (($dados_turma['id_serie'] ?? '') == $serie['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($serie['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="turno">Turno *</label>
                        <select name="turno" id="turno" required>
                            <option value="">Selecione...</option>
                            <?php $turno_selecionado = $dados_turma['turno'] ?? ''; ?>
                            <option value="Manhã" <?php echo ($turno_selecionado == 'Manhã') ? 'selected' : ''; ?>>Manhã</option>
                            <option value="Tarde" <?php echo ($turno_selecionado == 'Tarde') ? 'selected' : ''; ?>>Tarde</option>
                            <option value="Noite" <?php echo ($turno_selecionado == 'Noite') ? 'selected' : ''; ?>>Noite</option>
                            <option value="Integral" <?php echo ($turno_selecionado == 'Integral') ? 'selected' : ''; ?>>Integral</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="numero_maximo_alunos">Nº Máximo de Alunos</label>
                        <input type="number" name="numero_maximo_alunos" id="numero_maximo_alunos" min="1" value="<?php echo htmlspecialchars($dados_turma['max_alunos'] ?? '30'); ?>">
                    </div>
                </div>
            </fieldset>
            <div class="form-actions"><button type="submit" class="btn-primary">Próximo</button></div>
        </form>
    <?php endif; ?>

    <?php if ($step == 3): ?>
        <div class="summary-box">
            <strong>Resumo Etapa 2:</strong> Nível: <?php echo htmlspecialchars($dados_turma['nivel_ensino'] ?? 'N/D'); ?>, Turno: <?php echo htmlspecialchars($dados_turma['turno'] ?? 'N/D'); ?>.
            <a href="cadastro_turma.php?step=2">(Alterar)</a>
        </div>
        <form action="processa_cadastro_turma.php" method="POST">
            <input type="hidden" name="step" value="3">
            <fieldset>
                <legend>3. Localização e Horários</legend>
                <div class="form-row">
                    <div class="form-group">
                        <label for="id_sala">Sala de Aula Principal *</label>
                        <select name="id_sala" id="id_sala" required>
                            <option value="">Selecione a sala principal...</option>
                             <?php foreach ($salas as $sala): ?>
                                <option value="<?php echo $sala['id']; ?>" <?php echo (($dados_turma['id_sala'] ?? '') == $sala['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($sala['nome_sala']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Grade de Horários</label>
                    <p><small>A grade de horários detalhada (disciplinas, professores e horários) poderá ser configurada após a criação da turma, na tela de "Editar Turma".</small></p>
                    <button type="button" class="btn-secondary" disabled>Configurar Grade de Horários</button>
                </div>
            </fieldset>
            <div class="form-actions">
                <button type="submit" class="btn-primary">Próximo</button>
            </div>
        </form>
    <?php endif; ?>
    <?php if ($step == 4): ?>
        <div class="summary-box">
            <strong>Resumo Etapa 3:</strong> Sala Principal Definida.
            <a href="cadastro_turma.php?step=3">(Alterar)</a>
        </div>
        <form action="processa_cadastro_turma.php" method="POST">
            <input type="hidden" name="step" value="4">
            <fieldset>
                <legend>4. Disciplinas da Turma</legend>
                <p><small>Selecione todas as disciplinas que serão lecionadas para esta turma. Use Ctrl+Click (ou Cmd+Click em Mac) para selecionar várias.</small></p>
                <div class="form-group">
                    <label for="disciplinas">Disciplinas *</label>
                    <select name="disciplinas[]" id="disciplinas" multiple size="10" required>
                        <?php 
                        $disciplinas_selecionadas = $dados_turma['ids_disciplinas'] ?? [];
                        foreach ($disciplinas as $disciplina): 
                            $selecionado = in_array($disciplina['id'], $disciplinas_selecionadas) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $disciplina['id']; ?>" <?php echo $selecionado; ?>>
                                <?php echo htmlspecialchars($disciplina['nome_disciplina']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </fieldset>
            <div class="form-actions">
                <button type="submit" class="btn-primary">Próximo</button>
            </div>
        </form>
    <?php endif; ?>

    </div>
</div>

<script>

document.addEventListener('DOMContentLoaded', function() {

    const anoInput = document.querySelector('[data-codigo="ano"]');

    const nomeInput = document.querySelector('[data-codigo="nome"]');

    const codigoOutput = document.getElementById('codigo_turma');



    function gerarCodigoSugerido() {

        if (!anoInput || !nomeInput || !codigoOutput) return;



        const ano = anoInput.value.trim();

        const nome = nomeInput.value.trim();

        

        if (!ano || !nome) {

            codigoOutput.value = '';

            return;

        }



        // Lógica para criar uma sigla a partir do nome

        const nomeSigla = nome.replace(/[^a-zA-Z0-9\s]/g, '').replace(/\s+/g, '-').toUpperCase();

        

        // Monta o código final

        codigoOutput.value = `${ano}-${nomeSigla}`;

    }



    // Adiciona um "ouvinte" a cada campo relevante

    anoInput.addEventListener('input', gerarCodigoSugerido);

    nomeInput.addEventListener('input', gerarCodigoSugerido);



    // Gera o código uma vez no carregamento da página

    gerarCodigoSugerido();

});

</script>

<?php require 'templates/footer.php'; ?>