<?php
require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_professor'])) {
    
    // 1. Coletar todos os dados do formulário
    $nome_completo = trim($_POST['nome_completo']);
    $cpf = trim($_POST['cpf']);
    $rg = trim($_POST['rg']);
    $data_nascimento = trim($_POST['data_nascimento']);
    $genero = trim($_POST['genero']);
    
    $cep = trim($_POST['cep']);
    $logradouro = trim($_POST['logradouro']);
    $numero = trim($_POST['numero']);
    $complemento = trim($_POST['complemento']);
    $bairro = trim($_POST['bairro']);
    $cidade = trim($_POST['cidade']);
    $estado = trim($_POST['estado']);
    $telefone_celular = trim($_POST['telefone_celular']);
    $email_principal = trim($_POST['email_principal']);
    
    $numero_matricula = trim($_POST['numero_matricula']);
    $data_admissao = trim($_POST['data_admissao']);
    $id_disciplina = trim($_POST['id_disciplina']);





// Remove tudo que não for número, ponto ou traço.
    $cpf_limpo = preg_replace('/[^\d.-]/', '', $cpf); 

    $cep_limpo = preg_replace('/[^\d]/', '', $cep);
    $telefone_celular_limpo = preg_replace('/[^\d]/', '', $telefone_celular);
    


    // 2. Validações essenciais
    if (empty($nome_completo) || empty($cpf) || empty($email_principal) || empty($numero_matricula)) {
        header("Location: cadastro_professor.php?erro=Campos obrigatórios não preenchidos.");
        exit();
    }
    
    // Validação de formato de e-mail
    if (!filter_var($email_principal, FILTER_VALIDATE_EMAIL)) {
        header("Location: cadastro_professor.php?erro=Formato de e-mail inválido.");
        exit();
    }

    try {
        // 3. Verificar se CPF, E-mail ou Matrícula já existem
        $stmt_check = $pdo->prepare("SELECT cpf FROM professores WHERE cpf = ? OR email_principal = ? OR numero_matricula = ?");
        $stmt_check->execute([$cpf, $email_principal, $numero_matricula]);
        if ($stmt_check->fetch()) {
            header("Location: cadastro_professor.php?erro=CPF, e-mail ou matrícula já cadastrado.");
            exit();
        }

        // 4. Preparar o SQL para inserção
        $sql = "INSERT INTO professores (
                    nome_completo, cpf, rg, data_nascimento, genero,
                    cep, logradouro, numero, complemento, bairro, cidade, estado,
                    telefone_celular, email_principal,
                    numero_matricula, data_admissao, id_disciplina
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )";
        
        $stmt = $pdo->prepare($sql);

        // 5. Executar a inserção com todos os parâmetros
        $resultado = $stmt->execute([
            $nome_completo, $cpf, $rg, $data_nascimento, $genero,
            $cep, $logradouro, $numero, $complemento, $bairro, $cidade, $estado,
            $telefone_celular, $email_principal,
            $numero_matricula, $data_admissao, $id_disciplina
        ]);

        // 6. Redirecionar com base no resultado
        if ($resultado) {
            header("Location: cadastro_professor.php?sucesso=1");
        } else {
            header("Location: cadastro_professor.php?erro=Falha ao salvar no banco de dados.");
        }

    } catch (PDOException $e) {
        error_log("Erro no cadastro de professor: " . $e->getMessage());
        header("Location: cadastro_professor.php?erro=Erro de banco de dados. Contate o administrador.");
    }
    exit();

} else {
    // Redireciona se o acesso não for via POST
    header("Location: cadastro_professor.php");
    exit();
}
?>
