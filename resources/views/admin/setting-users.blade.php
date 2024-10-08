@extends('layouts.app')

@section('maproute-icon')
    <i class="ion-settings"></i>
@stop

@section('maproute-icon-mini')
    <i class="ion-settings"></i>
@stop

@section('maproute-actual')
    {{ __('Configuración') }}
@stop

@section('maproute-title')
    {{ __('Usuarios') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="settings_users">
                <div class="card-header">
                    <h6 class="card-title">
                        {{ __('Usuarios') }}
                        @include('buttons.help', [
                            'helpId' => 'SettingsUser',
                            'helpSteps' => get_json_resource('ui-guides/settings_user.json')
                        ])
                    </h6>
                    <div class="card-btns">
                        @include('buttons.previous', ['route' => url()->previous()])
                        @include('buttons.new', ['route' => route('users.create')])
                        @include('buttons.minimize')
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-hover table-striped dt-responsive datatable">
                        <thead>
                            <tr class="text-center">
                                <th class="col-md-3">{{ __('Usuario') }}</th>
                                <th class="col-md-7">{{ __('Organización') }}</th>
                                <th class="col-md-2">{{ __('Acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (App\Models\User::withTrashed()->get() as $user)
                                <tr>
                                    <td
                                        @if ($user->deleted_at != null)
                                            style="text-decoration: line-through"
                                            title="Registro eliminado"
                                            data-toggle="tooltip"
                                        @endif
                                    >
                                        {{ $user->username }}
                                    </td>
                                    <td
                                        @if ($user->deleted_at != null)
                                            style="text-decoration: line-through"
                                            title="Registro eliminado"
                                            data-toggle="tooltip"
                                        @endif
                                    >
                                        @php
                                            $institution = ($user->profile && $user->profile->institution)
                                                ? $user->profile->institution->acronym
                                                : __('NO ASIGNADA');
                                        @endphp
                                        {{ $institution }}
                                    </td>
                                    <td class="text-center">
                                        @if ($user->deleted_at == null)
                                            {!! Form::button('<i class="fa fa-info-circle"></i>', [
                                                'class' => 'btn btn-info btn-xs btn-icon btn-action',
                                                'data-toggle' => 'tooltip', 'type' => 'button',
                                                'onclick' => 'view_user_info('.$user->id.')',
                                                'title' => __('Ver información del usuario'),
                                            ]) !!}
                                            @include('buttons.edit', ['route' => route('users.edit', $user->id)])
                                            @include('buttons.delete', [
                                                'route' => route('users.destroy', $user->id),
                                                'disabled' => (
                                                    (auth()->user()->id !== $user->id && $user->profile && $user->profile->employee_id == null) ||
                                                    (auth()->user()->id === $user->id)
                                                )
                                            ])
                                        @else
                                            @include('buttons.restore', [
                                                'route' => route('restore.user', $user->id)
                                            ])
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
