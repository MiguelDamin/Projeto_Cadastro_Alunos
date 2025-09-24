<?php
// Define o tempo de vida do cookie da sessão para 2 horas
$tempo_limite_sessao = 7200;
session_set_cookie_params($tempo_limite_sessao);

session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['step'])) {
    header('Location: cadastro_geral.php');
    exit;
}

$step = $_POST['step'];

if ($step == 1) {
    // --- PROCESSANDO DADOS DO PASSO 1 (RESPONSÁVEL) ---
    
    // Limpa o CEP para salvar apenas números
    $cep_limpo = preg_replace('/[^0-9]/', '', $_POST['cep_resp'] ?? '');

    // Guarda TODOS os dados do responsável na sessão
    $_SESSION['dados_responsavel'] = [
        'nome_completo_resp' => $_POST['nome_completo_resp'],
        'cpf_resp' => $_POST['cpf_resp'],
        'grau_parentesco_resp' => $_POST['grau_parentesco_resp'],
        'email_resp' => $_POST['email_resp'],
        'telefone_resp' => $_POST['telefone_resp'],
        'cep_resp' => $cep_limpo,
        'logradouro_resp' => $_POST['logradouro_resp'],
        'numero_resp' => $_POST['numero_resp'],
        'complemento_resp' => $_POST['complemento_resp'],
        'bairro_resp' => $_POST['bairro_resp'],
        'cidade_resp' => $_POST['cidade_resp'],
        'uf_resp' => $_POST['uf_resp']
    ];

    // Avança para o próximo passo
    $_SESSION['step'] = 2;
    header('Location: cadastro_geral.php');
    exit;

} elseif ($step == 2) {
    // Recupera os dados do responsável da sessão
    $dados_responsavel = $_SESSION['dados_responsavel'];
    
    // Pega os dados do aluno do formulário
    $nome_aluno = $_POST['nome_completo_aluno'];
    $data_nascimento_br = $_POST['data_nascimento_aluno'];
    
    // Converte a data do formato dd/mm/YYYY para YYYY-mm-dd para o banco de dados
    try {
        $data_nascimento_aluno = DateTime::createFromFormat('d/m/Y', $data_nascimento_br)->format('Y-m-d');
    } catch (Exception $e) {
        // Se a data for inválida, define como null ou lida com o erro
        $data_nascimento_aluno = null; 
    }
    $email_aluno = $_POST['email_aluno'] ?: null;
    $cpf_aluno = $_POST['cpf_aluno'] ?: null;
    $caminho_foto = null; // Inicia a variável da foto como nula

    // --- LÓGICA DE UPLOAD DA FOTO ---
    // Verifica se um arquivo foi enviado (UPLOAD_ERR_NO_FILE significa que o campo foi deixado em branco)
    if (isset($_FILES['foto_aluno']) && $_FILES['foto_aluno']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_dir = 'uploads/fotos_alunos/';

        // VERIFICA SE O DIRETÓRIO DE UPLOAD EXISTE, SE NÃO, TENTA CRIAR
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0775, true)) {
                 error_log("Falha ao criar o diretório de upload: " . $upload_dir . ". Verifique as permissões e o SELinux.");
            }
        }

        // Verifica se houve algum erro no upload (tamanho do arquivo, etc.)
        if ($_FILES['foto_aluno']['error'] === UPLOAD_ERR_OK) {
            $arquivo_tmp = $_FILES['foto_aluno']['tmp_name'];
            $nome_original = $_FILES['foto_aluno']['name'];
            $extensao = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION));
            $extensoes_permitidas = ['jpg', 'jpeg', 'png'];

            if (in_array($extensao, $extensoes_permitidas)) {
                if (is_writable($upload_dir)) {
                    $nome_limpo = strtolower($nome_aluno);
                    $nome_limpo = preg_replace('/[^a-z0-9]+/', '-', $nome_limpo);
                    $nome_limpo = trim($nome_limpo, '-');
                    $novo_nome_arquivo = $nome_limpo . '-' . uniqid() . '.' . $extensao;
                    $caminho_completo = $upload_dir . $novo_nome_arquivo;
                    if (move_uploaded_file($arquivo_tmp, $caminho_completo)) {
                        $caminho_foto = $novo_nome_arquivo;
                    } else {
                        $_SESSION['form_data_aluno'] = $_POST; // Salva dados do form
                        $_SESSION['upload_error'] = "Erro crítico: Não foi possível salvar a foto. Verifique as permissões do servidor (SELinux).";
                        error_log("Falha ao mover o arquivo de upload para: " . $caminho_completo);
                        header('Location: cadastro_geral.php');
                        exit;
                    }
                } else {
                    $_SESSION['form_data_aluno'] = $_POST; // Salva dados do form
                    $_SESSION['upload_error'] = "Erro de configuração: O diretório de upload não tem permissão de escrita.";
                    error_log("Diretório de upload sem permissão de escrita: " . $upload_dir);
                    header('Location: cadastro_geral.php');
                    exit;
                }
            } else {
                $_SESSION['form_data_aluno'] = $_POST; // Salva dados do form
                $_SESSION['upload_error'] = "Tipo de arquivo inválido. Por favor, envie apenas imagens JPG, JPEG ou PNG.";
                header('Location: cadastro_geral.php');
                exit;
            }
        } else {
            $_SESSION['form_data_aluno'] = $_POST; // Salva dados do form
            $_SESSION['upload_error'] = "Ocorreu um erro durante o upload da foto. Verifique o tamanho do arquivo. (Erro: " . $_FILES['foto_aluno']['error'] . ")";
            header('Location: cadastro_geral.php');
            exit;
        }
    }

    $pdo->beginTransaction();
    try {
        // 1. VERIFICA SE O RESPONSÁVEL JÁ EXISTE OU PRECISA SER INSERIDO
        if (isset($dados_responsavel['id_resp']) && !empty($dados_responsavel['id_resp'])) {
            // O responsável já existe, apenas usamos o ID dele
            $id_responsavel = $dados_responsavel['id_resp'];
        } else {
            // O responsável é novo, então fazemos a inserção
            $sql_resp = "INSERT INTO responsaveis (nome_completo, cpf, email, telefone, cep, logradouro, numero, complemento, bairro, cidade, uf, grau_parentesco) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_resp = $pdo->prepare($sql_resp);
            $stmt_resp->execute([
                $dados_responsavel['nome_completo_resp'], $dados_responsavel['cpf_resp'],
                $dados_responsavel['email_resp'], $dados_responsavel['telefone_resp'],
                $dados_responsavel['cep_resp'], $dados_responsavel['logradouro_resp'],
                $dados_responsavel['numero_resp'], $dados_responsavel['complemento_resp'],
                $dados_responsavel['bairro_resp'], $dados_responsavel['cidade_resp'],
                $dados_responsavel['uf_resp'],
                $dados_responsavel['grau_parentesco_resp']
            ]);
            $id_responsavel = $pdo->lastInsertId();
        }

        // 2. INSERE O ALUNO (COM A CORREÇÃO)
        // Adicionamos a coluna `caminho_foto` e um `?` a mais
        $sql_aluno = "INSERT INTO alunos (nome_completo, data_nascimento, email, cpf, id_responsavel_principal, caminho_foto) VALUES (?, ?, ?, ?, ?, ?)"; // <-- MUDANÇA AQUI
        
        $stmt_aluno = $pdo->prepare($sql_aluno);
        
        // Adicionamos a variável $caminho_foto no final do array
        $stmt_aluno->execute([ // <-- MUDANÇA AQUI
            $nome_aluno,
            $data_nascimento_aluno,
            $email_aluno,
            $cpf_aluno,
            $id_responsavel,
            $caminho_foto // A variável com o nome do arquivo da foto
        ]);

        $id_aluno = $pdo->lastInsertId();

        $pdo->commit();

        unset($_SESSION['step']);
        unset($_SESSION['dados_responsavel']);
        unset($_SESSION['form_data_aluno']); // Limpa também os dados do formulário do aluno

        // Redireciona para a nova página de sucesso, passando o ID do aluno
        header("Location: cadastro_concluido.php?id=" . $id_aluno);
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        // Não limpa os dados da sessão para que o usuário possa tentar novamente.
        $_SESSION['form_data_aluno'] = $_POST; // Salva os dados do aluno que falharam
        // Em produção, é melhor uma mensagem genérica. Para debug, $e->getMessage() é útil.
        $_SESSION['upload_error'] = "Erro no banco de dados ao tentar salvar o cadastro. Tente novamente.";
        // Loga o erro real para o desenvolvedor
        error_log("PDOException em processa_cadastro_geral.php: " . $e->getMessage());
        header('Location: cadastro_geral.php');
        exit;
    }
}
?>