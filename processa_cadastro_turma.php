<?php
session_start();
require_once 'conexao.php';

// --- Lógica para Requisições AJAX ---
// AJAX envia dados como JSON, então precisamos ler o "corpo" da requisição
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['action']) && $input['action'] === 'salvar_horario') {
    // Inicializa o array de horários na sessão se não existir
    if (!isset($_SESSION['cadastro_turma_dados']['horarios'])) {
        $_SESSION['cadastro_turma_dados']['horarios'] = [];
    }
    // Adiciona o novo horário à sessão
    $_SESSION['cadastro_turma_dados']['horarios'][] = [
        'dia_semana' => $input['dia_semana'],
        'hora_inicio' => $input['hora_inicio'],
        'id_disciplina' => $input['id_disciplina'],
        'id_professor' => $input['id_professor']
    ];
    
    // Retorna uma resposta JSON para o JavaScript
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Horário adicionado à sessão.']);
    exit; // Termina o script aqui para requisições AJAX
}

// --- Lógica para Formulários Normais (POST) ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['step'])) {
    // ... (resto do seu código, sem alterações)
}
// Inicia a sessão para podermos armazenar os dados entre os passos.
session_start();

// Inclui a conexão com o banco de dados.
require_once 'conexao.php';

// --- VERIFICAÇÕES DE SEGURANÇA INICIAIS ---

// Garante que o usuário está logado para realizar esta ação.
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Garante que o script só seja acessado via método POST e que o passo foi enviado.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['step'])) {
    header('Location: cadastro_turma.php?reset=1');
    exit;
}

// Inicializa o array da turma na sessão se ele ainda não existir.
if (!isset($_SESSION['cadastro_turma_dados'])) {
    $_SESSION['cadastro_turma_dados'] = [];
}

$step = (int)$_POST['step'];
$_SESSION['cadastro_turma_dados']['step'] = $step; // Armazena o passo atual

// --- LÓGICA DE PROCESSAMENTO PARA CADA PASSO ---

switch ($step) {
    case 1: // Recebendo dados da Tela 1: Identificação da Turma
        if (empty($_POST['nome_turma']) || empty($_POST['ano_letivo'])) {
            die("Erro: Nome da Turma e Ano Letivo são obrigatórios.");
        }
        $_SESSION['cadastro_turma_dados']['nome_turma'] = trim($_POST['nome_turma']);
        $_SESSION['cadastro_turma_dados']['ano_letivo'] = (int)$_POST['ano_letivo'];
        $_SESSION['cadastro_turma_dados']['codigo_turma'] = trim($_POST['codigo_turma']);
        $_SESSION['cadastro_turma_dados']['step'] = 2;
        break;

    case 2: // Recebendo dados da Tela 2: Estrutura da Turma
        if (empty($_POST['id_nivel_ensino']) || empty($_POST['id_serie']) || empty($_POST['turno'])) {
            die("Erro: Nível de Ensino, Série/Ano e Turno são obrigatórios.");
        }

        $stmt = $pdo->prepare("SELECT nome FROM niveis_ensino WHERE id = ?");
        $stmt->execute([(int)$_POST['id_nivel_ensino']]);
        $nivel_nome = $stmt->fetchColumn();

        $_SESSION['cadastro_turma_dados']['id_nivel_ensino'] = (int)$_POST['id_nivel_ensino'];
        $_SESSION['cadastro_turma_dados']['nivel_ensino_nome'] = $nivel_nome;
        $_SESSION['cadastro_turma_dados']['id_serie'] = (int)$_POST['id_serie'];
        $_SESSION['cadastro_turma_dados']['turno'] = $_POST['turno'];
        $_SESSION['cadastro_turma_dados']['max_alunos'] = (int)$_POST['numero_maximo_alunos'];
        
        $ano = $_SESSION['cadastro_turma_dados']['ano_letivo'];
        $nivelSigla = strtoupper(implode('', array_map(fn($w) => $w[0], explode(' ', $nivel_nome))));
        $nomeSigla = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $_SESSION['cadastro_turma_dados']['nome_turma']));
        $turnoSigla = strtoupper(substr($_SESSION['cadastro_turma_dados']['turno'], 0, 1));
        $_SESSION['cadastro_turma_dados']['codigo_turma'] = "{$ano}-{$nivelSigla}-{$nomeSigla}-{$turnoSigla}";

        $_SESSION['cadastro_turma_dados']['step'] = 3;
        break;

    case 3: // Recebendo dados da Tela 3: Localização e Horários
        if (empty($_POST['id_sala'])) {
            die("Erro: A Sala de Aula Principal é obrigatória.");
        }
        $_SESSION['cadastro_turma_dados']['id_sala'] = (int)$_POST['id_sala'];
        $_SESSION['cadastro_turma_dados']['step'] = 4;
        break;

    case 4:
        // Valida e salva os dados da Tela 4 na sessão
        if (empty($_POST['alunos']) || !is_array($_POST['alunos'])) {
            die("Erro: Você deve selecionar pelo menos um aluno para a turma.");
        }
        // Sanitiza o array para garantir que todos os IDs são números inteiros
        $ids_alunos = array_map('intval', $_POST['alunos']);

        $_SESSION['cadastro_turma_dados']['alunos_matriculados'] = $ids_alunos;
        $_SESSION['cadastro_turma_dados']['step'] = 5; // Prepara para a tela final
        break;

    case 5: // Recebendo dados da Tela 5 e FINALIZANDO o cadastro
        $dados_turma = $_SESSION['cadastro_turma_dados'];
        
        // Coleta os dados finais do formulário
        $dados_turma['data_inicio'] = $_POST['data_inicio'] ?: null;
        $dados_turma['data_fim'] = $_POST['data_fim'] ?: null;
        $dados_turma['status'] = $_POST['status'] ?? 'Aberta';
        $dados_turma['id_professor_regente'] = (int)($_POST['id_professor_regente'] ?? 0);

        // --- TRANSAÇÃO SEGURA NO BANCO DE DADOS ---
        $pdo->beginTransaction();
        try {
            // 1. INSERE OS DADOS PRINCIPAIS NA TABELA `turmas`
            $sql_turma = "INSERT INTO turmas (nome_turma, ano_letivo, periodo, codigo_turma, nivel_ensino, serie_ano, numero_maximo_alunos, data_inicio, data_fim, id_sala_aula, id_professor_regente, status) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_turma = $pdo->prepare($sql_turma);
            $stmt_turma->execute([
                $dados_turma['nome_turma'], $dados_turma['ano_letivo'], $dados_turma['turno'], $dados_turma['codigo_turma'],
                $dados_turma['nivel_ensino_nome'], $dados_turma['id_serie'], $dados_turma['max_alunos'] > 0 ? $dados_turma['max_alunos'] : null,
                $dados_turma['data_inicio'], $dados_turma['data_fim'], $dados_turma['id_sala'],
                $dados_turma['id_professor_regente'] > 0 ? $dados_turma['id_professor_regente'] : null,
                $dados_turma['status']
            ]);
            $id_nova_turma = $pdo->lastInsertId();

            // 2. ASSOCIA AS DISCIPLINAS À TURMA RECÉM-CRIADA
            if (!empty($dados_turma['ids_disciplinas'])) {
                $sql_disciplinas = "INSERT INTO turmas_disciplinas (id_turma, id_disciplina) VALUES (?, ?)";
                $stmt_disciplinas = $pdo->prepare($sql_disciplinas);
                foreach ($dados_turma['ids_disciplinas'] as $id_disciplina) {
                    $stmt_disciplinas->execute([$id_nova_turma, $id_disciplina]);
                }
            }
            
            // 3. MATRICULAR ALUNOS e MONTAR GRADE (serão feitos na tela de "Gerenciar Turma")

            // Se tudo ocorreu bem, confirma as alterações no banco de dados.
            $pdo->commit();

            // Limpa os dados da sessão para o próximo cadastro.
            unset($_SESSION['cadastro_turma_dados']);

            // Redireciona para o painel com uma mensagem de sucesso.
            header('Location: painel.php?sucesso=turma_cadastrada');
            exit;

        } catch (Exception $e) {
            // Se qualquer erro ocorreu, desfaz todas as operações.
            $pdo->rollBack();
            // Limpa a sessão para evitar dados inconsistentes no próximo cadastro.
            unset($_SESSION['cadastro_turma_dados']);
            // Exibe uma mensagem de erro clara. Em produção, isso seria registrado em um log.
            die("Erro ao salvar a turma: " . $e->getMessage());
        }
        
}

// Ao final dos passos 1 a 4, redireciona de volta para o formulário.
header('Location: cadastro_turma.php');
exit;
?>