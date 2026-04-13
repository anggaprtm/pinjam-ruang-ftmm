{{-- menu.blade.php — CoreUI v4 --}}

<div id="sidebar" class="sidebar sidebar-fixed border-end">

    <div class="sidebar-brand">
        <span class="sidebar-brand-full fw-bold">FTMM-NEXUS</span>
        <span class="sidebar-brand-narrow">
            <img src="{{ asset('images/brand.png') }}" alt="Logo" style="height: 30px; width: auto;">
        </span>
    </div>

    {{-- KRITIS: data-coreui="navigation" wajib ada agar CoreUI v4 JS
         mengaktifkan toggle dropdown. Tanpa ini semua nav-group-toggle
         tidak bisa diklik. --}}
    <ul class="sidebar-nav" data-coreui="navigation">

        {{-- 1. DASHBOARD --}}
        <li class="nav-item">
            <a href="{{ route('admin.home') }}"
               class="nav-link {{ request()->is('admin/home') || request()->is('admin') ? 'active' : '' }}">
                <i class="nav-icon fas fa-fire"></i>
                {{ trans('global.dashboard') }}
                @if(optional(auth()->user())->isAdmin() && !empty($totalDashboardPending))
                    <span class="badge bg-danger ms-auto">{{ $totalDashboardPending }}</span>
                @endif
            </a>
        </li>

        {{-- 2. KEPEGAWAIAN --}}
        @canany(['presensi_access', 'tendik_access', 'dosen_access'])
            <li class="nav-group {{ request()->is('admin/absensi*') || request()->is('admin/tendik*') || request()->is('admin/dosen*') ? 'show' : '' }}">
                <a class="nav-group-toggle" href="#">
                    <i class="nav-icon fas fa-id-badge"></i>
                    Kepegawaian
                </a>
                <ul class="nav-group-items compact">
                    @can('presensi_access')
                        <li class="nav-item">
                            <a href="{{ route('admin.absensi.index') }}"
                               class="nav-link {{ request()->is('admin/absensi*') ? 'active' : '' }}">
                                <span class="nav-icon"><i class="fas fa-fingerprint"></i></span>
                                Log Presensi
                            </a>
                        </li>
                    @endcan
                    @can('presensi_access')
                        <li class="nav-item">
                            <a href="{{ route('admin.lembur-kegiatan.index') }}"
                            class="nav-link {{ request()->is('admin/lembur-kegiatan*') ? 'active' : '' }}">
                                <span class="nav-icon"><i class="fas fa-business-time"></i></span>
                                Kegiatan Lembur
                            </a>
                        </li>
                    @endcan
                    @can('tendik_access')
                        <li class="nav-item">
                            <a href="{{ route('admin.tendik.index') }}"
                               class="nav-link {{ request()->is('admin/tendik*') ? 'active' : '' }}">
                                <span class="nav-icon"><i class="fas fa-user-tie"></i></span>
                                Manajemen Tendik
                            </a>
                        </li>
                    @endcan
                    @can('dosen_access')
                        <li class="nav-item">
                            <a href="{{ route('admin.dosen.index') }}"
                               class="nav-link {{ request()->is('admin/dosen*') ? 'active' : '' }}">
                                <span class="nav-icon"><i class="fas fa-chalkboard-teacher"></i></span>
                                Manajemen Dosen
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        {{-- 3. AKADEMIK & KEGIATAN --}}
        <li class="nav-group {{ request()->is('admin/kegiatan*') || request()->is('admin/jadwal*') || request()->is('admin/kalender*') || request()->is('admin/cari-ruang*') ? 'show' : '' }}">
            <a class="nav-group-toggle" href="#">
                <i class="nav-icon fas fa-graduation-cap"></i>
                Akademik & Kegiatan
            </a>
            <ul class="nav-group-items compact">
                @can('kegiatan_access')
                    <li class="nav-item">
                        <a href="{{ route('admin.kegiatan.index') }}"
                           class="nav-link {{ request()->is('admin/kegiatan*') ? 'active' : '' }}">
                            <span class="nav-icon"><i class="fas fa-calendar"></i></span>
                            {{ trans('cruds.kegiatan.title') }}
                            @if(optional(auth()->user())->isAdmin() && !empty($pendingKegiatanCount))
                                <span class="badge bg-danger ms-auto">{{ $pendingKegiatanCount }}</span>
                            @endif
                        </a>
                    </li>
                @endcan
                @can('kuliah_access')
                    <li class="nav-item">
                        <a href="{{ route('admin.jadwal-perkuliahan.index') }}"
                           class="nav-link {{ request()->is('admin/jadwal*') ? 'active' : '' }}">
                            <span class="nav-icon"><i class="fas fa-calendar-alt"></i></span>
                            Jadwal Perkuliahan
                        </a>
                    </li>
                @endcan
                @can('calendar_access')
                    <li class="nav-item">
                        <a href="{{ route('admin.systemCalendar') }}"
                           class="nav-link {{ request()->is('admin/kalender*') ? 'active' : '' }}">
                            <span class="nav-icon"><i class="fas fa-calendar-check"></i></span>
                            Kalender Sistem
                        </a>
                    </li>
                @endcan
                <li class="nav-item">
                    <a href="{{ route('admin.cariRuang') }}"
                       class="nav-link {{ request()->is('admin/cari-ruang') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="fas fa-search"></i></span>
                        Cari Ruang
                    </a>
                </li>
            </ul>
        </li>

        {{-- 4. FASILITAS & LAYANAN --}}
        @canany(['ruangan_access', 'barang_access', 'aset_fakultas_access', 'mobil_access', 'riwayat_perjalanan_access', 'permintaan_kegiatan_access'])
            <li class="nav-group {{ request()->is('admin/ruangan*') || request()->is('admin/barangs*') || request()->is('admin/mobils*') || request()->is('admin/riwayat-perjalanan*') || request()->is('admin/permintaan-kegiatan*') ? 'show' : '' }}">
                <a class="nav-group-toggle" href="#">
                    <i class="nav-icon fas fa-building"></i>
                    Fasilitas & Layanan
                </a>
                <ul class="nav-group-items compact">
                    @can('ruangan_access')
                        <li class="nav-item">
                            <a href="{{ route('admin.ruangan.index') }}"
                               class="nav-link {{ request()->is('admin/ruangan*') ? 'active' : '' }}">
                                <span class="nav-icon"><i class="fas fa-hotel"></i></span>
                                {{ trans('cruds.ruangan.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('aset_fakultas_access')
                        <li class="nav-item">
                            <a href="{{ route('admin.aset-fakultas.index') }}"
                               class="nav-link {{ request()->is('admin/aset-fakultas*') ? 'active' : '' }}">
                                <span class="nav-icon"><i class="fas fa-boxes"></i></span>
                                Aset Fakultas
                            </a>
                        </li>
                    @endcan
                    @can('barang_access')
                        <li class="nav-item">
                            <a href="{{ route('admin.barangs.index') }}"
                               class="nav-link {{ request()->is('admin/barangs*') ? 'active' : '' }}">
                                <span class="nav-icon"><i class="fas fa-box"></i></span>
                                Data Barang
                            </a>
                        </li>
                    @endcan
                    @can('mobil_access')
                        <li class="nav-item">
                            <a href="{{ route('admin.mobils.index') }}"
                               class="nav-link {{ request()->is('admin/mobils*') ? 'active' : '' }}">
                                <span class="nav-icon"><i class="fas fa-car"></i></span>
                                Data Kendaraan
                            </a>
                        </li>
                    @endcan
                    @can('riwayat_perjalanan_access')
                        <li class="nav-item">
                            <a href="{{ route('admin.riwayat-perjalanan.index') }}"
                               class="nav-link {{ request()->is('admin/riwayat-perjalanan*') ? 'active' : '' }}">
                                <span class="nav-icon"><i class="fas fa-road"></i></span>
                                Logbook Driver
                            </a>
                        </li>
                    @endcan
                    @can('permintaan_kegiatan_access')
                        <li class="nav-item">
                            <a href="{{ route('admin.permintaan-kegiatan.index') }}"
                               class="nav-link {{ request()->is('admin/permintaan-kegiatan*') ? 'active' : '' }}">
                                <span class="nav-icon"><i class="fas fa-concierge-bell"></i></span>
                                Permintaan Layanan
                                @if(optional(auth()->user())->isAdmin() && !empty($pendingPermintaanCount))
                                    <span class="badge bg-danger ms-auto">{{ $pendingPermintaanCount }}</span>
                                @endif
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        {{-- 5. MANAJEMEN AKSES --}}
        @canany(['permission_access', 'role_access', 'user_access'])
            <li class="nav-group {{ request()->is('admin/permissions*') || request()->is('admin/roles*') || request()->is('admin/users*') ? 'show' : '' }}">
                <a class="nav-group-toggle" href="#">
                    <i class="nav-icon fas fa-users-cog"></i>
                    Manajemen Akses
                </a>
                <ul class="nav-group-items compact">
                    @can('permission_access')
                        <li class="nav-item">
                            <a href="{{ route('admin.permissions.index') }}"
                               class="nav-link {{ request()->is('admin/permissions*') ? 'active' : '' }}">
                                <span class="nav-icon"><i class="fas fa-unlock-alt"></i></span>
                                {{ trans('cruds.permission.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('role_access')
                        <li class="nav-item">
                            <a href="{{ route('admin.roles.index') }}"
                               class="nav-link {{ request()->is('admin/roles*') ? 'active' : '' }}">
                                <span class="nav-icon"><i class="fas fa-briefcase"></i></span>
                                {{ trans('cruds.role.title') }}
                            </a>
                        </li>
                    @endcan
                    @can('user_access')
                        <li class="nav-item">
                            <a href="{{ route('admin.users.index') }}"
                               class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                                <span class="nav-icon"><i class="fas fa-user"></i></span>
                                {{ trans('cruds.user.title') }}
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany
        @canany(['agenda_fakultas_access', 'display_config_access', 'device_command_access'])
            <li class="nav-group {{ request()->is('admin/agenda-fakultas*') || request()->is('admin/display-config*') || request()->is('admin/device-command*') ? 'show' : '' }}">
                <a class="nav-group-toggle" href="#">
                    <i class="nav-icon fas fa-display"></i>
                    Pengaturan Signage
                </a>
                <ul class="nav-group-items compact">
                    @can('agenda_fakultas_access')
                        <li class="nav-item">
                            <a href="{{ route('admin.agenda-fakultas.index') }}"
                               class="nav-link {{ request()->is('admin/agenda*') ? 'active' : '' }}">
                                <span class="nav-icon"><i class="fas fa-calendar-alt"></i></span>
                                Agenda Fakultas
                            </a>
                        </li>
                    @endcan
                    @can('display_config_access')
                        <li class="nav-item">
                            <a href="{{ route('admin.display-config.index') }}"
                               class="nav-link {{ request()->is('admin/config*') ? 'active' : '' }}">
                                <span class="nav-icon"><i class="fas fa-cog"></i></span>
                                Konfigurasi Display
                            </a>
                        </li>
                    @endcan
                    @can('device_command_access')
                        <li class="nav-item">
                            <a href="{{ route('admin.device-command.index') }}"
                               class="nav-link {{ request()->is('admin/device-command*') ? 'active' : '' }}">
                                <span class="nav-icon"><i class="fas fa-computer"></i></span>
                                Remote Perangkat
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany
        @canany(['surat_undangan_access', 'surat_tugas_access'])
            <li class="nav-group {{ request()->is('admin/surat-undangan*') || request()->is('admin/surat-tugas*') ? 'show' : '' }}">
                <a class="nav-group-toggle" href="#">
                    <i class="nav-icon fas fa-file-signature"></i>
                    Generator Surat
                </a>
                <ul class="nav-group-items compact">
                    @can('surat_undangan_access')
                        <li class="nav-item">
                            <a href="{{ route('admin.surat-undangan.index') }}"
                               class="nav-link {{ request()->is('admin/surat-undangan*') ? 'active' : '' }}">
                                <span class="nav-icon"><i class="fas fa-file-invoice"></i></span>
                                Surat Undangan
                            </a>
                        </li>
                    @endcan
                    @can('surat_tugas_access')
                        <li class="nav-item">
                            <a href="{{ route('admin.surat-tugas.index') }}"
                               class="nav-link {{ request()->is('admin/surat-tugas*') ? 'active' : '' }}">
                                <span class="nav-icon"><i class="fas fa-file-lines"></i></span>
                                Surat Tugas
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        {{-- Spacer dorong profil & logout ke bawah --}}
        <li class="nav-item nav-spacer"></li>

        {{-- 6. PROFIL --}}
        @if(file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php')))
            @can('profile_password_edit')
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('profile/password*') ? 'active' : '' }}"
                       href="{{ route('profile.password.edit') }}">
                        <i class="nav-icon fas fa-key"></i>
                        {{ trans('global.change_password') }}
                    </a>
                </li>
            @endcan
        @endif

        {{-- 7. LOGOUT --}}
        <li class="nav-item mb-2">
            <a href="#"
               class="nav-link nav-link-logout"
               onclick="event.preventDefault(); document.getElementById('logoutform').submit();">
                <i class="nav-icon fas fa-sign-out-alt"></i>
                {{ trans('global.logout') }}
            </a>
        </li>

    </ul>
</div>