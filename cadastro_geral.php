<?php 
// Inclui o header, que já inicia a sessão e verifica o login
require 'templates/header.php'; 
require 'conexao.php';

// --- LÓGICA DE NAVEGAÇÃO E DADOS DO FORMULÁRIO MULTI-ETAPAS ---

// Se o usuário clicar em "Novo Cadastro" (reset=1), limpa todos os dados da sessão.
if (isset($_GET['reset'])) {
    unset($_SESSION['dados_responsavel'], $_SESSION['form_data_aluno'], $_SESSION['step']);
    // Redireciona para a URL limpa para evitar re-reset no refresh.
    header('Location: cadastro_geral.php');
    exit;
}

// Permite voltar para um passo específico (usado no link "Alterar" do passo 2).
if (isset($_GET['back_to_step'])) {
    $_SESSION['step'] = (int)$_GET['back_to_step'];
}

// Determina qual passo estamos, o padrão é o passo 1
$step = $_SESSION['step'] ?? 1;

// Pega dados de sessões anteriores para repopular os formulários.
$dados_responsavel = $_SESSION['dados_responsavel'] ?? [];
$form_data_aluno = $_SESSION['form_data_aluno'] ?? [];

// Pega e limpa mensagens de erro da sessão para que não sejam exibidas novamente.
$form_error = $_SESSION['upload_error'] ?? null;
unset($_SESSION['upload_error']);

?>

<h2>Cadastro de Responsável e Aluno</h2>
<p>Passo <?php echo $step; ?> de 2</p>

<?php
// Exibe mensagens de erro, se houver.
if ($form_error) {
    echo '<div class="form-message error">❌ ' . htmlspecialchars($form_error) . '</div>';
}
?>


<!-- // Exibe o formulário correspondente ao passo atual -->
<?php if ($step == 1): // PASSO 1: FORMULÁRIO DO RESPONSÁVEL ?>
<div class="form-card">
    <form action="processa_cadastro_geral.php" method="POST">
        <input type="hidden" name="step" value="1">
        
        <h3>Dados do Responsável</h3>
        <p>Preencha as informações do responsável pelo aluno que será cadastrado.</p>
        
        <h4>Dados Pessoais</h4>
        <div class="form-row">
            <div class="form-group">
                <label for="nome_completo_resp">Nome Completo *</label>
                <input type="text" placeholder="Digite o nome completo" name="nome_completo_resp" id="nome_completo_resp" value="<?php echo htmlspecialchars($dados_responsavel['nome_completo_resp'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="cpf_resp">CPF *</label>
                <input type="text" placeholder="000.000.000-00" name="cpf_resp" id="cpf_resp" value="<?php echo htmlspecialchars($dados_responsavel['cpf_resp'] ?? ''); ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="email_resp">E-mail *</label>
                <input type="email" placeholder="email@exemplo.com" name="email_resp" id="email_resp" value="<?php echo htmlspecialchars($dados_responsavel['email_resp'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="telefone_resp">Telefone</label>
                <input type="text" placeholder="(00) 90000-0000" name="telefone_resp" id="telefone_resp" value="<?php echo htmlspecialchars($dados_responsavel['telefone_resp'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="grau_parentesco_resp">Grau de Parentesco *</label>
                <select name="grau_parentesco_resp" id="grau_parentesco_resp" required>
                    <option value="">Selecione...</option>
                    <option value="Pai" <?php echo (($dados_responsavel['grau_parentesco_resp'] ?? '') === 'Pai') ? 'selected' : ''; ?>>Pai</option>
                    <option value="Mãe" <?php echo (($dados_responsavel['grau_parentesco_resp'] ?? '') === 'Mãe') ? 'selected' : ''; ?>>Mãe</option>
                    <option value="Avô/Avó" <?php echo (($dados_responsavel['grau_parentesco_resp'] ?? '') === 'Avô/Avó') ? 'selected' : ''; ?>>Avô/Avó</option>
                    <option value="Tio/Tia" <?php echo (($dados_responsavel['grau_parentesco_resp'] ?? '') === 'Tio/Tia') ? 'selected' : ''; ?>>Tio/Tia</option>
                    <option value="Outro" <?php echo (($dados_responsavel['grau_parentesco_resp'] ?? '') === 'Outro') ? 'selected' : ''; ?>>Outro</option>
                </select>
            </div>
        </div>

        <h4>Endereço</h4>
        <div class="form-row">
            <div class="form-group">
                <label for="cep_resp">CEP *</label>
                <input type="text" placeholder="00000-000" name="cep_resp" id="cep_resp" value="<?php echo htmlspecialchars($dados_responsavel['cep_resp'] ?? ''); ?>" maxlength="9" required>
            </div>
            <div class="form-group flex-2">
                <label for="logradouro_resp">Logradouro (Rua/Avenida) *</label>
                <input type="text" placeholder="Preenchido automaticamente" name="logradouro_resp" id="logradouro_resp" value="<?php echo htmlspecialchars($dados_responsavel['logradouro_resp'] ?? ''); ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="numero_resp">Número *</label>
                <input type="text" placeholder="123" name="numero_resp" id="numero_resp" value="<?php echo htmlspecialchars($dados_responsavel['numero_resp'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="complemento_resp">Complemento</label>
                <input type="text" placeholder="Apto, Casa, etc." name="complemento_resp" id="complemento_resp" value="<?php echo htmlspecialchars($dados_responsavel['complemento_resp'] ?? ''); ?>">
            </div>
             <div class="form-group">
                <label for="bairro_resp">Bairro *</label>
                <input type="text" placeholder="Preenchido automaticamente" name="bairro_resp" id="bairro_resp" value="<?php echo htmlspecialchars($dados_responsavel['bairro_resp'] ?? ''); ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group flex-2">
                <label for="cidade_resp">Cidade *</label>
                <input type="text" placeholder="Preenchido automaticamente" name="cidade_resp" id="cidade_resp" value="<?php echo htmlspecialchars($dados_responsavel['cidade_resp'] ?? ''); ?>" required>
            </div>
            <div class="form-group flex-1">
                <label for="uf_resp">Estado (UF) *</label>
                <input type="text" placeholder="UF" name="uf_resp" id="uf_resp" value="<?php echo htmlspecialchars($dados_responsavel['uf_resp'] ?? ''); ?>" required maxlength="2">
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary">Próximo: Cadastrar Aluno <i class="fas fa-arrow-right"></i></button>
        </div>
    </form>
</div>
<?php endif; ?>

<?php if ($step == 2): // PASSO 2: FORMULÁRIO DO ALUNO ?>
<div class="form-card">
    <div class="responsavel-info">
        <span>Responsável: <strong><?php echo htmlspecialchars($_SESSION['dados_responsavel']['nome_completo_resp']); ?></strong></span>
        <a href="cadastro_geral.php?back_to_step=1">Alterar</a>
    </div>

    <form action="processa_cadastro_geral.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="step" value="2">
        
        <h3>Dados do Aluno</h3>
        <p>Agora, preencha as informações do aluno.</p>

        <div class="form-row">
            <div class="form-group">
                <label for="nome_completo_aluno">Nome Completo do Aluno *</label>
                <input type="text" placeholder="Nome completo do aluno" name="nome_completo_aluno" id="nome_completo_aluno" value="<?php echo htmlspecialchars($form_data_aluno['nome_completo_aluno'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="data_nascimento_aluno">Data de Nascimento *</label>
                <input type="text" placeholder="dd/mm/aaaa" name="data_nascimento_aluno" id="data_nascimento_aluno" value="<?php echo htmlspecialchars($form_data_aluno['data_nascimento_aluno'] ?? ''); ?>" required maxlength="10">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="email_aluno">E-mail (opcional)</label>
                <input type="email" placeholder="email.aluno@exemplo.com" name="email_aluno" id="email_aluno" value="<?php echo htmlspecialchars($form_data_aluno['email_aluno'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="cpf_aluno">CPF (opcional)</label>
                <input type="text" placeholder="000.000.000-00" name="cpf_aluno" id="cpf_aluno" value="<?php echo htmlspecialchars($form_data_aluno['cpf_aluno'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Foto do Aluno (opcional)</label>
            <div class="file-upload-wrapper">
                <img id="foto-preview" src="#" alt="Preview da foto" style="display: none;">
                <label for="foto_aluno" class="file-upload-label">
                    <i class="fas fa-camera"></i>
                    <span>Clique para enviar uma imagem</span>
                    <small>JPG, PNG até 2MB</small>
                </label>
                <input type="file" name="foto_aluno" id="foto_aluno" accept="image/png, image/jpeg">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Finalizar Cadastro <i class="fas fa-check"></i></button>
        </div>
    </form>
</div>
<?php endif; ?>

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

    // --- MÁSCARA PARA DATA DE NASCIMENTO DO ALUNO (se estiver no passo 2) ---
    const inputDataNascimento = document.querySelector('#data_nascimento_aluno');
    if (inputDataNascimento) {
        inputDataNascimento.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9]/g, '');
            
            if (value.length > 2) value = value.slice(0, 2) + '/' + value.slice(2);
            if (value.length > 5) value = value.slice(0, 5) + '/' + value.slice(5, 9);
            
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