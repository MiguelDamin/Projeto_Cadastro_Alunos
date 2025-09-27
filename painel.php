<?php
// Inclui o cabeçalho (header + sidebar)
require 'templates/header.php';
require 'conexao.php';

// CONSULTA ATUALIZADA: Buscar apenas avisos que ainda não expiraram
try {
    $avisos = $pdo->query("
        SELECT a.*, u.nome_completo as autor_nome,
               CASE 
                   WHEN TIMESTAMPDIFF(MINUTE, NOW(), a.data_expiracao) <= 60 AND TIMESTAMPDIFF(MINUTE, NOW(), a.data_expiracao) > 0 THEN CONCAT(TIMESTAMPDIFF(MINUTE, NOW(), a.data_expiracao), ' min restantes')
                   WHEN TIMESTAMPDIFF(HOUR, NOW(), a.data_expiracao) <= 24 AND TIMESTAMPDIFF(HOUR, NOW(), a.data_expiracao) > 0 THEN CONCAT(TIMESTAMPDIFF(HOUR, NOW(), a.data_expiracao), 'h restantes')
                   WHEN TIMESTAMPDIFF(DAY, NOW(), a.data_expiracao) > 0 THEN CONCAT(TIMESTAMPDIFF(DAY, NOW(), a.data_expiracao), ' dias restantes')
                   ELSE 'Expirando em breve'
               END as tempo_restante
        FROM avisos a 
        LEFT JOIN usuarios u ON a.usuario_id = u.id 
        WHERE a.status = 'ativo' 
        AND a.data_publicacao <= NOW()
        AND a.data_expiracao > NOW()
        ORDER BY a.data_publicacao DESC 
        LIMIT 5
    ")->fetchAll();
} catch (PDOException $e) {
    $avisos = [];
}
?>

<head>
    <title>Painel - Sistema Escolar</title>
</head>

<div class="page-header">
    <h2>Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>!</h2>
    <p>Bem-vindo ao sistema de gestão escolar. Aqui você pode gerenciar alunos, responsáveis e turmas.</p>
</div>

<!-- Seção de Avisos Recentes (com sistema de expiração) -->
<?php if (!empty($avisos)): ?>
<div style="margin-bottom: 30px;">
    <h3 style="color: #495057; margin-bottom: 15px;">📢 Avisos Recentes</h3>
    
    <div style="display: grid; gap: 15px;">
        <?php foreach ($avisos as $aviso): ?>
            <div style="background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,123,255,0.3); position: relative;">
                
                <!-- Badge de tempo restante -->
                <div style="position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.2); padding: 5px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                    ⏰ <?php echo $aviso['tempo_restante']; ?>
                </div>
                
                <div style="display: flex; justify-content: between; align-items: flex-start; margin-bottom: 10px; padding-right: 100px;">
                    <h4 style="margin: 0; font-size: 18px; font-weight: bold;">
                        <?php echo htmlspecialchars($aviso['titulo']); ?>
                    </h4>
        <div style="position: absolute; top: 20px; left: 1010px; text-align: right; font-size: 12px; opacity: 0.9;">
            <div><?php echo date('d/m/Y', strtotime($aviso['data_publicacao'])); ?></div>
            <div><?php echo date('H:i', strtotime($aviso['data_publicacao'])); ?></div>
        </div>
                </div>
                
                <p style="margin: 0; line-height: 1.5; font-size: 15px;">
                    <?php 
                    // Limitar a mensagem para não ficar muito grande no painel
                    $mensagem = htmlspecialchars($aviso['mensagem']);
                    if (strlen($mensagem) > 200) {
                        echo substr($mensagem, 0, 200) . '...';
                    } else {
                        echo $mensagem;
                    }
                    ?>
                </p>
                
                <?php if ($aviso['autor_nome']): ?>
                    <div style="margin-top: 10px; font-size: 12px; opacity: 0.8; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 8px;">
                        Publicado por: <?php echo htmlspecialchars($aviso['autor_nome']); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div style="text-align: center; margin-top: 15px;">
        <a href="adicionar_avisos.php" style="text-decoration: none; background: #28a745; color: white; padding: 8px 16px; border-radius: 5px; font-size: 14px; font-weight: 600;">
            ➕ Adicionar Novo Aviso
        </a>
    </div>
</div>
<?php else: ?>
<!-- Caso não tenha avisos ativos/não expirados -->
<div style="margin-bottom: 30px;">
    <h3 style="color: #495057; margin-bottom: 15px;">📢 Avisos Recentes</h3>
    <div style="background: #f8f9fa; padding: 30px; border-radius: 8px; text-align: center; border: 2px dashed #dee2e6;">
        <p style="color: #6c757d; margin: 0; font-size: 16px;">
            Nenhum aviso ativo no momento. 
        </p>
        <p style="color: #6c757d; margin: 10px 0 20px 0; font-size: 14px;">
            Todos os avisos podem ter expirado ou não há avisos publicados.
        </p>
        <a href="adicionar_avisos.php" style="text-decoration: none; background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; font-size: 14px; font-weight: 600;">
            ➕ Publicar Primeiro Aviso
        </a>
    </div>
</div>
<?php endif; ?>

<div class="dashboard-grid">
    <div class="widget card">
        <div class="widget-icon" style="background-color: #e0f7fa;">
            <i class="fas fa-info-circle" style="color: #0097a7;"></i>
        </div>
        <div class="widget-content">
            <h3>Bem-vindo!</h3>
            <p>Utilize o menu lateral para navegar entre as funcionalidades.</p>
        </div>
    </div>

    <div class="widget card">
        <div class="widget-icon" style="background-color: #fff3e0;">
            <i class="fas fa-users" style="color: #ff9800;"></i>
        </div>
        <div class="widget-content">
            <h3>Alunos Recentes</h3>
            <p>Resumo dos últimos alunos cadastrados no sistema.</p>
        </div>
    </div>

    <div class="widget card">
        <div class="widget-icon" style="background-color: #e8f5e9;">
            <i class="fas fa-chalkboard" style="color: #4caf50;"></i>
        </div>
        <div class="widget-content">
            <h3>Turmas</h3>
            <p>Informações sobre as últimas turmas criadas ou ativas.</p>
        </div>
    </div>

    <div class="widget card">
        <div class="widget-icon" style="background-color: #fce4ec;">
            <i class="fas fa-bolt" style="color: #e91e63;"></i>
        </div>
        <div class="widget-content">
            <h3>Atalhos</h3>
            <p>Acesse rapidamente as funcionalidades mais utilizadas.</p>
        </div>
    </div>
</div>

<!-- Widget de Estatísticas Rápidas -->
<?php
try {
    // Buscar estatísticas básicas
    $total_alunos = $pdo->query("SELECT COUNT(*) FROM alunos")->fetchColumn();
    $total_turmas = $pdo->query("SELECT COUNT(*) FROM turmas")->fetchColumn();
    $total_professores = $pdo->query("SELECT COUNT(*) FROM professores")->fetchColumn();
    // Contamos apenas avisos não expirados para a estatística
    $total_avisos_ativos = $pdo->query("SELECT COUNT(*) FROM avisos WHERE status = 'ativo' AND data_expiracao > NOW()")->fetchColumn();
} catch (PDOException $e) {
    $total_alunos = $total_turmas = $total_professores = $total_avisos_ativos = 0;
}
?>

<div style="margin-top: 30px;">
    <h3 style="color: #495057; margin-bottom: 15px;">📊 Estatísticas do Sistema</h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        <div style="background: #28a745; color: white; padding: 20px; border-radius: 8px; text-align: center;">
            <div style="font-size: 32px; font-weight: bold; margin-bottom: 5px;">
                <?php echo $total_alunos; ?>
            </div>
            <div style="font-size: 14px; opacity: 0.9;">
                Alunos Cadastrados
            </div>
        </div>
        
        <div style="background: #17a2b8; color: white; padding: 20px; border-radius: 8px; text-align: center;">
            <div style="font-size: 32px; font-weight: bold; margin-bottom: 5px;">
                <?php echo $total_turmas; ?>
            </div>
            <div style="font-size: 14px; opacity: 0.9;">
                Turmas Ativas
            </div>
        </div>
        
        <div style="background: #ffc107; color: #212529; padding: 20px; border-radius: 8px; text-align: center;">
            <div style="font-size: 32px; font-weight: bold; margin-bottom: 5px;">
                <?php echo $total_professores; ?>
            </div>
            <div style="font-size: 14px; opacity: 0.9;">
                Professores
            </div>
        </div>
        
        <div style="background: #6f42c1; color: white; padding: 20px; border-radius: 8px; text-align: center;">
            <div style="font-size: 32px; font-weight: bold; margin-bottom: 5px;">
                <?php echo $total_avisos_ativos; ?>
            </div>
            <div style="font-size: 14px; opacity: 0.9;">
                Avisos Ativos
            </div>
        </div>
    </div>
</div>

<!-- ESTILOS DO MURAL DE AVISOS -->
<style>
    .mural-avisos-container {
        width: 100%;
        margin-top: 40px;
    }
    .mural-avisos-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 25px;
        border-bottom: 2px solid #dee2e6;
        padding-bottom: 10px;
    }
    .mural-avisos-header h3 {
        margin: 0;
        font-size: 1.5rem;
        color: #343a40;
    }
    .mural-avisos-header .link-novo-aviso {
        text-decoration: none;
        font-weight: 600;
        color: #007bff;
        font-size: 0.9rem;
    }
    .mural-avisos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
    }
    .aviso-card {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.07);
        display: flex;
        flex-direction: column;
        border-left: 5px solid #007bff;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        position: relative;
    }
    .aviso-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 7px 20px rgba(0,0,0,0.09);
    }
    .aviso-card-header { 
        padding: 20px 25px 15px; 
        position: relative; 
    }
    .aviso-card-header h4 { 
        margin: 0 0 10px 0; 
        font-size: 1.2rem; 
        color: #212529; 
        padding-right: 80px; /* Espaço para o badge de tempo */
    }
    .aviso-meta { 
        display: flex; 
        flex-wrap: wrap; 
        align-items: center; 
        gap: 15px; 
        font-size: 0.85rem; 
        color: #6c757d; 
    }
    .aviso-meta-item { 
        display: flex; 
        align-items: center; 
        gap: 6px; 
    }
    .aviso-meta-item svg { 
        width: 16px; 
        height: 16px; 
        opacity: 0.7; 
    }
    .aviso-card-body { 
        padding: 0 25px 20px; 
        font-size: 0.9rem; 
        line-height: 1.6; 
        color: #495057; 
        flex-grow: 1; 
    }
    .aviso-card-footer { 
        padding: 15px 25px; 
        background-color: #f8f9fa; 
        border-top: 1px solid #dee2e6; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        border-bottom-left-radius: 10px; 
        border-bottom-right-radius: 10px; 
    }
    .card-actions a { 
        text-decoration: none; 
        font-size: 0.8rem; 
        font-weight: 600; 
        color: #6c757d; 
        margin-left: 15px; 
        transition: color 0.2s; 
    }
    .card-actions a:hover { 
        color: #007bff; 
    }
    .aviso-vazio { 
        grid-column: 1 / -1; 
        text-align: center; 
        padding: 40px; 
        background-color: #fff; 
        border-radius: 10px; 
        color: #6c757d; 
        border: 2px dashed #dee2e6; 
    }
    
    /* Estilos para o badge de tempo restante nos cards */
    .tempo-restante-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: #e8f4fd;
        color: #0056b3;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        border: 1px solid #bee5eb;
    }
    
    .tempo-urgente-card {
        background: #ffebee !important;
        color: #c62828 !important;
        border-color: #ffcdd2 !important;
    }
    
    .tempo-atencao-card {
        background: #fff3e0 !important;
        color: #ef6c00 !important;
        border-color: #ffcc02 !important;
    }
</style>

<!-- HTML DO MURAL DE AVISOS COMPLETO (Mantendo tudo como estava, mas agora com dados atualizados) -->
<div class="mural-avisos-container">
    <div class="mural-avisos-header">
        <h3>📋 Mural de Avisos Completo</h3>
        <a href="adicionar_avisos.php" class="link-novo-aviso">➕ Gerenciar Avisos</a>
    </div>

    <?php 
    // Buscar TODOS os avisos (incluindo os expirados) para o mural completo
    try {
        $todos_avisos = $pdo->query("
            SELECT a.*, u.nome_completo as autor_nome,
                   CASE 
                       WHEN a.data_expiracao <= NOW() THEN 1 
                       ELSE 0 
                   END as expirado,
                   CASE 
                       WHEN TIMESTAMPDIFF(MINUTE, NOW(), a.data_expiracao) <= 60 AND TIMESTAMPDIFF(MINUTE, NOW(), a.data_expiracao) > 0 THEN CONCAT(TIMESTAMPDIFF(MINUTE, NOW(), a.data_expiracao), ' min')
                       WHEN TIMESTAMPDIFF(HOUR, NOW(), a.data_expiracao) <= 24 AND TIMESTAMPDIFF(HOUR, NOW(), a.data_expiracao) > 0 THEN CONCAT(TIMESTAMPDIFF(HOUR, NOW(), a.data_expiracao), 'h')
                       WHEN TIMESTAMPDIFF(DAY, NOW(), a.data_expiracao) > 0 THEN CONCAT(TIMESTAMPDIFF(DAY, NOW(), a.data_expiracao), ' dias')
                       ELSE 'Expirado'
                   END as tempo_restante
            FROM avisos a 
            LEFT JOIN usuarios u ON a.usuario_id = u.id 
            WHERE a.status = 'ativo' 
            AND a.data_publicacao <= NOW()
            ORDER BY a.data_publicacao DESC 
            LIMIT 8
        ")->fetchAll();
    } catch (PDOException $e) {
        $todos_avisos = [];
    }
    ?>

    <?php if (empty($todos_avisos)): ?>
        <div class="aviso-vazio">
            <p>Nenhum aviso publicado ainda. Crie o primeiro aviso!</p>
            <a href="adicionar_avisos.php" style="display: inline-block; margin-top: 15px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">
                ➕ Criar Primeiro Aviso
            </a>
        </div>
    <?php else: ?>
        <div class="mural-avisos-grid">
            <?php foreach ($todos_avisos as $aviso): ?>
                <div class="aviso-card" style="<?php echo $aviso['expirado'] ? 'opacity: 0.7; border-left-color: #6c757d;' : ''; ?>">
                    <div class="aviso-card-header">
                        <h4><?php echo htmlspecialchars($aviso['titulo']); ?></h4>
                        
                        <!-- Badge de tempo restante -->
                        <div class="tempo-restante-badge <?php 
                            if ($aviso['expirado']) {
                                echo 'tempo-urgente-card';
                            } elseif (strpos($aviso['tempo_restante'], 'min') !== false || (strpos($aviso['tempo_restante'], 'h') !== false && (int)$aviso['tempo_restante'] <= 24)) {
                                echo 'tempo-urgente-card';
                            } elseif (strpos($aviso['tempo_restante'], 'dias') !== false && (int)$aviso['tempo_restante'] <= 2) {
                                echo 'tempo-atencao-card';
                            }
                        ?>">
                            <?php echo $aviso['expirado'] ? '❌ Expirado' : '⏰ ' . $aviso['tempo_restante']; ?>
                        </div>
                        
                        <div class="aviso-meta">
                            <span class="aviso-meta-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                <?php echo htmlspecialchars($aviso['autor_nome'] ?: 'Sistema'); ?>
                            </span>
                            <span class="aviso-meta-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                <?php echo date('d/m/Y', strtotime($aviso['data_publicacao'])); ?>
                            </span>
                        </div>
                    </div>
                    <div class="aviso-card-body">
                        <p><?php echo nl2br(htmlspecialchars($aviso['mensagem'])); ?></p>
                    </div>
                    <div class="aviso-card-footer">
                        <div style="font-size: 0.75rem; color: #6c757d;">
                            <?php echo $aviso['expirado'] ? 'Este aviso expirou e não aparece mais nos avisos recentes' : 'Visível nos avisos recentes'; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
// Inclui o rodapé (fecha as tags e adiciona o script da sidebar)
require 'templates/footer.php';
?>