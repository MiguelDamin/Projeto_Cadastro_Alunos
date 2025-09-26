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
    $alunos_disponiveis = $pdo->query("SELECT id, nome_completo FROM alunos ORDER BY nome_completo ASC")->fetchAll(PDO::FETCH_ASSOC);


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
    <form action="processa_cadastro_turma.php" method="POST">
        <input type="hidden" name="step" value="3">
        <fieldset>
            <legend>3. Localização e Horários</legend>
            <div class="form-group">
                <label for="id_sala">Sala de Aula Principal *</label>
                <select name="id_sala" id="id_sala" required>
                    <option value="">Selecione a sala...</option>
                    <?php foreach ($salas as $sala): ?>
                        <option value="<?php echo $sala['id']; ?>" <?php
                        // Defina 'selected' se esta era a sala previamente selecionada.
                        if (isset($dados_turma['id_sala']) && $dados_turma['id_sala'] == $sala['id']) {
                            echo 'selected';
                        }
                        ?>>
                            <?php echo htmlspecialchars($sala['nome_sala']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Grade de Horários</label>
                <button type="button" id="btn-abrir-grade" class="btn-secondary">Configurar Grade de Horários</button>
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
                <legend>4. Matricular Alunos na Turma</legend>
                <p><small>Comece a digitar o nome de um aluno para buscar e selecionar. Você pode selecionar múltiplos alunos.</small></p>
                <div class="form-group">
                    <label for="alunos-select">Alunos *</label>
                    <select name="alunos[]" id="alunos-select" multiple="multiple" class="select2-aluno" required>
                        <?php 
                        $alunos_selecionados = $dados_turma['alunos_matriculados'] ?? [];
                        foreach ($alunos_disponiveis as $aluno): 
                            $selecionado = in_array($aluno['id'], $alunos_selecionados) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $aluno['id']; ?>" <?php echo $selecionado; ?>>
                                <?php echo htmlspecialchars($aluno['nome_completo']); ?>
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

<script>
$(document).ready(function() {
    $('#alunos-select').select2({
        placeholder: "Busque e selecione os alunos",
        width: '100%'
    });
});

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

<div id="modal-horarios" class="modal-overlay" style="display: none;"> 
    <div class="modal-content">
        <div class="modal-header">
            <h3>Grade de Horários da Turma</h3>
            <button type="button" id="fechar-modal-horarios" class="modal-close-btn">&times;</button>
        </div>
        <div class="modal-body">
            <table class="grade-horarios">
                <thead>
                    <tr>
                        <th>Horário</th>
                        <th>Segunda-feira</th>
                        <th>Terça-feira</th>
                        <th>Quarta-feira</th>
                        <th>Quinta-feira</th>
                        <th>Sexta-feira</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>07:30 - 08:20</td>
                        <td class="slot-horario" data-dia="2" data-hora-inicio="08:00"></td>
                        <td class="slot-horario" data-dia="3" data-hora-inicio="08:00"></td>
                        <td class="slot-horario" data-dia="4" data-hora-inicio="08:00"></td>
                        <td class="slot-horario" data-dia="5" data-hora-inicio="08:00"></td>
                        <td class="slot-horario" data-dia="6" data-hora-inicio="08:00"></td>
                    </tr>
                    <tr>
                        <td>08:20 - 09:10</td>
                        <td class="slot-horario" data-dia="2" data-hora-inicio="08:20"></td>
                        <td class="slot-horario" data-dia="3" data-hora-inicio="08:20"></td>
                        <td class="slot-horario" data-dia="4" data-hora-inicio="08:20"></td>
                        <td class="slot-horario" data-dia="5" data-hora-inicio="08:20"></td>
                        <td class="slot-horario" data-dia="6" data-hora-inicio="08:20"></td>
                    </tr>
                    <tr>
                        <td>09:10 - 10:00</td>
                        <td class="slot-horario" data-dia="2" data-hora-inicio="09:10"></td>
                        <td class="slot-horario" data-dia="3" data-hora-inicio="09:10"></td>
                        <td class="slot-horario" data-dia="4" data-hora-inicio="09:10"></td>
                        <td class="slot-horario" data-dia="5" data-hora-inicio="09:10"></td>
                        <td class="slot-horario" data-dia="6" data-hora-inicio="09:10"></td>
                    </tr>
                    <tr>
                        <td>10:00 - 10:50</td>
                        <td class="slot-horario" data-dia="2" data-hora-inicio="10:00"></td>
                        <td class="slot-horario" data-dia="3" data-hora-inicio="10:00"></td>
                        <td class="slot-horario" data-dia="4" data-hora-inicio="10:00"></td>
                        <td class="slot-horario" data-dia="5" data-hora-inicio="10:00"></td>
                        <td class="slot-horario" data-dia="6" data-hora-inicio="10:00"></td>
                    </tr>
                    <tr>
                        <td>10:50 - 11:40</td>
                        <td class="slot-horario" data-dia="2" data-hora-inicio="10:50"></td>
                        <td class="slot-horario" data-dia="3" data-hora-inicio="10:50"></td>
                        <td class="slot-horario" data-dia="4" data-hora-inicio="10:50"></td>
                        <td class="slot-horario" data-dia="5" data-hora-inicio="10:50"></td>
                        <td class="slot-horario" data-dia="6" data-hora-inicio="10:50"></td>
                    </tr>
                    </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modal-selecao" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h4>Adicionar Aula</h4>
            <button type="button" id="fechar-modal-selecao" class="modal-close-btn">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="selecao-dia-semana">
            <input type="hidden" id="selecao-hora-inicio">
            
            <div class="form-group">
                <label for="selecao_disciplina">Disciplina</label>
                <select id="selecao_disciplina">
                    </select>
            </div>
            <div class="form-group">
                <label for="selecao_professor">Professor</label>
                <select id="selecao_professor">
                    </select>
            </div>
            <div class="form-actions">
                <button type="button" id="salvar-horario" class="btn-primary">Salvar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Dados do PHP para o JavaScript ---
    const disciplinas = <?php echo json_encode($pdo->query("SELECT id, nome_disciplina FROM disciplinas")->fetchAll(PDO::FETCH_ASSOC)); ?>;
    const professores = <?php echo json_encode($pdo->query("SELECT id, nome_completo FROM professores")->fetchAll(PDO::FETCH_ASSOC)); ?>;

    // --- Elementos do DOM ---
    const btnAbrirGrade = document.getElementById('btn-abrir-grade');
    const modalHorarios = document.getElementById('modal-horarios');
    const btnFecharModalHorarios = document.getElementById('fechar-modal-horarios');
    
    const modalSelecao = document.getElementById('modal-selecao');
    const btnFecharModalSelecao = document.getElementById('fechar-modal-selecao');
    const btnSalvarHorario = document.getElementById('salvar-horario');
    
    const selectDisciplina = document.getElementById('selecao_disciplina');
    const selectProfessor = document.getElementById('selecao_professor');
    let slotClicado = null;

    // --- Funções do Modal ---
    if (btnAbrirGrade) {
        btnAbrirGrade.addEventListener('click', () => modalHorarios.style.display = 'flex');
    }
    if (btnFecharModalHorarios) {
        btnFecharModalHorarios.addEventListener('click', () => modalHorarios.style.display = 'none');
    }
    if (btnFecharModalSelecao) {
        btnFecharModalSelecao.addEventListener('click', () => modalSelecao.style.display = 'none');
    }

    // --- Lógica da Grade ---
    document.querySelectorAll('.slot-horario').forEach(slot => {
        slot.addEventListener('click', function() {
            slotClicado = this;
            // Guarda as informações do slot clicado
            document.getElementById('selecao-dia-semana').value = this.dataset.dia;
            document.getElementById('selecao-hora-inicio').value = this.dataset.horaInicio;
            
            // Popula os dropdowns do modal de seleção
            selectDisciplina.innerHTML = '<option value="">Selecione...</option>' + disciplinas.map(d => `<option value="${d.id}">${d.nome_disciplina}</option>`).join('');
            selectProfessor.innerHTML = '<option value="">Selecione...</option>' + professores.map(p => `<option value="${p.id}">${p.nome_completo}</option>`).join('');
            
            modalSelecao.style.display = 'flex';
        });
    });

    // --- Lógica do AJAX para Salvar ---
    if (btnSalvarHorario) {
        btnSalvarHorario.addEventListener('click', function() {
            const dadosHorario = {
                step: 3, // Identifica a ação no script PHP
                action: 'salvar_horario', // Ação específica
                dia_semana: document.getElementById('selecao-dia-semana').value,
                hora_inicio: document.getElementById('selecao-hora-inicio').value,
                id_disciplina: selectDisciplina.value,
                id_professor: selectProfessor.value
            };

            // Envia os dados para o backend via AJAX/Fetch
            fetch('processa_cadastro_turma.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dadosHorario)
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    modalSelecao.style.display = 'none';

                    // 2. Pega o NOME da disciplina e do professor selecionados para exibir na tela
                    const nomeDisciplina = selectDisciplina.options[selectDisciplina.selectedIndex].text;
                    const nomeProfessor = selectProfessor.options[selectProfessor.selectedIndex].text;
                    
                    // 3. Monta o HTML que será inserido na grade
                    const novoConteudo = `
                        <strong>${nomeDisciplina}</strong>
                        <small>${nomeProfessor}</small>
                    `;

                    // 4. Verifica se temos um slot guardado e atualiza seu conteúdo e estilo
                    if (slotClicado) {
                        slotClicado.innerHTML = novoConteudo;
                        slotClicado.classList.add('slot-preenchido');
                    }
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(error => console.error('Erro no AJAX:', error));
        });
    }
});
</script>

<?php require 'templates/footer.php'; ?>