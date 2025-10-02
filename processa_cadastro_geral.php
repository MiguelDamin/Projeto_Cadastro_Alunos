<?php
session_start();
require_once 'conexao.php';

// --- VERIFICAÇÕES DE SEGURANÇA ---
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cadastro_geral.php');
    exit;
}

$step = (int)($_POST['step'] ?? 0);

if ($step === 1) {
    // --- PROCESSA O PASSO 1: DADOS DO RESPONSÁVEL ---
    
    // Coleta e armazena os dados do responsável na sessão
    $_SESSION['dados_responsavel'] = $_POST;
    
    // Validação básica (pode ser mais robusta)
    if (empty($_POST['nome_completo_resp']) || empty($_POST['cpf_resp']) || empty($_POST['email_resp'])) {
        $_SESSION['upload_error'] = "Por favor, preencha todos os campos obrigatórios do responsável.";
        header('Location: cadastro_geral.php');
        exit;
    }
    
    // Avança para o próximo passo
    $_SESSION['step'] = 2;
    header('Location: cadastro_geral.php');
    exit;

} elseif ($step === 2) {
    // --- PROCESSA O PASSO 2: DADOS DO ALUNO E FINALIZAÇÃO ---
    
    // Guarda os dados do aluno na sessão para repopular em caso de erro
    $_SESSION['form_data_aluno'] = $_POST;

    // Validação dos dados do aluno
    if (empty($_POST['nome_completo_aluno']) || empty($_POST['data_nascimento_aluno']) || empty($_POST['cpf_aluno'])) {
        $_SESSION['upload_error'] = "Por favor, preencha todos os campos obrigatórios do aluno.";
        header('Location: cadastro_geral.php');
        exit;
    }

    // Inicia a transação para garantir que ambos os cadastros (ou nenhum) sejam feitos
    $pdo->beginTransaction();

    try {
        // --- 1. INSERE OU ATUALIZA O RESPONSÁVEL ---
        $dados_resp = $_SESSION['dados_responsavel'];
        $id_responsavel = $dados_resp['id_resp'] ?? null;

        // Limpa máscaras
        $cpf_resp_limpo = preg_replace('/[^\d]/', '', $dados_resp['cpf_resp']);
        $cep_resp_limpo = preg_replace('/[^\d]/', '', $dados_resp['cep_resp']);

        if ($id_responsavel) {
            // Atualiza um responsável existente
            $sql_resp = "UPDATE responsaveis SET nome_completo=?, cpf=?, grau_parentesco=?, email=?, telefone=?, cep=?, logradouro=?, numero=?, complemento=?, bairro=?, cidade=?, uf=? WHERE id=?";
            $stmt_resp = $pdo->prepare($sql_resp);
            $stmt_resp->execute([
                $dados_resp['nome_completo_resp'], $cpf_resp_limpo, $dados_resp['grau_parentesco_resp'], $dados_resp['email_resp'], $dados_resp['telefone_resp'],
                $cep_resp_limpo, $dados_resp['logradouro_resp'], $dados_resp['numero_resp'], $dados_resp['complemento_resp'], $dados_resp['bairro_resp'], $dados_resp['cidade_resp'], $dados_resp['uf_resp'],
                $id_responsavel
            ]);
        } else {
            // Insere um novo responsável
            $sql_resp = "INSERT INTO responsaveis (nome_completo, cpf, grau_parentesco, email, telefone, cep, logradouro, numero, complemento, bairro, cidade, uf) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_resp = $pdo->prepare($sql_resp);
            $stmt_resp->execute([
                $dados_resp['nome_completo_resp'], $cpf_resp_limpo, $dados_resp['grau_parentesco_resp'], $dados_resp['email_resp'], $dados_resp['telefone_resp'],
                $cep_resp_limpo, $dados_resp['logradouro_resp'], $dados_resp['numero_resp'], $dados_resp['complemento_resp'], $dados_resp['bairro_resp'], $dados_resp['cidade_resp'], $dados_resp['uf_resp']
            ]);
            $id_responsavel = $pdo->lastInsertId();
        }

        // --- 2. PROCESSA UPLOAD DA FOTO (se houver) ---
        $caminho_foto = null;
        if (isset($_FILES['foto_aluno']) && $_FILES['foto_aluno']['error'] == 0) {
            // --- VALIDAÇÕES DO ARQUIVO ---
            $allowed_types = ['image/jpeg', 'image/png'];
            $max_size = 2 * 1024 * 1024; // 2 MB

            if (!in_array($_FILES['foto_aluno']['type'], $allowed_types)) {
                throw new Exception("Formato de arquivo inválido. Apenas JPG e PNG são permitidos.");
            }

            if ($_FILES['foto_aluno']['size'] > $max_size) {
                throw new Exception("O arquivo da foto é muito grande. O tamanho máximo é 2MB.");
            }

            // --- LÓGICA DE UPLOAD ---
            $upload_dir = 'uploads/fotos_alunos/';
            // Garante que o diretório exista e tenha as permissões corretas
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0775, true)) {
                    throw new Exception("Falha ao criar o diretório de uploads. Verifique as permissões do servidor.");
                }
            }

            $file_info = pathinfo($_FILES['foto_aluno']['name']);
            $file_ext = strtolower($file_info['extension']);
            $caminho_foto = uniqid('aluno_', true) . '.' . $file_ext;
            
            if (!move_uploaded_file($_FILES['foto_aluno']['tmp_name'], $upload_dir . $caminho_foto)) {
                // Mensagem de erro mais específica
                throw new Exception("Falha ao mover o arquivo da foto. Verifique as permissões da pasta 'uploads/fotos_alunos/'.");
            }
        }

        // --- 3. INSERE O ALUNO ---
        $dados_aluno = $_SESSION['form_data_aluno'];
        $cpf_aluno_limpo = preg_replace('/[^\d]/', '', $dados_aluno['cpf_aluno']);
        // Converte data dd/mm/yyyy para yyyy-mm-dd
        $data_nasc_formatada = DateTime::createFromFormat('d/m/Y', $dados_aluno['data_nascimento_aluno'])->format('Y-m-d');

        $sql_aluno = "INSERT INTO alunos (nome_completo, data_nascimento, email, cpf, id_responsavel_principal, caminho_foto) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_aluno = $pdo->prepare($sql_aluno);
        $stmt_aluno->execute([
            $dados_aluno['nome_completo_aluno'],
            $data_nasc_formatada,
            $dados_aluno['email_aluno'] ?: null,
            $cpf_aluno_limpo,
            $id_responsavel,
            $caminho_foto
        ]);
        $id_aluno = $pdo->lastInsertId();

        // Se tudo deu certo, confirma a transação
        $pdo->commit();

        // Limpa os dados da sessão para um novo cadastro
        unset($_SESSION['dados_responsavel'], $_SESSION['form_data_aluno'], $_SESSION['step']);

        // Redireciona para a página de sucesso
        header('Location: cadastro_concluido.php?id=' . $id_aluno);
        exit;

    } catch (Exception $e) {
        // Se algo deu errado, desfaz tudo
        $pdo->rollBack();
        
        // Guarda a mensagem de erro e redireciona de volta
        $_SESSION['upload_error'] = "Erro ao finalizar cadastro: " . $e->getMessage();
        header('Location: cadastro_geral.php');
        exit;
    }
    
} else {
    // Se o passo for inválido, redireciona para o início
    header('Location: cadastro_geral.php?reset=1');
    exit;
}
?>