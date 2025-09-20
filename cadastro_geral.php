<?php 
// Inclui o header, que já inicia a sessão e verifica o login
require 'templates/header.php'; 
require 'conexao.php';

// Determina qual passo estamos, o padrão é o passo 1
$step = $_SESSION['step'] ?? 1;

// Limpa dados de um cadastro anterior se o usuário voltar ao passo 1
if (isset($_GET['reset'])) {
    unset($_SESSION['dados_responsavel']);
    $step = 1;
    $_SESSION['step'] = 1;
}
?>

<h2>Cadastro de Responsável e Aluno</h2>
<p>Passo <?php echo $step; ?> de 2</p>

<?php
// Exibe o formulário correspondente ao passo atual
if ($step == 1) {
// PASSO 1: FORMULÁRIO DO RESPONSÁVEL (COM A CORREÇÃO NO ID DO CEP)
?>
<h3>Dados do Responsável</h3>
<form action="processa_cadastro_geral.php" method="POST">
    <input type="hidden" name="step" value="1">

    <div class="form-row">
        <div class="form-group">
            <label for="nome_completo_resp">Nome Completo:</label>
            <input type="text" placeholder="Nome completo do responsável" name="nome_completo_resp" id="nome_completo_resp" required>
        </div>
        <div class="form-group">
            <label for="cpf_resp">CPF:</label>
            <input type="text" placeholder="000.000.000-00" name="cpf_resp" id="cpf_resp" required>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="email_resp">E-mail:</label>
            <input type="email" placeholder="email@exemplo.com" name="email_resp" id="email_resp" required>
        </div>
        <div class="form-group">
            <label for="telefone_resp">Telefone:</label>
            <input type="text" placeholder="(00) 90000-0000" name="telefone_resp" id="telefone_resp">
        </div>
    </div>

    <hr>
    <h4>Endereço</h4>

    <div class="form-row">
        <div class="form-group">
            <label for="cep_resp">CEP:</label>
            <input type="text" placeholder="00000-000" name="cep_resp" id="cep_resp" maxlength="9">
        </div>
        <div class="form-group">
            <label for="logradouro_resp">Logradouro (Rua/Avenida):</label>
            <input type="text" placeholder="Preenchido automaticamente" name="logradouro_resp" id="logradouro_resp" required>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="numero_resp">Número:</label>
            <input type="text" name="numero_resp" id="numero_resp" required>
        </div>
        <div class="form-group">
            <label for="complemento_resp">Complemento (opcional):</label>
            <input type="text" name="complemento_resp" id="complemento_resp">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group" style="flex: 2;"> <label for="bairro_resp">Bairro:</label>
            <input type="text" placeholder="Preenchido automaticamente" name="bairro_resp" id="bairro_resp" required>
        </div>
        <div class="form-group" style="flex: 2;"> <label for="cidade_resp">Cidade:</label>
            <input type="text" placeholder="Preenchido automaticamente" name="cidade_resp" id="cidade_resp" required>
        </div>
        <div class="form-group" style="flex: 1;"> <label for="uf_resp">Estado (UF):</label>
            <input type="text" placeholder="UF" name="uf_resp" id="uf_resp" required maxlength="2">
        </div>
    </div>

    <button type="submit">Próximo: Cadastrar Aluno</button>
</form>

<?php
} elseif ($step == 2) {
// PASSO 2: FORMULÁRIO DO ALUNO
?>

    <h3>Dados do Aluno</h3>
    <p>
        <strong>Responsável:</strong> 
        <?php echo htmlspecialchars($_SESSION['dados_responsavel']['nome_completo_resp']); ?>
        (<a href="cadastro_geral.php?reset=1">Alterar</a>)
    </p>

    <form action="processa_cadastro_geral.php" method="POST">
        <input type="hidden" name="step" value="2">

        <label for="nome_completo_aluno">Nome Completo do Aluno:</label>
        <input type="text" placeholder="Nome completo do aluno" name="nome_completo_aluno" id="nome_completo_aluno" required>

        <label for="data_nascimento_aluno">Data de Nascimento:</label>
        <input type="date" placeholder="XX/XX/XXXX" name="data_nascimento_aluno" id="data_nascimento_aluno" required>

        <label for="email_aluno">E-mail (opcional):</label>
        <input type="email" placeholder="Email aluno" name="email_aluno" id="email_aluno">

        <label for="cpf_aluno">CPF (opcional):</label>
        <input type="text" placeholder="XXX.XXX.XXX-XX" name="cpf_aluno" id="cpf_aluno">

        <button type="submit">Finalizar Cadastro</button>
    </form>

<?php
}
?>

<script>
console.log('Script de CEP iniciado e pronto!');

// --- Pega os campos do formulário ---
const inputCep = document.querySelector('#cep_resp');
const inputLogradouro = document.querySelector('#logradouro_resp');
const inputNumero = document.querySelector('#numero_resp');
const inputBairro = document.querySelector('#bairro_resp');
const inputCidade = document.querySelector('#cidade_resp');
const inputUf = document.querySelector('#uf_resp');

// Verifica se o campo do CEP realmente foi encontrado na página
if (inputCep) {
    console.log('Campo do CEP encontrado com sucesso!');

    // --- MÁSCARA DO CEP ---
    inputCep.addEventListener('input', function () {
        let value = inputCep.value.replace(/[^0-9]/g, '');
        if (value.length > 5) {
            value = value.slice(0, 5) + '-' + value.slice(5, 8);
        }
        inputCep.value = value;
    });

    // --- CONSULTA AUTOMÁTICA DO ENDEREÇO ---
    inputCep.addEventListener('blur', function() {
        console.log('EVENTO DE BLUR DISPARADO! O usuário saiu do campo CEP.');

        const cepDigitado = inputCep.value;
        console.log('CEP digitado no campo:', cepDigitado);

        const cepLimpo = cepDigitado.replace(/[^0-9]/g, '');
        console.log('CEP após limpar (só números):', cepLimpo);
        console.log('Tamanho do CEP limpo:', cepLimpo.length);

        // Verifica se o CEP tem 8 dígitos
        if (cepLimpo.length === 8) {
            console.log('Tamanho é 8. Preparando para chamar a API...');
            
            const url = `https://viacep.com.br/ws/${cepLimpo}/json/`;
            console.log('URL da API:', url);

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    console.log('Dados recebidos da API:', data);
                    if (data.erro) {
                        alert('CEP não encontrado. Por favor, verifique.');
                    } else {
                        console.log('Preenchendo os campos do endereço...');
                        inputLogradouro.value = data.logradouro;
                        inputBairro.value = data.bairro;
                        inputCidade.value = data.localidade;
                        inputUf.value = data.uf;
                        inputNumero.focus();
                    }
                })
                .catch(error => {
                    console.error('Houve um erro GRAVE ao chamar a API:', error);
                });
        } else {
            console.log('Tamanho do CEP não é 8. A chamada para a API foi cancelada.');
        }
    });

} else {
    console.error('ERRO CRÍTICO: Não foi possível encontrar o campo com id="cep_resp" na página!');
}
</script>

<?php require '../templates/footer.php'; ?>