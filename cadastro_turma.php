<?php
// cadastro_turma.php (VERSÃO REATORADA)
session_start();
require_once 'templates/header.php';
require_once 'conexao.php';

// Busca dados para os dropdowns
$niveis_ensino = $pdo->query("SELECT id, nome FROM niveis_ensino ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$series = $pdo->query("SELECT id, nome FROM series ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Pega dados antigos em caso de erro de validação
$dados_antigos = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
?>

<div class="form-card">
    <div class="page-top-header">
        <div>
            <h2>Criar Nova Turma</h2>
            <p>Preencha os dados essenciais para criar a turma. Os detalhes como horários e alunos serão adicionados a seguir.</p>
        </div>
        <a href="gerenciar_turmas.php" class="back-link"><i class="fas fa-arrow-left"></i> Voltar para Lista</a>
    </div>

    <?php if (isset($_SESSION['form_errors'])): ?>
        <div class="form-message error spaced">
            <strong>Ocorreram erros:</strong>
            <ul>
                <?php foreach ($_SESSION['form_errors'] as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['form_errors']); ?>
    <?php endif; ?>

    <form action="actions/turma_criar.php" method="POST">
        <fieldset>
            <legend>1. Identificação e Estrutura</legend>
            <div class="form-row">
                <div class="form-group flex-2">
                    <label for="nome_turma">Nome da Turma *</label>
                    <input type="text" name="nome_turma" id="nome_turma" required value="<?php echo htmlspecialchars($dados_antigos['nome_turma'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="ano_letivo">Ano Letivo *</label>
                    <input type="number" name="ano_letivo" id="ano_letivo" value="<?php echo htmlspecialchars($dados_antigos['ano_letivo'] ?? date('Y')); ?>" required>
                </div>
            </div>
             <div class="form-row">
                <div class="form-group">
                    <label for="id_nivel_ensino">Nível de Ensino *</label>
                    <select name="id_nivel_ensino" id="id_nivel_ensino" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($niveis_ensino as $nivel): ?>
                            <option value="<?php echo $nivel['id']; ?>" <?php echo (($dados_antigos['id_nivel_ensino'] ?? '') == $nivel['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($nivel['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="id_serie">Série / Ano *</label>
                    <select name="id_serie" id="id_serie" required disabled>
                        <option value="">Primeiro, selecione o Nível de Ensino</option>
                    </select>
                </div>
            </div>
             <div class="form-row">
                <div class="form-group">
                    <label for="turno">Turno *</label>
                    <select name="turno" id="turno" required>
                        <option value="">Selecione...</option>
                        <?php $turno_selecionado = $dados_antigos['turno'] ?? ''; ?>
                        <option value="Matutino" <?php echo ($turno_selecionado == 'Matutino') ? 'selected' : ''; ?>>Manhã</option>
                        <option value="Vespertino" <?php echo ($turno_selecionado == 'Vespertino') ? 'selected' : ''; ?>>Tarde</option>
                        <option value="Noturno" <?php echo ($turno_selecionado == 'Noturno') ? 'selected' : ''; ?>>Noite</option>
                        <option value="Integral" <?php echo ($turno_selecionado == 'Integral') ? 'selected' : ''; ?>>Integral</option>
                </select>
                </div>
                <div class="form-group">
                    <label for="numero_maximo_alunos">Nº Máximo de Alunos</label>
                    <input type="number" name="numero_maximo_alunos" id="numero_maximo_alunos" min="1" value="<?php echo htmlspecialchars($dados_antigos['numero_maximo_alunos'] ?? '30'); ?>">
                </div>
            </div>
        </fieldset>
        <div class="form-actions">
            <button type="submit" class="btn-primary">Criar Turma e Continuar</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Seleciona os dois dropdowns
    const selectNivel = document.getElementById('id_nivel_ensino');
    const selectSerie = document.getElementById('id_serie');

    // Garante que os elementos existem na página
    if (selectNivel && selectSerie) {
        
        // Adiciona um "ouvinte" para o evento de MUDANÇA no select de Nível
        selectNivel.addEventListener('change', function() {
            const nivelId = this.value; // Pega o ID do nível de ensino selecionado

            // Limpa e desabilita o select de séries enquanto busca
            selectSerie.innerHTML = '<option value="">Carregando séries...</option>';
            selectSerie.disabled = true;

            // Se o usuário selecionar "Selecione...", reseta o campo de séries
            if (!nivelId) {
                selectSerie.innerHTML = '<option value="">Primeiro, selecione o Nível de Ensino</option>';
                return;
            }

            // Faz a chamada AJAX para a nossa API
            fetch(`api_buscar_series.php?id_nivel=${nivelId}`)
                .then(response => response.json())
                .then(series => {
                    // Limpa a mensagem "Carregando..."
                    selectSerie.innerHTML = '<option value="">Selecione a série...</option>';

                    // Preenche o select de séries com os dados recebidos do PHP
                    series.forEach(serie => {
                        const option = document.createElement('option');
                        option.value = serie.id;
                        option.textContent = serie.nome;
                        selectSerie.appendChild(option);
                    });

                    // Habilita o select de séries para o usuário
                    selectSerie.disabled = false;
                })
                .catch(error => {
                    console.error('Erro ao buscar séries:', error);
                    selectSerie.innerHTML = '<option value="">Erro ao carregar séries</option>';
                });
        });
    }
});
</script>

<?php require 'templates/footer.php'; ?>