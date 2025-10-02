<?php
require 'conexao.php';
require 'templates/header.php';

// (O código de processamento do POST virá aqui em cima)

// Consulta para buscar as disciplinas
try {
    $sql_disciplinas = "SELECT id, nome_disciplina FROM disciplinas ORDER BY nome_disciplina ASC";
    $result_disciplinas = $pdo->query($sql_disciplinas)->fetchAll();
} catch (PDOException $e) {
    $result_disciplinas = [];
    $erro_disciplinas = "Erro ao carregar disciplinas: " . $e->getMessage();
}
?>

<h2>Cadastrar Novo Professor</h2>

<!-- Mensagens de erro ou sucesso (coloque isso antes do formulário) -->
<?php if (isset($_GET['erro'])): ?>
    <div class="alerta erro">Erro: <?php echo htmlspecialchars($_GET['erro']); ?></div>
<?php endif; ?>
<?php if (isset($_GET['sucesso'])): ?>
    <div class="alerta sucesso">Professor cadastrado com sucesso!</div>
<?php endif; ?>
<form method="POST" action="processa_cadastro_professor.php" class="form-cadastro">
    
    <!-- TELA 1: INFORMAÇÕES PESSOAIS -->
    <fieldset>
        <legend>1. Informações Pessoais</legend>
        <div class="form-row">
            <div class="form-group full-width">
                <label for="nome_completo">Nome Completo *</label>
                <input type="text" id="nome_completo" name="nome_completo" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="cpf">CPF *</label>
                <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" required maxlength="14">
            </div>
            <div class="form-group">
                <label for="rg">RG</label>
                <input type="text" id="rg" name="rg" placeholder="000.000.000.0" maxlength="13">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="data_nascimento">Data de Nascimento *</label>
                <input type="date" id="data_nascimento" name="data_nascimento" required>
            </div>
            <div class="form-group">
                <label for="genero">Gênero</label>
                <select id="genero" name="genero">
                    <option value="">Selecione...</option>
                    <option value="Masculino">Masculino</option>
                    <option value="Feminino">Feminino</option>
                    <option value="Outro">Outro</option>
                    <option value="NaoInformar">Prefiro não informar</option>
                </select>
            </div>
        </div>
    </fieldset>

    <!-- TELA 2: INFORMAÇÕES DE CONTATO -->
    <fieldset>
        <legend>2. Informações de Contato</legend>
        <div class="form-row">
            <div class="form-group">
                <label for="cep">CEP</label>
                <input type="text" id="cep" name="cep" placeholder="00000-000" maxlength="9" required>
            </div>
             <div class="form-group">
                <label for="logradouro">Logradouro (Rua, Av.)</label>
                <input type="text" id="logradouro" name="logradouro">
            </div>
            <div class="form-group">
                <label for="numero">Número</label>
                <input type="text" id="numero" name="numero">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="complemento">Complemento</label>
                <input type="text" id="complemento" name="complemento">
            </div>
            <div class="form-group">
                <label for="bairro">Bairro</label>
                <input type="text" id="bairro" name="bairro">
            </div>
            <div class="form-group">
                <label for="cidade">Cidade</label>
                <input type="text" id="cidade" name="cidade">
            </div>
            <div class="form-group">
                <label for="estado">Estado (UF)</label>
                <input type="text" id="estado" name="estado" maxlength="2">
            </div>
        </div>
         <div class="form-row">
            <div class="form-group">
                <label for="telefone_celular">Celular / WhatsApp *</label>
                <input type="text" id="telefone_celular" name="telefone_celular" placeholder="(00) 00000-0000" maxlength="15" required>
            </div>
            <div class="form-group">
                <label for="email_principal">Email Principal *</label>
                <input type="email" id="email_principal" name="email_principal" required>
            </div>
        </div>
    </fieldset>

    <!-- TELA 3: INFORMAÇÕES PROFISSIONAIS -->
    <fieldset>
        <legend>3. Informações Profissionais e Acadêmicas</legend>
        <div class="form-row">
            <div class="form-group">
                <label for="numero_matricula">Nivel de Escolaridade</label>
                <input type="text" id="nivel_escolaridade" name="nivel_escolaridade" placeholder="Ex: Graduado em Matemática" required>
            </div>
            <div class="form-group">
                <label for="data_admissao">Data de Admissão </label>
                <input type="date" id="data_admissao" name="data_admissao" required>
            </div>
            <div class="form-group">
                <label for="id_disciplina">Disciplina Principal</label>
                <select name="id_disciplina" id="id_disciplina" required>
                    <option value="">Selecione uma disciplina...</option>
                    <?php foreach ($result_disciplinas as $disciplina): ?>
                        <option value="<?php echo $disciplina['id']; ?>">
                            <?php echo htmlspecialchars($disciplina['nome_disciplina']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </fieldset>

    <button type="submit" name="cadastrar_professor">Cadastrar Professor</button>
</form>

<!-- Adicione este script no final do seu body ou no footer.php -->
<!-- NOVO SCRIPT COMPLETO - USE ESTE BLOCO -->
<script>
// Função para aplicar máscara de CEP
document.getElementById('cep').addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito
    value = value.replace(/^(\d{5})(\d)/, '$1-$2'); // Adiciona o traço
    e.target.value = value;
});

// Função principal para buscar o CEP
document.getElementById('cep').addEventListener('blur', function() {
    const cep = this.value.replace(/\D/g, '');
    if (cep.length !== 8) {
        return;
    }
    setAddressFieldsReadOnly(true, 'Buscando...');
    fetch(`https://viacep.com.br/ws/${cep}/json/` )
        .then(response => response.json())
        .then(data => {
            if (data.erro) {
                console.error('CEP não encontrado.');
                clearAddressFields();
                setAddressFieldsReadOnly(false, '');
                alert('CEP não encontrado. Por favor, preencha o endereço manualmente.');
            } else {
                document.getElementById('logradouro').value = data.logradouro;
                document.getElementById('bairro').value = data.bairro;
                document.getElementById('cidade').value = data.localidade;
                document.getElementById('estado').value = data.uf;
                setAddressFieldsReadOnly(false, '');
                document.getElementById('numero').focus();
            }
        })
        .catch(error => {
            console.error('Erro ao buscar CEP:', error);
            setAddressFieldsReadOnly(false, '');
            alert('Não foi possível buscar o CEP. Verifique sua conexão com a internet.');
        });
});

// Funções auxiliares para melhorar a experiência do usuário
function setAddressFieldsReadOnly(isReadOnly, placeholderText) {
    const fields = ['logradouro', 'bairro', 'cidade', 'estado'];
    fields.forEach(id => {
        const field = document.getElementById(id);
        field.readOnly = isReadOnly;
        field.style.backgroundColor = isReadOnly ? '#e9ecef' : '#fff';
        field.placeholder = placeholderText;
        if (!isReadOnly) field.placeholder = ''; // Limpa o placeholder ao liberar
    });
}

function clearAddressFields() {
    const fields = ['logradouro', 'bairro', 'cidade', 'estado'];
    fields.forEach(id => {
        document.getElementById(id).value = '';
    });
}

// Máscara para CPF
document.getElementById('cpf').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    e.target.value = value;
});

// Máscara para Telefone Celular
document.getElementById('telefone_celular').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
    value = value.replace(/(\d{5})(\d)/, '$1-$2');
    e.target.value = value;
});
</script>


<style>
/* Estilos para melhorar a aparência do formulário */
.form-cadastro { background: #f8f9fa; padding: 20px; border-radius: 8px; }
fieldset { border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 5px; }
legend { font-weight: bold; font-size: 1.2em; color: #007bff; }
.form-row { display: flex; gap: 20px; margin-bottom: 15px; flex-wrap: wrap; }
.form-group { flex: 1; min-width: 250px; }
.form-group.full-width { flex-basis: 100%; }
label { display: block; margin-bottom: 5px; font-weight: bold; }
input[type="text"], input[type="email"], input[type="date"], select {
    width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;
}
.alerta { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
.alerta.erro { background: #f48d7d; color: #721c24; }
.alerta.sucesso { background: #d4edda; color: #155724; }
</style>

<?php require 'templates/footer.php'; ?>
