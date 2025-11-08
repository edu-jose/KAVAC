{!! Form::button('<i class="fa fa-check"></i>', [
    'class' => 'btn btn-success btn-xs btn-icon btn-action btn-undelete-record',
    'data-toggle' => 'tooltip', 'type' => 'button',
    'data-route' => $route,
    'data-id' => $user->id,
    'title' => 'Restaurar registro eliminado',
    'disabled' => $disabled ?? false
]) !!}
