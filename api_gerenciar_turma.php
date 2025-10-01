<?php
session_start();
require_once 'conexao.php';
header('Content-Type: application/json'); // Sempre responderá em JSON

// --- Validação e Segurança ---
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403); // Proibido
    echo json_encode(['success' => false, 'message' => 'Acesso negado. Faça login novamente.']);
    exit;
}

// Pega os dados enviados via JavaScript (JSON)
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['action']) || !isset($input['id_turma'])) {
    http_response_code(400); // Requisição Inválida
    echo json_encode(['success' => false, 'message' => 'Requisição inválida.']);
    exit;
}

$action = $input['action'];
$id_turma = (int)$input['id_turma'];

try {
    // Busca a turma para validações de regras de negócio
    $stmt_turma = $pdo->prepare("SELECT numero_maximo_alunos, ano_letivo, periodo FROM turmas WHERE id = ?");
    $stmt_turma->execute([$id_turma]);
    $turma = $stmt_turma->fetch(PDO::FETCH_ASSOC);

    if (!$turma) {
        throw new Exception('Turma não encontrada.');
    }

    // Gerencia as diferentes ações que a página pode solicitar
    switch ($action) {
        case 'matricular_aluno':
            $id_aluno = (int)($input['id_aluno'] ?? 0);
            if ($id_aluno === 0) throw new Exception('Aluno inválido.');

            // [Validação] Lotação da Turma
            $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM matriculas WHERE id_turma = ?");
            $stmt_count->execute([$id_turma]);
            $total_alunos = $stmt_count->fetchColumn();
            if ($turma['numero_maximo_alunos'] > 0 && $total_alunos >= $turma['numero_maximo_alunos']) {
                throw new Exception('A turma já atingiu o número máximo de alunos.');
            }

            // [Validação] Dupla Matrícula
            $sql_check = "SELECT COUNT(*) FROM matriculas m JOIN turmas t ON m.id_turma = t.id WHERE m.id_aluno = ? AND t.ano_letivo = ? AND t.periodo = ?";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([$id_aluno, $turma['ano_letivo'], $turma['periodo']]);
            if ($stmt_check->fetchColumn() > 0) {
                throw new Exception('Este aluno já está matriculado em outra turma no mesmo ano e turno.');
            }

            // Insere a matrícula
            $sql = "INSERT INTO matriculas (id_aluno, id_turma, data_matricula, status) VALUES (?, ?, CURDATE(), 'Ativa')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_aluno, $id_turma]);
            $id_matricula = $pdo->lastInsertId();
            
            // Retorna os dados do aluno para o frontend adicionar na lista
            $stmt_aluno = $pdo->prepare("SELECT id, nome_completo, cpf FROM alunos WHERE id = ?");
            $stmt_aluno->execute([$id_aluno]);
            $aluno_data = $stmt_aluno->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'aluno' => $aluno_data, 'id_matricula' => $id_matricula]);
            break;

        case 'desmatricular_aluno':
            $id_matricula = (int)($input['id_matricula'] ?? 0);
            if ($id_matricula === 0) throw new Exception('Matrícula inválida.');
            
            $sql = "DELETE FROM matriculas WHERE id = ? AND id_turma = ?"; // Segurança extra para garantir que a matrícula é da turma certa
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_matricula, $id_turma]);

            echo json_encode(['success' => true]);
            break;
            
        case 'salvar_detalhes':
            // Recebe todos os dados do formulário de edição
            $nome_turma = trim($input['nome_turma'] ?? '');
            $numero_maximo_alunos = (int)($input['numero_maximo_alunos'] ?? 0);
            $data_inicio = !empty($input['data_inicio']) ? $input['data_inicio'] : null;
            $data_fim = !empty($input['data_fim']) ? $input['data_fim'] : null;
            $status = $input['status'] ?? '';
            $id_professor_regente = (int)($input['id_professor_regente'] ?? 0);
            $descricao = trim($input['descricao'] ?? '');

            if (empty($nome_turma)) throw new Exception('O nome da turma é obrigatório.');

            // Prepara o UPDATE com todas as novas colunas
            $sql = "UPDATE turmas SET 
                        nome_turma = :nome_turma, 
                        numero_maximo_alunos = :max_alunos,
                        data_inicio = :data_inicio,
                        data_fim = :data_fim,
                        status = :status, 
                        id_professor_regente = :id_professor,
                        descricao = :descricao
                    WHERE id = :id_turma";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome_turma' => $nome_turma,
                ':max_alunos' => $numero_maximo_alunos > 0 ? $numero_maximo_alunos : null,
                ':data_inicio' => $data_inicio,
                ':data_fim' => $data_fim,
                ':status' => $status,
                ':id_professor' => $id_professor_regente > 0 ? $id_professor_regente : null,
                ':descricao' => $descricao,
                ':id_turma' => $id_turma
            ]);

            // Busca os dados atualizados para devolver ao frontend e atualizar a tela
            $stmt_turma_atualizada = $pdo->prepare("SELECT t.*, p.nome_completo as nome_professor_regente, s.nome_sala FROM turmas t LEFT JOIN professores p ON t.id_professor_regente = p.id LEFT JOIN salas s ON t.id_sala_aula = s.id WHERE t.id = ?");
            $stmt_turma_atualizada->execute([$id_turma]);
            
            echo json_encode(['success' => true, 'message' => 'Detalhes salvos com sucesso!', 'turma' => $stmt_turma_atualizada->fetch(PDO::FETCH_ASSOC)]);
            break;
            
        // case 'salvar_horario':
        //     // Lógica de "UPSERT": Atualiza se já existe, insere se for novo.
        //     // Para isso, a tabela turmas_horarios precisa de uma chave única em (id_turma, dia_semana, hora_inicio)
        //     $sql = "INSERT INTO turmas_horarios (id_turma, dia_semana, hora_inicio, hora_fim, id_disciplina, id_professor) 
        //             VALUES (?, ?, ?, ?, ?, ?)
        //             ON DUPLICATE KEY UPDATE id_disciplina = VALUES(id_disciplina), id_professor = VALUES(id_professor)";
        //     $stmt = $pdo->prepare($sql);
        //     $stmt->execute([
        //         $id_turma, (int)$input['dia_semana'], $input['hora_inicio'], $input['hora_fim'], 
        //         (int)$input['id_disciplina'], (int)$input['id_professor']
        //     ]);
        //     echo json_encode(['success' => true, 'message' => 'Horário salvo!']);
        //     break;

        case 'salvar_horario':
            $dia = (int)($input['dia_semana'] ?? 0);
            $hora_inicio = $input['hora_inicio'] ?? '';
            $hora_fim = $input['hora_fim'] ?? '';
            $id_disciplina = (int)($input['id_disciplina'] ?? 0);
            $id_professor = (int)($input['id_professor'] ?? 0);

            if (!$dia || !$hora_inicio || !$hora_fim || !$id_disciplina || !$id_professor) {
                throw new Exception('Todos os campos são obrigatórios para salvar o horário.');
            }
            
            // Lógica de "UPSERT": Atualiza se já existe, insere se for novo.
            // Para isso, a tabela turmas_horarios precisa de uma chave única em (id_turma, dia_semana, hora_inicio)
            $sql = "INSERT INTO turmas_horarios (id_turma, dia_semana, hora_inicio, hora_fim, id_disciplina, id_professor) 
                    VALUES (:id_turma, :dia, :h_inicio, :h_fim, :id_disc, :id_prof)
                    ON DUPLICATE KEY UPDATE id_disciplina = VALUES(id_disciplina), id_professor = VALUES(id_professor)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_turma' => $id_turma, ':dia' => $dia, ':h_inicio' => $hora_inicio, ':h_fim' => $hora_fim,
                ':id_disc' => $id_disciplina, ':id_prof' => $id_professor
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Horário salvo com sucesso!']);
            break;
        
        case 'remover_horario':
            $dia = (int)($input['dia_semana'] ?? 0);
            $hora_inicio = $input['hora_inicio'] ?? '';
            if (!$dia || !$hora_inicio) throw new Exception('Dados insuficientes para remover o horário.');

            $sql = "DELETE FROM turmas_horarios WHERE id_turma = ? AND dia_semana = ? AND hora_inicio = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_turma, $dia, $hora_inicio]);

            echo json_encode(['success' => true, 'message' => 'Horário removido com sucesso!']);
            break;

        default:
            throw new Exception('Ação desconhecida.');
    }
} catch (Exception $e) {
    http_response_code(400); // Bad Request (erro do cliente ou de regra de negócio)
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>