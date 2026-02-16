<aside class="sidebar">
    <div style="display: flex; align-items: center; justify-content: space-between;">
        <div class="logo-area">
            <div class="logo-shapes">
                <div class="shape"></div>
                <div class="shape medium"></div>
                <div class="shape dark"></div>
                <div class="shape reverse"></div>
                <div class="shape" style="background:#0399E2;"></div>
                <div class="shape special"></div>
            </div>
            <span class="logo-text">Daily Grow</span>
        </div>
        <i class="fas fa-chevron-down" style="color:#6C757D;"></i>
    </div>

    <div class="account-name">Название аккаунта</div>

    <a href="{{ route('yandex-maps.index') }}" class="menu-item {{ request()->routeIs('yandex-maps.*') ? 'active' : '' }}">
        <i class="fas fa-tools"></i>
        <span>Отзывы</span>
    </a>

    <div class="sub-menu {{ request()->routeIs('yandex-maps.*') ? 'active' : '' }}">
        <a href="{{ route('yandex-maps.index') }}" class="sub-item {{ request()->routeIs('yandex-maps.index') ? 'active' : '' }}">Отзывы</a>
        <a href="{{ route('yandex-maps.settings') }}" class="sub-item {{ request()->routeIs('yandex-maps.settings') ? 'active' : '' }}">Настройка</a>
    </div>

    <div style="margin-top: auto; height: 40px;"></div>
</aside>
