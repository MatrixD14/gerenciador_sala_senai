<nav class="topbar"><a class="NameSite" href="/admin">Gerenciador de Sala</a>
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
                    <p>
                        privilegio: <?php echo $_SESSION['privilegio'] ?? 'Nenhum'; ?></p>
                </li>
                <li> <a class="effect-button-link" href="config">
                        <svg class="icon">
                            <use href="#icon-config"></use>
                        </svg>
                        configurações</a>
                </li>
                <li> <a class="effect-button-link" href="logout">
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
                <li><a class="effect-button-link ajax-link" href="agendamentos">agendamentos</a></li>
                <li><a class="effect-button-link ajax-link" href="usuarios">usuários</a></li>
                <li><a class="effect-button-link ajax-link" href="salas">salas</a></li>
            </ul>
        </div>
        <div class="menu-sms">
            <p><a class="effect-button-link button-menu" href="menssagem">
                    <svg class="icon">
                        <use href="#icon-sinio"></use>
                    </svg> menssagem
                </a></p>
        </div>
    </div>
</nav>
<script src="app/view/vendor/layout/js/button.js"></script>
<script src="app/view/vendor/layout/js/ajax-router.js"></script>