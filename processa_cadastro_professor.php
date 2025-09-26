<?php
require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_professor'])) {
    // 1. Coletar todos os dados do formulário
    $nome_completo = trim($_POST['nome_completo'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $rg = trim($_POST['rg'] ?? '');
    $data_nascimento = trim($_POST['data_nascimento'] ?? '');
    $genero = trim($_POST['genero'] ?? '');
    $cep = trim($_POST['cep'] ?? '');
    $logradouro = trim($_POST['logradouro'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $complemento = trim($_POST['complemento'] ?? '');
    $bairro = trim($_POST['bairro'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $telefone_celular = trim($_POST['telefone_celular'] ?? '');
    $email_principal = trim($_POST['email_principal'] ?? '');
    $nivel_escolaridade = trim($_POST['nivel_escolaridade'] ?? '');
    $data_admissao = trim($_POST['data_admissao'] ?? '');
    $id_disciplina = trim($_POST['id_disciplina'] ?? '');

    // Limpar dados
    $cpf_limpo = preg_replace('/[^\d.-]/', '', $cpf);
    $cep_limpo = preg_replace('/[^\d]/', '', $cep);
    $telefone_celular_limpo = preg_replace('/[^\d]/', '', $telefone_celular);

    // DEBUG: Verificar se os dados estão chegando
    error_log("DEBUG - Nome: '$nome_completo', CPF: '$cpf', Email: '$email_principal', Nivel: '$nivel_escolaridade'");
    
    // 2. Validações essenciais
    if (empty($nome_completo) || empty($cpf) || empty($email_principal) || empty($nivel_escolaridade)) {
        $erro = "Campos obrigatórios não preenchidos. ";
        $erro .= "Nome: " . (empty($nome_completo) ? "VAZIO " : "OK ");
        $erro .= "CPF: " . (empty($cpf) ? "VAZIO " : "OK ");
        $erro .= "Email: " . (empty($email_principal) ? "VAZIO " : "OK ");
        $erro .= "Nível: " . (empty($nivel_escolaridade) ? "VAZIO " : "OK ");
        
        header("Location: cadastro_professor.php?erro=" . urlencode($erro));
        exit();
    }

    // Validação de formato de e-mail
    if (!filter_var($email_principal, FILTER_VALIDATE_EMAIL)) {
        header("Location: cadastro_professor.php?erro=" . urlencode("Formato de e-mail inválido."));
        exit();
    }

    try {
        // 3. Verificar se CPF ou E-mail já existem (REMOVIDO numero_matricula)
        $stmt_check = $pdo->prepare("SELECT cpf FROM professores WHERE cpf = ? OR email_principal = ?");
        $stmt_check->execute([$cpf_limpo, $email_principal]);
        
        if ($stmt_check->fetch()) {
            header("Location: cadastro_professor.php?erro=" . urlencode("CPF ou e-mail já cadastrado."));
            exit();
        }

        // 4. Verificar se a disciplina existe
        if (!empty($id_disciplina)) {
            $stmt_disciplina = $pdo->prepare("SELECT id FROM disciplinas WHERE id = ?");
            $stmt_disciplina->execute([$id_disciplina]);
            
            if (!$stmt_disciplina->fetch()) {
                header("Location: cadastro_professor.php?erro=" . urlencode("Disciplina selecionada não existe."));
                exit();
            }
        }

        // 5. Preparar o SQL para inserção
        $sql = "INSERT INTO professores (
            nome_completo, cpf, rg, data_nascimento, genero,
            cep, logradouro, numero, complemento, bairro, cidade, estado,
            telefone_celular, email_principal,
            nivel_escolaridade, data_admissao, id_disciplina
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )";
        
        $stmt = $pdo->prepare($sql);

        // 6. Executar a inserção com todos os parâmetros
        $resultado = $stmt->execute([
            $nome_completo, 
            $cpf_limpo, 
            $rg, 
            $data_nascimento, 
            $genero,
            $cep_limpo, 
            $logradouro, 
            $numero, 
            $complemento, 
            $bairro, 
            $cidade, 
            $estado,
            $telefone_celular_limpo, 
            $email_principal,
            $nivel_escolaridade, 
            $data_admissao, 
            $id_disciplina ?: null // Se vazio, coloca NULL
        ]);

        // 7. Redirecionar com base no resultado
        if ($resultado) {
            header("Location: cadastro_professor.php?sucesso=1");
        } else {
            header("Location: cadastro_professor.php?erro=" . urlencode("Falha ao salvar no banco de dados."));
        }

    } catch (PDOException $e) {
        error_log("Erro no cadastro de professor: " . $e->getMessage());
        header("Location: cadastro_professor.php?erro=" . urlencode("Erro de banco de dados: " . $e->getMessage()));
    }
    
    exit();
    
} else {
    // Redireciona se o acesso não for via POST
    header("Location: cadastro_professor.php");
    exit();
}
?>