<?php
$privilegio = $_SESSION['privilegio'] ?? 'Nenhum';
?>
<nav class="topbar"><a class="NameSite" href="/gerenciado_de_Sala">Gerenciador de Sala</a>
    <p class="menu-oculto"><svg class="menu-icon">
            <use href="#icon-menu"></use>
        </svg></p>

    <div class="menu">
        <div class="menu-login">
            <p class="effect-button-link button-menu clice-login"> <svg class="icon">
                    <use href="#icon-pessoa"></use>
                </svg>
                <?php echo $_SESSION['nome'] ?? 'Login'; ?>
            </p>

            <ul class=" menu-login-list  menu-list">
                <li class="menu-info">
                    <p class="menu-title"> <svg class="icon">
                            <use href="#icon-pessoa"></use>
                        </svg>
                        <?php echo $_SESSION['nome'] ?? 'Login'; ?></p>
                    <p class="menu-privilegio">
                        privilegio: <?= $privilegio ?></p>
                </li>
                <li> <a class="effect-button-link" href="/config">
                        <svg class="icon">
                            <use href="#icon-config"></use>
                        </svg>
                        configurações</a>
                </li>
                <li>
                    <a class="effect-button-link ajax-link" href="/creditos">
                        <svg class="icon">
                            <use href="#icon-escramacao"></use>
                        </svg>
                        Sobre
                    </a>
                </li>
                <li> <a class="effect-button-link" href="/logout">
                        <svg class="icon">
                            <use href="#icon-seta"></use>
                        </svg>
                        Sair</a>
                </li>
            </ul>
        </div>
        <div class="menu-tabela">
            <p class="effect-button-link button-menu clice-tabela">
                <svg class="icon">
                    <use href="#icon-anotacao"></use>
                </svg> Tabelas <svg class="icon-seta-baixa">
                    <use href="#icon-iconMutidirecao"></use>
                </svg>
            </p>
            <ul class=" menu-list menu-tabela-list">
                <li><a class="effect-button-link ajax-link" href="/agendamentos">agendamentos</a></li>
                <li><a class="effect-button-link ajax-link" href="/salas">salas</a></li>
                <li><a class="effect-button-link ajax-link" href="/cursos">cursos</a></li>
                <?php
                if ($privilegio === 'admin') {
                ?>
                    <li><a class="effect-button-link ajax-link" href="/usuarios">usuários</a></li>
                <?php
                }
                if ($privilegio === 'admin' || $privilegio === 'professor') {
                ?>
                    <li><a class="effect-button-link ajax-link" href="/turmas">turmas</a></li>
                <?php } ?>
            </ul>
        </div>
        <?php
        if ($privilegio !== 'admin') {
        ?>
            <div class="menu-sms">
                <p><a class="effect-button-link button-menu ajax-link" href="/menssagem">
                        <svg class="icon">
                            <use href="#icon-sinio"></use>
                        </svg> menssagem
                    </a></p>
            </div><?php } ?>
        <div class="menu-calendario">
            <p><a class="effect-button-link button-menu ajax-link" href="/calendario">
                    <svg class="icon">
                        <use href="#icon-calendario"></use>
                    </svg> Calendario
                </a></p>
        </div>
    </div>
</nav>