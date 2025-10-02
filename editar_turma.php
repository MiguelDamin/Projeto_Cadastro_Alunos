<?php
require_once 'templates/header.php';
require_once 'conexao.php';

$id_turma = (int)($_GET['id'] ?? 0);
if ($id_turma === 0) die("Erro: ID da turma não fornecido.");

// --- BUSCA TODOS OS DADOS INICIAIS DA TURMA ---
try {
    // Detalhes da turma (com JOIN para professor e sala)
    $stmt_turma = $pdo->prepare("SELECT t.*, p.nome_completo as nome_professor_regente, s.nome_sala FROM turmas t LEFT JOIN professores p ON t.id_professor_regente = p.id LEFT JOIN salas s ON t.id_sala_aula = s.id WHERE t.id = ?");
    $stmt_turma->execute([$id_turma]);
    $turma = $stmt_turma->fetch(PDO::FETCH_ASSOC);
    if (!$turma) die("Erro: Turma não encontrada.");

    // Alunos matriculados
    $stmt_alunos = $pdo->prepare("SELECT a.id, a.nome_completo, a.cpf, m.id as id_matricula FROM alunos a JOIN matriculas m ON a.id = m.id_aluno WHERE m.id_turma = ? ORDER BY a.nome_completo");
    $stmt_alunos->execute([$id_turma]);
    $alunos_matriculados = $stmt_alunos->fetchAll(PDO::FETCH_ASSOC);

    // Dados para os dropdowns de edição
    $professores = $pdo->query("SELECT id, nome_completo FROM professores ORDER BY nome_completo")->fetchAll(PDO::FETCH_ASSOC);


    // Busca a grade de horários já salva
    $stmt_horarios = $pdo->prepare("SELECT th.*, d.nome_disciplina, p.nome_completo AS nome_professor FROM turmas_horarios th JOIN disciplinas d ON th.id_disciplina = d.id JOIN professores p ON th.id_professor = p.id WHERE th.id_turma = ?");
    $stmt_horarios->execute([$id_turma]);
    $horarios_salvos = $stmt_horarios->fetchAll(PDO::FETCH_ASSOC);

    // Reorganiza os horários para fácil acesso no HTML e JS
    $grade_formatada = [];
    foreach ($horarios_salvos as $h) {
        $grade_formatada[$h['dia_semana']][$h['hora_inicio']] = $h;
    }
    
    // Busca todas as disciplinas e professores para os modais
    $disciplinas = $pdo->query("SELECT id, nome_disciplina FROM disciplinas ORDER BY nome_disciplina")->fetchAll(PDO::FETCH_ASSOC);

    // Define os horários das aulas
    $horarios_aula = [['inicio' => '07:30', 'fim' => '08:20'], ['inicio' => '08:20', 'fim' => '09:10'], ['inicio' => '09:10', 'fim' => '10:00'], ['intervalo' => '10:00 - 10:20'], ['inicio' => '10:20', 'fim' => '11:10'], ['inicio' => '11:10', 'fim' => '12:00']];

} catch (PDOException $e) {
    die("Erro ao carregar dados da turma: " . $e->getMessage());
}
?>

<div class="page-top-header">
    <div>
        <h2 id="titulo-turma">Gerenciar Turma: <?php echo htmlspecialchars($turma['nome_turma']); ?></h2>
        <p>Adicione ou remova alunos, configure a grade de horários e edite os detalhes da turma.</p>
    </div>
    <a href="gerenciar_turmas.php" class="back-link"><i class="fas fa-arrow-left"></i> Voltar</a>
</div>

<div class="gerenciar-turma-grid">
    <div class="coluna-principal">
        <div class="form-card">
            <h3><i class="fas fa-users"></i> Alunos Matriculados (<span id="contador-alunos"><?php echo count($alunos_matriculados); ?></span>/<?php echo $turma['numero_maximo_alunos'] ?: '∞'; ?>)</h3>
            <div id="lista-alunos-matriculados">
                <?php if (empty($alunos_matriculados)): ?><p id="sem-alunos-msg">Nenhum aluno matriculado.</p><?php else: ?>
                    <?php foreach ($alunos_matriculados as $aluno): ?>
                        <div class="aluno-item" id="matricula-<?php echo $aluno['id_matricula']; ?>">
                            <span><?php echo htmlspecialchars($aluno['nome_completo']); ?> (CPF: <?php echo htmlspecialchars($aluno['cpf']); ?>)</span>
                            <button class="btn-action btn-delete btn-remover-aluno" data-id-matricula="<?php echo $aluno['id_matricula']; ?>" title="Desmatricular Aluno">&times;</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <hr>
            <h4><i class="fas fa-user-plus"></i> Matricular Novo Aluno</h4>
            <div class="form-group"><label for="select-adicionar-aluno">Buscar aluno por nome ou CPF</label><select id="select-adicionar-aluno" style="width: 100%;"></select></div>
            <div class="form-actions">
                <button type="button" id="btn-adicionar-aluno" class="btn-primary btn-loading">
                    <i class="fas fa-spinner fa-spin spinner"></i>
                    <span class="btn-text">Matricular Aluno</span>
                </button>
            </div>
        </div>
    </div>
    
    <div class="coluna-secundaria">
        <div class="form-card" id="card-detalhes">
            
            <div id="view-mode">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3><i class="fas fa-info-circle"></i> Detalhes da Turma</h3>
                    <button id="btn-editar-detalhes" class="btn-secondary">Editar</button>
                </div>
                <ul class="lista-detalhes">
                    <li><strong>Nome:</strong> <span data-field="nome_turma"><?php echo htmlspecialchars($turma['nome_turma']); ?></span></li>
                    <li><strong>Status:</strong> <span data-field="status" class="badge status-<?php echo strtolower(str_replace(' ', '-', $turma['status'])); ?>"><?php echo htmlspecialchars($turma['status']); ?></span></li>
                    <li><strong>Alunos:</strong> <span data-field="alunos"><?php echo count($alunos_matriculados) . ' / ' . ($turma['numero_maximo_alunos'] ?: '∞'); ?></span></li>
                    <li><strong>Professor Regente:</strong> <span data-field="nome_professor_regente"><?php echo htmlspecialchars($turma['nome_professor_regente'] ?? 'Não definido'); ?></span></li>
                    <li><strong>Início das Aulas:</strong> <span data-field="data_inicio"><?php echo $turma['data_inicio'] ? date('d/m/Y', strtotime($turma['data_inicio'])) : 'N/D'; ?></span></li>
                    <li><strong>Fim das Aulas:</strong> <span data-field="data_fim"><?php echo $turma['data_fim'] ? date('d/m/Y', strtotime($turma['data_fim'])) : 'N/D'; ?></span></li>
                </ul>
            </div>

            <div id="edit-mode" style="display: none;">
                <div class="card-header"><h3><i class="fas fa-pencil-alt"></i> Editando Detalhes</h3></div>
                <form id="form-detalhes">
                    <div class="form-row">
                        <div class="form-group flex-2"><label for="edit-nome-turma">Nome da Turma *</label><input type="text" id="edit-nome-turma" name="nome_turma" value="<?php echo htmlspecialchars($turma['nome_turma']); ?>" required></div>
                        <div class="form-group"><label for="edit-max-alunos">Nº Máximo de Alunos</label><input type="number" id="edit-max-alunos" name="numero_maximo_alunos" value="<?php echo htmlspecialchars($turma['numero_maximo_alunos']); ?>"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label for="edit-data-inicio">Data de Início</label><input type="date" id="edit-data-inicio" name="data_inicio" value="<?php echo htmlspecialchars($turma['data_inicio']); ?>"></div>
                        <div class="form-group"><label for="edit-data-fim">Data de Fim</label><input type="date" id="edit-data-fim" name="data_fim" value="<?php echo htmlspecialchars($turma['data_fim']); ?>"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-professor-regente">Professor Regente</label>
                            <select id="edit-professor-regente" name="id_professor_regente">
                                <option value="">Nenhum...</option>
                                <?php foreach ($professores as $p): ?>
                                    <option value="<?php echo $p['id']; ?>" <?php echo ($p['id'] == $turma['id_professor_regente']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['nome_completo']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit-status">Status</label>
                            <select id="edit-status" name="status">
                                <option value="Em Planejamento" <?php echo ($turma['status'] == 'Em Planejamento') ? 'selected' : ''; ?>>Em Planejamento</option>
                                <option value="Aberta" <?php echo ($turma['status'] == 'Aberta') ? 'selected' : ''; ?>>Aberta</option>
                                <option value="Em Andamento" <?php echo ($turma['status'] == 'Em Andamento') ? 'selected' : ''; ?>>Em Andamento</option>
                                <option value="Encerrada" <?php echo ($turma['status'] == 'Encerrada') ? 'selected' : ''; ?>>Encerrada</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group"><label for="edit-descricao">Descrição / Observações</label><textarea id="edit-descricao" name="descricao" rows="3"><?php echo htmlspecialchars($turma['descricao']); ?></textarea></div>
                    <div class="form-actions">
                        <button type="button" id="btn-cancelar-edicao" class="btn-secondary-outline">Cancelar</button>
                        <button type="submit" id="btn-salvar-detalhes" class="btn-primary btn-loading">
                            <i class="fas fa-spinner fa-spin spinner"></i><span class="btn-text">Salvar</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </div>
</div>

                <div class="form-card">
             <h3><i class="fas fa-calendar-alt"></i> Grade de Horários</h3>
             <p><small>Clique no botão para abrir o editor da grade de horários.</small></p>
             <div class="form-actions">
                <button type="button" class="btn-secondary" id="btn-abrir-grade">Gerenciar Grade</button>
             </div>
        </div>
    </div>
</div>

<div id="modal-horarios" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header"><h3>Grade de Horários da Turma</h3><button type="button" id="fechar-modal-horarios" class="modal-close-btn">&times;</button></div>
        <div class="modal-body">
            <table class="grade-horarios">
                <thead><tr><th>Horário</th><th>Segunda</th><th>Terça</th><th>Quarta</th><th>Quinta</th><th>Sexta</th></tr></thead>
                <tbody>
                    <?php foreach ($horarios_aula as $horario): ?>
                        <?php if (isset($horario['intervalo'])): ?>
                            <tr class="linha-intervalo"><td colspan="6">INTERVALO</td></tr>
                        <?php else: $hora_inicio = $horario['inicio']; $hora_fim = $horario['fim']; ?>
                            <tr>
                                <td><?php echo $hora_inicio . ' - ' . $hora_fim; ?></td>
                                <?php for ($dia = 2; $dia <= 6; $dia++): ?>
                                    <td class="slot-horario <?php echo isset($grade_formatada[$dia][$hora_inicio]) ? 'slot-preenchido' : ''; ?>" data-dia="<?php echo $dia; ?>" data-hora-inicio="<?php echo $hora_inicio; ?>" data-hora-fim="<?php echo $hora_fim; ?>">
                                        <?php if (isset($grade_formatada[$dia][$hora_inicio])): $aula = $grade_formatada[$dia][$hora_inicio]; ?>
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
</div>
<div id="modal-selecao-aula" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header"><h4>Adicionar/Editar Aula</h4><button type="button" id="fechar-modal-selecao" class="modal-close-btn">&times;</button></div>
        <div class="modal-body">
            <input type="hidden" id="selecao-dia-semana"><input type="hidden" id="selecao-hora-inicio"><input type="hidden" id="selecao-hora-fim">
            <div class="form-group"><label for="selecao_disciplina">Disciplina</label><select id="selecao_disciplina"></select></div>
            <div class="form-group"><label for="selecao_professor">Professor</label><select id="selecao_professor"></select></div>
            <div class="form-actions" style="justify-content: space-between;">
                <button type="button" id="remover-horario" class="btn-danger-outline">Remover</button>
                <button type="button" id="salvar-horario" class="btn-primary">Salvar</button>
            </div>
        </div>
    </div>
</div>

<?php require 'templates/footer.php'; ?>

<script>
// Usa-se jQuery.noConflict() como boa prática se outras bibliotecas puderem ser adicionadas no futuro
jQuery(document).ready(function($) {
    // --- DADOS DO PHP PARA O JAVASCRIPT ---
    const idTurma = <?php echo $id_turma; ?>;
    const disciplinas = <?php echo json_encode($disciplinas); ?>;
    const professores = <?php echo json_encode($professores); ?>;
    let gradeSalva = <?php echo json_encode($grade_formatada); ?>;

    // =======================================================
    // 1. LÓGICA PARA EDIÇÃO IN-PLACE DOS DETALHES
    // =======================================================
    const viewMode = $('#view-mode');
    const editMode = $('#edit-mode');
    const btnSalvarDetalhes = $('#btn-salvar-detalhes');

    $('#btn-editar-detalhes').on('click', function() { viewMode.fadeOut(200, () => editMode.fadeIn(200)); });
    $('#btn-cancelar-edicao').on('click', function() { editMode.fadeOut(200, () => viewMode.fadeIn(200)); });
    
$('#form-detalhes').on('submit', function(e) {
    e.preventDefault();
    
    // ADICIONADO: Ativa a animação e desabilita o botão
    btnSalvarDetalhes.addClass('is-loading').prop('disabled', true);

    // Seus dados e a requisição fetch continuam os mesmos...
    const data = { 
        action: 'salvar_detalhes',
        id_turma: idTurma,
        nome_turma: $('#edit-nome-turma').val(),
        numero_maximo_alunos: $('#edit-max-alunos').val(),
        data_inicio: $('#edit-data-inicio').val(),
        data_fim: $('#edit-data-fim').val(),
        id_professor_regente: $('#edit-professor-regente').val(),
        status: $('#edit-status').val(),
        descricao: $('#edit-descricao').val()
    };

    fetch('api_gerenciar_turma.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // ... (lógica de atualização visual dos detalhes que você já tem) ...
            alert('Detalhes da turma atualizados com sucesso!');
            editMode.fadeOut(200, () => viewMode.fadeIn(200));

            // Atualiza os campos na tela
            $('#titulo-turma').text('Gerenciar Turma: ' + result.turma.nome_turma);
            $('span[data-field="nome_turma"]').text(result.turma.nome_turma);
            // ... (atualize os outros campos aqui se necessário)
        } else {
            alert('Erro: ' + result.message);
        }
    })
    .catch(error => console.error('Erro:', error))
    .finally(() => { // O bloco .finally() já estava aqui, perfeito!
         btnSalvarDetalhes.removeClass('is-loading').prop('disabled', false);
    });
});


    // =======================================================
    // 2. LÓGICA PARA GERENCIAR ALUNOS
    // =======================================================
const listaAlunosEl = $('#lista-alunos-matriculados');

// --- Configuração da Busca de Alunos (Select2 com AJAX) ---
$('#select-adicionar-aluno').select2({
    placeholder: 'Digite o nome ou CPF do aluno...',
    minimumInputLength: 3, // Inicia a busca após 3 caracteres digitados
    ajax: {
        url: 'api_buscar_alunos.php', // O arquivo PHP que faz a busca
        dataType: 'json',
        delay: 250, // Pequena pausa para não sobrecarregar o servidor
        data: function (params) {
            return {
                q: params.term // 'q' é o nome do parâmetro que a API espera
            };
        },
        processResults: function (data) {
            // Formata a resposta da API para o formato que o Select2 entende
            return {
                results: data.results
            };
        },
        cache: true
    },
    language: {
        noResults: function () {
            return "Nenhum aluno encontrado";
        },
        inputTooShort: function () {
            return "Digite 3 ou mais caracteres para buscar...";
        },
        searching: function() {
            return "Buscando...";
        }
    }
});

// --- Lógica para ADICIONAR aluno ---
$('#btn-adicionar-aluno').on('click', function() {
    const idAlunoSelecionado = $('#select-adicionar-aluno').val();
    const btnAdicionarAluno = $(this);

    if (!idAlunoSelecionado) {
        alert('Por favor, busque e selecione um aluno primeiro.');
        return;
    }

    btnAdicionarAluno.addClass('is-loading').prop('disabled', true);

    const payload = {
        action: 'matricular_aluno',
        id_turma: idTurma,
        id_aluno: idAlunoSelecionado
    };

    fetch('api_gerenciar_turma.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            $('#sem-alunos-msg').remove();
            const novoAlunoHtml = `
                <div class="aluno-item" id="matricula-${result.id_matricula}">
                    <span>${result.aluno.nome_completo} (CPF: ${result.aluno.cpf})</span>
                    <button class="btn-action btn-delete btn-remover-aluno" data-id-matricula="${result.id_matricula}" title="Desmatricular Aluno">&times;</button>
                </div>
            `;
            listaAlunosEl.append(novoAlunoHtml);
            const contadorEl = $('#contador-alunos');
            const novoTotal = parseInt(contadorEl.text()) + 1;
            contadorEl.text(novoTotal);
            $('#select-adicionar-aluno').val(null).trigger('change');
        } else {
            alert('Erro: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Erro na requisição:', error);
        alert('Ocorreu um erro de comunicação. Tente novamente.');
    })
    .finally(() => {
        btnAdicionarAluno.removeClass('is-loading').prop('disabled', false);
    });
});

// --- Lógica para REMOVER aluno ---
listaAlunosEl.on('click', '.btn-remover-aluno', function() {
    if (!confirm('Tem certeza que deseja desmatricular este aluno?')) {
        return;
    }

    const idMatricula = $(this).data('id-matricula');
    const elementoAluno = $(`#matricula-${idMatricula}`);

    const payload = {
        action: 'desmatricular_aluno',
        id_turma: idTurma,
        id_matricula: idMatricula
    };

    fetch('api_gerenciar_turma.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            elementoAluno.fadeOut(300, function() { 
                $(this).remove(); 
                
                const contadorEl = $('#contador-alunos');
                const novoTotal = parseInt(contadorEl.text()) - 1;
                contadorEl.text(novoTotal);

                if (listaAlunosEl.children('.aluno-item').length === 0) {
                    listaAlunosEl.html('<p id="sem-alunos-msg">Nenhum aluno matriculado.</p>');
                }
            });
        } else {
            alert('Erro ao desmatricular: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Erro na requisição:', error);
        alert('Ocorreu um erro de comunicação. Tente novamente.');
    });
});

    // =======================================================
    // 3. LÓGICA COMPLETA E REATORADA DA GRADE DE HORÁRIOS
    // =======================================================
    const modalHorarios = $('#modal-horarios');
    const modalSelecao = $('#modal-selecao-aula');
    let slotClicado = null;

    $('#btn-abrir-grade').on('click', () => modalHorarios.fadeIn(200).css('display', 'flex'));
    $('#fechar-modal-horarios, #btn-concluir-horarios').on('click', () => modalHorarios.fadeOut(200));
    $('#fechar-modal-selecao').on('click', () => modalSelecao.fadeOut(200));

    modalHorarios.on('click', '.slot-horario', function() {
        slotClicado = $(this);
        const dia = slotClicado.data('dia');
        const horaInicio = slotClicado.data('hora-inicio');
        
        $('#selecao-dia-semana').val(dia);
        $('#selecao-hora-inicio').val(horaInicio);
        $('#selecao-hora-fim').val(slotClicado.data('hora-fim'));
        
        $('#selecao_disciplina').html('<option value="">Selecione...</option>' + disciplinas.map(d => `<option value="${d.id}">${d.nome_disciplina}</option>`).join(''));
        $('#selecao_professor').html('<option value="">Selecione...</option>' + professores.map(p => `<option value="${p.id}">${p.nome_completo}</option>`).join(''));
        
        if (gradeSalva[dia] && gradeSalva[dia][horaInicio]) {
            const aula = gradeSalva[dia][horaInicio];
            $('#selecao_disciplina').val(aula.id_disciplina);
            $('#selecao_professor').val(aula.id_professor);
            $('#remover-horario').show();
        } else {
            $('#remover-horario').hide();
        }
        modalSelecao.fadeIn(200).css('display', 'flex');
    });

    // --- AÇÕES AJAX (FUNÇÃO GENÉRICA E REATORADA) ---
    function enviarAcaoHorario(action, data) {
        data.action = action;
        data.id_turma = idTurma;
        
        return fetch('api_gerenciar_turma.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        }).then(res => res.json());
    }

    $('#salvar-horario').on('click', function() {
        const dadosAula = {
            dia_semana: $('#selecao-dia-semana').val(),
            hora_inicio: $('#selecao-hora-inicio').val(),
            hora_fim: $('#selecao-hora-fim').val(),
            id_disciplina: $('#selecao_disciplina').val(),
            id_professor: $('#selecao_professor').val()
        };
        
        enviarAcaoHorario('salvar_horario', dadosAula)
        .then(result => {
            if (result.success) {
                // ATUALIZA A TELA SEM RECARREGAR
                const nomeDisciplina = $('#selecao_disciplina option:selected').text();
                const nomeProfessor = $('#selecao_professor option:selected').text();
                const novoConteudo = `<strong>${nomeDisciplina}</strong><small>${nomeProfessor}</small>`;
                
                if (slotClicado) {
                    slotClicado.html(novoConteudo).addClass('slot-preenchido');
                }
                
                // Atualiza o estado local da grade
                gradeSalva[dadosAula.dia_semana] = gradeSalva[dadosAula.dia_semana] || {};
                gradeSalva[dadosAula.dia_semana][dadosAula.hora_inicio] = {
                    nome_disciplina: nomeDisciplina, nome_professor: nomeProfessor,
                    id_disciplina: dadosAula.id_disciplina, id_professor: dadosAula.id_professor
                };
                
                modalSelecao.fadeOut(200);
            } else {
                alert('Erro: ' + result.message);
            }
        });
    });

    $('#remover-horario').on('click', function() {
        if (!confirm('Tem certeza que deseja remover esta aula da grade?')) return;
        
        const dadosAula = {
            dia_semana: $('#selecao-dia-semana').val(),
            hora_inicio: $('#selecao-hora-inicio').val()
        };

        enviarAcaoHorario('remover_horario', dadosAula)
        .then(result => {
            if (result.success) {
                // ATUALIZA A TELA SEM RECARREGAR
                if (slotClicado) {
                    slotClicado.html('').removeClass('slot-preenchido');
                }
                delete gradeSalva[dadosAula.dia_semana][dadosAula.hora_inicio];
                modalSelecao.fadeOut(200);
            } else {
                alert('Erro: ' + result.message);
            }
        });
    });
});
</script>