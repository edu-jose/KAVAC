<ul class="hide">
    <li>
        <a href="javascript:void(0)" title="Gestiónd e Asistencia" data-toggle="tooltip" data-placement="right">
            <i class="icofont icofont-calendar"></i><span>Gestión de Asistencia</span>
        </a>
        <ul class="submenu" style="{!! display_submenu('workattendance') !!}">
            <li class="{!! set_active_menu(['workattendance.setting.index']) !!}">
                <a
                    href="{{ route('workattendance.setting.index') }}"
                    title="Configuración para la gestión de asistencia"
                    data-toggle="tooltip"
                    data-placement="right"
                >
                    {{ __('Configuración') }}
                </a>
            </li>
            <li class="{!! set_active_menu(['workattendance.custom.schedule.index']) !!}">
                <a
                    href="{{ route('workattendance.custom.schedule.index') }}"
                    title="Registro de horarios personalizados"
                    data-toggle="tooltip"
                    data-placement="right"
                >
                    {{ __('Horarios Personalizados') }}
                </a>
            </li>
            <li class="{!! set_active_menu(['workattendance.external.activity.index']) !!}">
                <a
                    href="{{ route('workattendance.external.activity.index') }}"
                    title="Registro de asistencia a actividades externas"
                    data-toggle="tooltip"
                    data-placement="right"
                >
                    {{ __('Actividades Externas') }}
                </a>
            </li>
            <li class="{!! set_active_menu(
                [
                    'workattendance.permissions.index',
                    'workattendance.permissions.create',
                    'workattendance.permissions.edit',
                ]
            ) !!}">
                <a
                    href="{{ route('workattendance.permissions.index') }}"
                    title="registrar permisos y/o motivos de inasistencia"
                    data-toggle="tooltip"
                    data-placement="right"
                >
                    {{ __('Permisos') }}
                </a>
            </li>
            <li>
                <a href="javascript:void(0)" data-toggle="tooltip" data-placement="right" title="Reportes">Reportes</a>
                <ul
                    class="submenu"
                    style="{!! display_submenu([
                        'workattendance.history.index',
                        'workattendance.history.individual',
                        'workattendance.history.by-department',
                    ]) !!}"
                >
                    <li class="{!! set_active_menu(['workattendance.history.index']) !!}">
                        <a
                            href="{{ route('workattendance.history.index') }}"
                            title="Mostrar histórico de asistencia del personal"
                            data-toggle="tooltip"
                            data-placement="right"
                        >
                            {{ __('Histórico General') }}
                        </a>
                    </li>
                    <li class="{!! set_active_menu(['workattendance.history.individual']) !!}">
                        <a
                            href="{{ route('workattendance.history.individual') }}"
                            title="Mostrar asistencia individual (por persona)"
                            data-toggle="tooltip"
                            data-placement="right"
                        >
                            {{ __('Histórico Individual') }}
                        </a>
                    </li>
                    <li class="{!! set_active_menu(['workattendance.history.by-department']) !!}">
                        <a
                            href="{{ route('workattendance.history.by-department') }}"
                            title="Mostrar asistencia por unidad o dependencia"
                            data-toggle="tooltip"
                            data-placement="right"
                        >
                            {{ __('Histórico por Dependencia') }}
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </li>
</ul>
