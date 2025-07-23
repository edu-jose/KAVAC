{{-- Gestión de atención al ciudadano --}}
<li>
    <a href="javascript:void(0)" title="Gestión de atención al ciudadano" data-toggle="tooltip"
       data-placement="right">
        <i class="icofont icofont-users-social"></i><span>{{ __('Atención / Ciudadano') }}</span>
    </a>
    <ul class="submenu" style="{!! display_submenu('citizenservice') !!}">
        <li class="{!! set_active_menu(['citizenservice.settings.index']) !!}">
            <a href="{{ route('citizenservice.settings.index') }}">
                {{__('Configuración') }}
            </a>
        </li>
        <li class="{!! set_active_menu(['citizenservice.request.index']) !!}">
            <a href="{{ route('citizenservice.request.index') }}">
                {{ __('Gestión de Trámites') }}
            </a>
        </li>
         <li class="{!! set_active_menu(['citizenservice.report.index']) !!}">
             <a href="{{ route('citizenservice.report.index') }}">
                {{ __('Reportes') }}
             </a>
        </li>
        <li class="{!! set_active_menu(['citizenservice.register.index']) !!}">
            <a href="{{ route('citizenservice.register.index') }}">
                {{ __('Ingresar Cronograma') }}
            </a>
        </li>
        <li
            class="{!! set_active_menu([
                'citizenservice.contact-books.index',
                'citizenservice.contact-books.create',
                'citizenservice.contact-books.edit',
                'citizenservice.contact-books.show',
            ]) !!}"
        >
            <a href="{{ route('citizenservice.contact-books.index') }}">
                {{ __('Agenda de Contactos') }}
            </a>
        </li>
        <li class="{!! set_active_menu([
            'citizenservice.community-profilings.index',
            'citizenservice.community-profilings.create',
            'citizenservice.community-profilings.edit',
            'citizenservice.community-profilings.show',
        ]) !!}">
            <a href="{{ route('citizenservice.community-profilings.index') }}">
                {{ __('Caracterización / Comunidades') }}
            </a>
        </li>
    </ul>
</li>
