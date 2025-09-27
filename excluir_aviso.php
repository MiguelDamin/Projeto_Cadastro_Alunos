<?php
session_start();

// Proteção: Apenas usuários logados podem acessar esta página.
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Inclui a conexão com o banco de dados.
require 'conexao.php';

// Define o nome da página de destino em uma variável.
// Altere este nome se o seu arquivo principal de avisos tiver um nome diferente.
$pagina_de_retorno = 'adicionar_avisos.php';

// Verifica se um 'id' foi enviado pela URL.
if (isset($_GET['id'])) {
    
    // Pega o ID e garante que é um número inteiro para segurança.
    $id_para_excluir = (int)$_GET['id'];

    try {
        // Prepara o comando SQL para deletar o aviso com o ID recebido.
        $sql = "DELETE FROM avisos WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        // Executa o comando, passando o ID.
        $stmt->execute([$id_para_excluir]);

        // Se chegou até aqui, a exclusão funcionou.
        // Redireciona de volta para a página de avisos com uma mensagem de sucesso.
        // AGORA USANDO A VARIÁVEL $pagina_de_retorno.
        header("Location: " . $pagina_de_retorno . "?sucesso_exclusao=Aviso apagado com sucesso!");
        exit();

    } catch (PDOException $e) {
        // Se deu algum erro no banco de dados...
        // Redireciona de volta com uma mensagem de erro.
        header("Location: " . $pagina_de_retorno . "?erro_exclusao=Falha ao apagar o aviso.");
        exit();
    }

} else {
    // Se alguém acessou este arquivo sem um ID, apenas redireciona de volta.
    header("Location: " . $pagina_de_retorno);
    exit();
}
?>
