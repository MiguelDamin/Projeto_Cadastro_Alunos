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
// Exibe mensagens de erro do upload, se houver
if (isset($_SESSION['upload_error'])) {
    // Adicione a classe .error-message ao seu CSS com um fundo vermelho e texto branco
    echo '<div style="background-color: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 20px;">' 
            . htmlspecialchars($_SESSION['upload_error']) . 
         '</div>';
    unset($_SESSION['upload_error']); // Limpa a mensagem para não exibir novamente
}
?>

<?php
// Exibe o formulário correspondente ao passo atual
if ($step == 1) {
// PASSO 1: FORMULÁRIO DO RESPONSÁVEL
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
            <small id="cep-status" style="display: none; color: #666;"></small>
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
        <div class="form-group" style="flex: 2;">
            <label for="bairro_resp">Bairro:</label>
            <input type="text" placeholder="Preenchido automaticamente" name="bairro_resp" id="bairro_resp" required>
        </div>
        <div class="form-group" style="flex: 2;">
            <label for="cidade_resp">Cidade:</label>
            <input type="text" placeholder="Preenchido automaticamente" name="cidade_resp" id="cidade_resp" required>
        </div>
        <div class="form-group" style="flex: 1;">
            <label for="uf_resp">Estado (UF):</label>
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

    <form action="processa_cadastro_geral.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="step" value="2">


        <label for="nome_completo_aluno">Nome Completo do Aluno:</label>
        <input type="text" placeholder="Nome completo do aluno" name="nome_completo_aluno" id="nome_completo_aluno" required><br><br>

        <label for="data_nascimento_aluno">Data de Nascimento:</label>
        <input type="date" placeholder="XX/XX/XXXX" name="data_nascimento_aluno" id="data_nascimento_aluno" required><br><br>

        <label for="email_aluno">E-mail (opcional):</label>
        <input type="email" placeholder="Email aluno" name="email_aluno" id="email_aluno"><br><br>

        <label for="cpf_aluno">CPF:</label>
        <input type="text" placeholder="XXX.XXX.XXX-XX" name="cpf_aluno" id="cpf_aluno"><br><br>

        <label for="foto_aluno">Foto do Aluno (opcional):</label>
    
        <img id="foto-preview" src="#" alt="Preview da foto" style="max-width: 150px; display: none; margin-bottom: 10px; border-radius: 50%;">
        
        <input type="file" name="foto_aluno" id="foto_aluno" accept="image/png, image/jpeg">
        <small>Formatos aceitos: JPG, PNG.</small>
        <br><br>

        <button type="submit">Finalizar Cadastro</button>
    </form>

<?php
}
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Script de CEP iniciado e pronto!');

    // --- Pega os campos do formulário ---
    const inputCep = document.querySelector('#cep_resp');
    const inputLogradouro = document.querySelector('#logradouro_resp');
    const inputNumero = document.querySelector('#numero_resp');
    const inputBairro = document.querySelector('#bairro_resp');
    const inputCidade = document.querySelector('#cidade_resp');
    const inputUf = document.querySelector('#uf_resp');
    const statusElement = document.querySelector('#cep-status');

    // Verifica se estamos no passo 1 (onde tem o formulário do responsável)
    if (inputCep) {
        console.log('Campo do CEP encontrado com sucesso!');

        // --- FUNÇÃO PARA LIMPAR CAMPOS ---
        function limparCamposEndereco() {
            inputLogradouro.value = '';
            inputBairro.value = '';
            inputCidade.value = '';
            inputUf.value = '';
        }

        // --- FUNÇÃO PARA MOSTRAR STATUS ---
        function mostrarStatus(mensagem, tipo = 'info') {
            if (statusElement) {
                statusElement.style.display = 'block';
                statusElement.textContent = mensagem;
                statusElement.style.color = tipo === 'error' ? '#d32f2f' : tipo === 'success' ? '#2e7d32' : '#666';
            }
        }

        function ocultarStatus() {
            if (statusElement) {
                statusElement.style.display = 'none';
            }
        }

        // --- MÁSCARA DO CEP ---
        inputCep.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9]/g, '');
            
            // Aplica a máscara
            if (value.length > 5) {
                value = value.slice(0, 5) + '-' + value.slice(5, 8);
            }
            
            e.target.value = value;
            
            // Limpa os campos se o CEP não estiver completo
            if (value.replace(/[^0-9]/g, '').length < 8) {
                limparCamposEndereco();
                ocultarStatus();
            }
        });

        // --- FUNÇÃO PARA BUSCAR CEP ---
        function buscarCEP(cep) {
            const cepLimpo = cep.replace(/[^0-9]/g, '');
            
            // Verifica se o CEP tem 8 dígitos
            if (cepLimpo.length !== 8) {
                return;
            }

            mostrarStatus('Buscando CEP...', 'info');
            
            const url = `https://viacep.com.br/ws/${cepLimpo}/json/`;
            console.log('Buscando CEP na URL:', url);

            // Desabilita os campos enquanto busca
            const campos = [inputLogradouro, inputBairro, inputCidade, inputUf];
            campos.forEach(campo => campo.disabled = true);

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na requisição');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Dados recebidos da API:', data);
                    
                    // Reabilita os campos
                    campos.forEach(campo => campo.disabled = false);
                    
                    if (data.erro) {
                        mostrarStatus('CEP não encontrado', 'error');
                        limparCamposEndereco();
                    } else {
                        // Verifica se os dados essenciais existem
                        if (!data.localidade || !data.uf) {
                            mostrarStatus('CEP inválido ou incompleto', 'error');
                            limparCamposEndereco();
                            return;
                        }
                        
                        // Preenche os campos
                        inputLogradouro.value = data.logradouro || '';
                        inputBairro.value = data.bairro || '';
                        inputCidade.value = data.localidade || '';
                        inputUf.value = data.uf || '';
                        
                        // Log para debug
                        console.log('Cidade encontrada:', data.localidade);
                        console.log('UF encontrada:', data.uf);
                        
                        mostrarStatus('CEP encontrado: ' + data.localidade + '/' + data.uf, 'success');
                        
                        // Foca no campo número após preencher
                        setTimeout(() => {
                            inputNumero.focus();
                            ocultarStatus();
                        }, 2000);
                        
                        console.log('Campos preenchidos com sucesso!');
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar CEP:', error);
                    // Reabilita os campos em caso de erro
                    campos.forEach(campo => campo.disabled = false);
                    mostrarStatus('Erro ao buscar CEP. Verifique sua conexão.', 'error');
                    limparCamposEndereco();
                });
        }

        // --- EVENTO BLUR (quando sai do campo) ---
        inputCep.addEventListener('blur', function(e) {
            console.log('Usuário saiu do campo CEP');
            const cepDigitado = e.target.value;
            console.log('CEP digitado:', cepDigitado);
            
            if (cepDigitado.trim()) {
                buscarCEP(cepDigitado);
            }
        });

        // --- EVENTO KEYDOWN (Enter no campo CEP) ---
        inputCep.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const cepDigitado = e.target.value;
                if (cepDigitado.trim()) {
                    buscarCEP(cepDigitado);
                }
            }
        });

        // --- MÁSCARA PARA CPF ---
        const inputCpf = document.querySelector('#cpf_resp');
        if (inputCpf) {
            inputCpf.addEventListener('input', function(e) {
                let value = e.target.value.replace(/[^0-9]/g, '');
                
                // Aplica máscara do CPF: 000.000.000-00
                if (value.length > 3) value = value.slice(0, 3) + '.' + value.slice(3);
                if (value.length > 7) value = value.slice(0, 7) + '.' + value.slice(7);
                if (value.length > 11) value = value.slice(0, 11) + '-' + value.slice(11, 13);
                
                e.target.value = value;
            });
        }

        // --- MÁSCARA PARA TELEFONE ---
        const inputTelefone = document.querySelector('#telefone_resp');
        if (inputTelefone) {
            inputTelefone.addEventListener('input', function(e) {
                let value = e.target.value.replace(/[^0-9]/g, '');
                
                // Aplica máscara do telefone: (00) 90000-0000
                if (value.length > 0) value = '(' + value;
                if (value.length > 3) value = value.slice(0, 3) + ') ' + value.slice(3);
                if (value.length > 10) value = value.slice(0, 10) + '-' + value.slice(10, 14);
                
                e.target.value = value;
            });
        }

    } else {
        console.log('Campo CEP não encontrado - provavelmente estamos no passo 2');
    }

    // --- MÁSCARA PARA CPF DO ALUNO (se estiver no passo 2) ---
    const inputCpfAluno = document.querySelector('#cpf_aluno');
    if (inputCpfAluno) {
        inputCpfAluno.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9]/g, '');
            
            if (value.length > 3) value = value.slice(0, 3) + '.' + value.slice(3);
            if (value.length > 7) value = value.slice(0, 7) + '.' + value.slice(7);
            if (value.length > 11) value = value.slice(0, 11) + '-' + value.slice(11, 13);
            
            e.target.value = value;
        });
    }
});
</script>

<script>
    const inputFoto = document.getElementById('foto_aluno');
    const previewFoto = document.getElementById('foto-preview');

    if (inputFoto && previewFoto) {
        inputFoto.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                
                previewFoto.style.display = 'block'; // Mostra o campo da imagem
                
                reader.onload = function(event) {
                    previewFoto.setAttribute('src', event.target.result);
                }
                
                reader.readAsDataURL(file);
            } else {
                previewFoto.style.display = 'none'; // Esconde se nenhum arquivo for selecionado
            }
        });
    }
</script>


<?php require 'templates/footer.php'; ?>