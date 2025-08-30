<div id="sidebar" class="c-sidebar c-sidebar-fixed c-sidebar-lg-show">

    <div class="c-sidebar-brand d-md-down-none">
        <div class="c-sidebar-brand-full" href="#">
            PINJAM-RUANG FTMM
        </div>
        <div class="c-sidebar-brand-minimized">
            PR
        </div>
    </div>

    <ul class="c-sidebar-nav">
        <li class="c-sidebar-nav-item">
            <a href="{{ route("admin.home") }}" class="c-sidebar-nav-link" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="{{ trans('global.dashboard') }}">
                <i class="c-sidebar-nav-icon fas fa-fw fa-fire"></i>
                {{ trans('global.dashboard') }}
            </a>
        </li>
        @can('permission_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route("admin.permissions.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/permissions") || request()->is("admin/permissions/*") ? "c-active" : "" }}" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="{{ trans('cruds.permission.title') }}">
                    <i class="fa-fw fas fa-unlock-alt c-sidebar-nav-icon"></i>
                    {{ trans('cruds.permission.title') }}
                </a>
            </li>
        @endcan
        @can('role_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route("admin.roles.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/roles") || request()->is("admin/roles/*") ? "c-active" : "" }}" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="{{ trans('cruds.role.title') }}">
                    <i class="fa-fw fas fa-briefcase c-sidebar-nav-icon"></i>
                    {{ trans('cruds.role.title') }}
                </a>
            </li>
        @endcan
        @can('user_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route("admin.users.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/users") || request()->is("admin/users/*") ? "c-active" : "" }}" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="{{ trans('cruds.user.title') }}">
                    <i class="fa-fw fas fa-user c-sidebar-nav-icon"></i>
                    {{ trans('cruds.user.title') }}
                </a>
            </li>
        @endcan
        
        @can('ruangan_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route("admin.ruangan.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/ruangan") || request()->is("admin/ruangan/*") ? "c-active" : "" }}" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="{{ trans('cruds.ruangan.title') }}">
                    <i class="fa-fw fas fa-hotel c-sidebar-nav-icon"></i>
                    {{ trans('cruds.ruangan.title') }}
                </a>
            </li>
        @endcan
        @can('kegiatan_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route("admin.kegiatan.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/kegiatan") || request()->is("admin/kegiatan/*") ? "c-active" : "" }}" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="{{ trans('cruds.kegiatan.title') }}">
                    <i class="fa-fw fas fa-calendar c-sidebar-nav-icon"></i>
                    {{ trans('cruds.kegiatan.title') }}
                </a>
            </li>
        @endcan
        @can('kuliah_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route("admin.jadwal-perkuliahan.index") }}" class="c-sidebar-nav-link {{ request()->is("admin/jadwal") || request()->is("admin/jadwal/*") ? "c-active" : "" }}" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Jadwal Perkuliahan">
                    <i class="fa-fw fa fa-calendar-alt c-sidebar-nav-icon"></i>
                    Jadwal Perkuliahan
                </a>
            </li>
        @endcan
        @can('calendar_access')
        <li class="c-sidebar-nav-item">
            <a href="{{ route("admin.systemCalendar") }}" class="c-sidebar-nav-link {{ request()->is("admin/kalender") || request()->is("admin/kalender/*") ? "c-active" : "" }}" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="{{ trans('global.systemCalendar') }}">
                <i class="c-sidebar-nav-icon fa-fw fa fa-calendar-check"></i>
                {{ trans('global.systemCalendar') }}
            </a>
        </li>
        @endcan
        @if(file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php')))
            @can('profile_password_edit')
                <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link {{ request()->is('profile/password') || request()->is('profile/password/*') ? 'c-active' : '' }}" href="{{ route('profile.password.edit') }}" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="{{ trans('global.change_password') }}">
                        <i class="fa-fw fas fa-key c-sidebar-nav-icon"></i>
                        {{ trans('global.change_password') }}
                    </a>
                </li>
            @endcan
        @endif
        <li class="c-sidebar-nav-item">
            <a href="{{ route("admin.cariRuang") }}" class="c-sidebar-nav-link {{ request()->is("admin/cari-ruang") ? "c-active" : "" }}" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Cari Ruang">
                <i class="c-sidebar-nav-icon fa-fw fas fa-search"></i>
                Cari Ruang
            </a>
        </li>
        <li class="c-sidebar-nav-item">
            <a href="#" class="c-sidebar-nav-link" onclick="event.preventDefault(); document.getElementById('logoutform').submit();" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="{{ trans('global.logout') }}">
                <i class="c-sidebar-nav-icon fas fa-fw fa-sign-out-alt"></i>
                {{ trans('global.logout') }}
            </a>
        </li>
    </ul>

</div>