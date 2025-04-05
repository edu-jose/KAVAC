@extends('workattendance::layouts.master-without-auth')

@section('content')
    {{-- COLOCAR EL CONTENIDO DE LA PAGINA PRINCIPAL DEL MODULO ACA SI APLICA --}}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-3 text-center">
                <img
                    src="{{ asset('images/default-avatar.png') }}"
                    alt=""
                    class="img-profile mt-4"
                    title="{{ __('Imagen de perfil') }}"
                    data-toggle="tooltip"
                >
                <div class="row">
                    <div class="col-12 from-group text-left">
                        <label for="name" class="control-label h5">
                            <strong>Nombres:</strong> <span id="name"></span>
                        </label>
                    </div>
                    <div class="col-12 from-group text-left">
                        <label for="last_name" class="control-label h5">
                            <strong>Apellidos:</strong> <span id="last_name"></span>
                        </label>
                    </div>
                    <div class="col-12 from-group text-left">
                        <label for="position" class="control-label h5">
                            <strong>Cargo:</strong> <span id="position"></span>
                        </label>
                    </div>
                    <div class="col-12">
                        <div class="calendar">
                            <div class="calendar-header">
                                <h2>{{ $monthsDict[date('F')] }} {{ date('Y') }}</h2>
                            </div>
                            <div class="calendar-body">
                                <div class="table-responsive">
                                <table class="table table-bordered table-calendar">
                                    <thead>
                                        <tr>
                                            @foreach ($weekDaysName as $weekDayName)
                                                <th class="text-info">{{ $weekDayName }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $dateArray = [];
                                        @endphp
                                        @for ($i = 0; $i < $rows; $i++)
                                            <tr>
                                                @for ($j = 0; $j < 7; $j++)
                                                    @php
                                                        $day = ($i * 7) + $j + 1 - ($weekDay - 1);
                                                    @endphp
                                                    @if ($day > 0 && $day <= $monthDaysNumber)
                                                        @php
                                                            $dateArray[] = $currentYear . '-' . (strlen($currentMonth) == 1 ? '0' . $currentMonth : $currentMonth) . '-' . (strlen($day) == 1 ? '0' . $day : $day);
                                                        @endphp
                                                        <td class="text-info" data-date="{{ $currentYear }}-{{ strlen($currentMonth) == 1 ? '0' . $currentMonth : $currentMonth }}-{{ strlen($day) == 1 ? '0' . $day : $day }}">
                                                            {{ $monthDays[$day - 1] }}
                                                            @if ($monthDays[$day - 1] == date('j'))
                                                                &nbsp;<i class="fa fa-clock-o text-success" aria-hidden="true"></i>
                                                            @endif
                                                        </td>
                                                    @else
                                                        <td>&nbsp;</td>
                                                    @endif
                                                @endfor
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                                </div>
                                <table class="table table-without-border table-calendar-legend mt-4">
                                    <tbody>
                                        <tr>
                                            <th class="col-1 bg-warning">&nbsp;</th>
                                            <th class="text-left">
                                                <span class="text-muted ml-2">Asistencia parcial [falta el registro de entrada o salida]</span>
                                            </th>
                                        </tr>
                                        <tr class="table-separator">
                                            <td colspan="2">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td class="col-1 bg-success">&nbsp;</td>
                                            <td class="text-left">
                                                <span class="text-muted ml-2">Asistencia registrada [entrada y salida]</span>
                                            </td>
                                        </tr>
                                        <tr class="table-separator">
                                            <td colspan="2">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td class="col-1 bg-danger">&nbsp;</td>
                                            <td class="text-left">
                                                <span class="text-muted ml-2">Asistencia no registrada</span>
                                            </td>
                                        </tr>
                                        <tr class="table-separator">
                                            <td colspan="2">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td class="col-1 diagonal with-border">&nbsp;</td>
                                            <td class="text-left">
                                                <span class="text-muted ml-2">Permiso / Vacaciones / Feriado</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="col-1 no-mark with-border">&nbsp;</td>
                                            <td class="text-left">
                                                <span class="text-muted ml-2">No marco asistencia</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8 col-lg-9 text-center">
                @include('layouts.logo-images', ['logo_app' => true, 'logo_name' => false, 'logo_class' => 'img-fluid mt-2 img-work-attendance'])
                <div class="d-flex flex-column justify-content-center">
                    <div class="text-center">
                        <h1 class="display-5">Control de asistencia laboral</h1>
                        <h2 class="display-5 text-center"><span class="dayNow">&nbsp</span> <span class="dateNow">&nbsp</span></h2>
                        <h2 class="display-5"><span class="time">&nbsp</span></h2>
                        <form action="{{ route('workattendance.store') }}" method="POST" id="registerForm">
                            @csrf
                            <div class="row">
                                <div class="col-4 offset-4">
                                    @if($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    @if(session()->has('success'))
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <strong>Registro exitoso!</strong> {{ session()->get('success') }}
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    @endif
                                    <div class="form-group">
                                        <label for="identity_card">Cédula</label>
                                        <div class="input-group">
                                            <input
                                                type="text" class="form-control"
                                                id="identity_card" name="identity_card"
                                                data-toggle="tooltip"
                                                data-original-title="{{ __('Indique la cédula de identidad para la asistencia a marcar') }}"
                                                placeholder="Indique su cédula"
                                                autocomplete="off" required
                                            >
                                            <span class="input-group-addon">
                                                <button
                                                    id="searchIdentityCard"
                                                    class="btn btn-transparent btn-sm p-0"
                                                    type="button" data-toggle="tooltip"
                                                    title="{{ __('Buscar cédula') }}"
                                                >
                                                    <i class="now-ui-icons ui-1_zoom-bold"></i>
                                                </button>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div
                                            class="custom-control custom-control-inline custom-switch"
                                            title="Seleccione esta opción si desea marcar la entrada"
                                            data-toggle="tooltip" data-placement="left"
                                        >
                                            <input
                                                type="radio" class="custom-control-input"
                                                id="entry_time" name="type_mark" value="entry"
                                                checked
                                            />
                                            <label class="custom-control-label" for="entry_time">Entrada</label>
                                        </div>
                                        <div
                                            class="custom-control custom-control-inline custom-switch"
                                            title="Seleccione esta opción si desea marcar la salida"
                                            data-toggle="tooltip" data-placement="left"
                                        >
                                            <input
                                                type="radio" class="custom-control-input"
                                                id="exit_time" name="type_mark" value="exit"
                                            />
                                            <label class="custom-control-label" for="exit_time">Salida</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" id="register" class="btn btn-primary btn-lg" disabled>Registrar asistencia</button>
                        </form>
                        <div class="row">
                            <div class="col-4 offset-4 mt-4" id="personalDetails"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('with-footer')
@endsection

@section('modules-css')
    @parent
    <style nonce="{{ session()->get('nonce') }}">
        body {margin: 0 !important;}
        #app {background-color: #32A4FC !important;color: white;padding: 0;margin: 0;}
        #app .container-fluid {min-height: 100vh !important;max-height: 100vh !important;}
        .img-work-attendance {height: 160px;}
        .form-control, .form-control:focus, .input-group-addon {background-color: #FFFFFF !important;font-size: 18px;}
        .btn-transparent {background-color: transparent !important;border: none !important;color: #888 !important;height: 10px !important;}
        .btn-transparent:hover {box-shadow: none !important;}
        .input-group-addon:last-child {padding: 1px 10px !important;}
        .calendar {width: 100%;border: 1px solid #ccc;padding: 20px;background-color: #f0f0f0;color:#32A4FC;}

        @media (max-width: 767px) {
            .calendar-header {font-size: .6rem}
            .img-profile {width: 100px;height: 100px;}
        }

        @media (max-width: 991px) {
            .img-profile {width: 125px;height: 125px;}
        }

        .calendar-header {padding: 10px;border-bottom: 1px solid #ccc;}
        .calendar-header h2 {margin: 0;}
        .calendar-body {padding:5px 0;}
        .calendar-body table {width: 100%;border-collapse: collapse;}
        .calendar-body table th, .calendar-body table td {border: 1px solid #ccc;padding: 10px;text-align: center;}
        .phpdebugbar, .phpdebugbar-openhandler, .phpdebugbar-openhandler-overlay {display:none;}
        .table-calendar {margin: 0 auto;width: 100% !important;}
        .table-without-border tr th, .table-without-border tr td {border: none !important;padding: 0 !important;}
        .table-without-border tr th {font-weight: normal !important;}
        .table-without-border tr th:first-child, .table-without-border tr td:first-child {border-radius: 10px !important;}
        .table-calendar-legend {font-size: .60rem;}
        .table-separator {font-size: 5px}
        td.diagonal {background-image: linear-gradient(to bottom right, transparent 50%, #32A4FC 50%, #32A4FC calc(50% + 1px), transparent calc(50% + 1px));background-size: 100% 100%;}
        td.no-mark {
            background-image: linear-gradient(to bottom right, transparent 50%, #FF0000 50%, #FF0000 calc(50% + 1.5px), transparent 0%),
            linear-gradient(to bottom left, transparent 50%, #FF0000 50%, #FF0000 calc(50% + 1.5px), transparent 0%);
            background-size: 100% 100%, 100% 100%;
        }
        .with-border {border: 1px solid #ccc !important;}
        td.text-info.bg-success, td.text-info.bg-success i {color: #fff !important;}
        .table-details {
            background-color: #fff !important;
        }
    </style>
@endsection

@section('modules-js')
    @parent
    <script nonce="{{ session()->get('nonce') }}">
        let inactividad = 300000;
        let timeoutId = setTimeout(refrescarToken, inactividad);
        document.addEventListener('DOMContentLoaded', function() {
            const searchIdentityCard = document.getElementById('searchIdentityCard');
            const identityCard = document.getElementById('identity_card');
            let register = false;
            identityCard.focus();
            searchIdentityCard.addEventListener('click', getPersonalData);
            identityCard.addEventListener("keydown", function(event) {
                if (event.key === "Enter") {
                    getPersonalData();
                } else if (
                    event.key !== "Escape" &&
                    event.key !== "Esc" &&
                    event.key !== "Tab" &&
                    event.key !== "Alt" &&
                    event.key !== "Control" &&
                    event.key !== "Shift"
                ) {
                    if (
                        !/^\d+$/.test(event.key) &&
                        event.key !== "Backspace" &&
                        event.key !== "ArrowLeft" &&
                        event.key !== "ArrowRight" &&
                        event.key !== "Delete"
                    ) {
                        /* Permite solo pulsar las teclas numéricas y teclas de borrado, flecha izquierda, flecha derecha y tecla de borrar */
                        event.preventDefault();
                    }
                    document.getElementById('name').innerHTML = '';
                    document.getElementById('last_name').innerHTML = '';
                    document.getElementById('position').innerHTML = '';
                    document.getElementById('personalDetails').innerHTML = '';
                    document.getElementById('register').disabled = true;
                    $('.table-calendar tbody tr td').removeClass('bg-success bg-warning bg-danger diagonal no-mark');
                }
            });
            document.getElementById('register').addEventListener('submit', function() {
                if (!resgister) {
                    register = true;
                    $(this).submit();
                }
                return false;
            })
            setTimeout(function() {
                $('.alert-success').alert('close');
            }, 3000);
            $(document).on('keydown mousemove', function() {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(refrescarToken, inactividad);
            });
        });
        const clock = document.getElementsByClassName('time');
        const dayNow = document.getElementsByClassName('dayNow');
        const dateNow = document.getElementsByClassName('dateNow');

        setInterval(actualizarReloj, 1000);

        actualizarReloj();

        function actualizarReloj() {
            const fecha = new Date();
            const horas = fecha.getHours();
            const minutos = fecha.getMinutes();
            const segundos = fecha.getSeconds();
            const dia = fecha.toLocaleString('es-ES', { weekday: 'long' }).toUpperCase();

            // Formateamos la hora en formato 12 horas
            const horaFormateada = (horas % 12 === 0) ? 12 : horas % 12;
            const minutosFormateados = (minutos < 10) ? '0' + minutos : minutos;
            const segundosFormateados = (segundos < 10) ? '0' + segundos : segundos;

            // Mostramos la hora en el elemento
            if (clock.length > 0) {
                clock[0].textContent = `${horaFormateada.toString().padStart(2, '0')}:${minutosFormateados.toString().padStart(2, '0')}:${segundosFormateados.toString().padStart(2, '0')} ${horas >= 12 ? 'PM' : 'AM'}`;
            }
            if (dayNow.length > 0) {
                dayNow[0].textContent = dia;
            }
            if (dateNow.length > 0) {
                dateNow[0].textContent = `${fecha.getDate().toString().padStart(2, '0')}/${(fecha.getMonth() + 1).toString().padStart(2, '0')}/${fecha.getFullYear()}`;
            }
        }

        function getPersonalData(id) {
            axios.get(`${window.app_url}/work-attendance/search/id_card/${document.getElementById('identity_card').value}`).then(response => {
                if (response.data.result) {
                    document.getElementById('name').innerHTML = response.data.staff.first_name;
                    document.getElementById('last_name').innerHTML = response.data.staff.last_name;
                    document.getElementById('position').innerHTML = response.data.staff.payroll_employment?.payrollPosition?.name ?? 'NO REGISTRADO';
                    document.getElementById('register').disabled = false;
                    let currentDate = "{{ \Carbon\Carbon::now()->format('Y-m-d') }}";
                    let registerDates = response.data.workAttendance.map(workAttendance => workAttendance.date_at);
                    let calendarDates = {!! json_encode($dateArray) !!}
                    let noMarkDates = calendarDates.filter(date => !registerDates.includes(date) && date != currentDate && date < currentDate);
                    noMarkDates.forEach(date => {
                        $(`[data-date="${date}"]`).addClass('no-mark');
                    });

                    if (response.data.workAttendance.length > 0) {
                        const hasNowRegisters = response.data.workAttendance.filter(workAttendance => workAttendance.date_at == currentDate);
                        let personalDetails = '';
                        if (hasNowRegisters.length > 0) {
                            personalDetails = `
                                <table class="table table-bordered table-condensed table-hover table-details">
                                    <thead>
                                        <tr>
                                            <th class="text-center" colspan="2">Datos registrados hoy</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center text-bold">Entrada</td>
                                            <td class="text-center text-bold">Salida</td>
                                        </tr>
                            `;
                        }

                        response.data.workAttendance.forEach(workAttendance => {
                            if (hasNowRegisters.length > 0 && workAttendance.date_at == currentDate) {
                                personalDetails += `
                                    <tr>
                                        <td>${workAttendance.entry_time ? workAttendance.entry_time : '---'}</td>
                                        <td>${workAttendance.exit_time ? workAttendance.exit_time : '---'}</td>
                                    </tr>`;
                            }

                            let success = workAttendance.entry_time && workAttendance.exit_time ? true : false;
                            let warning = (workAttendance.entry_time && !workAttendance.exit_time) || (!workAttendance.entry_time && workAttendance.exit_time);
                            let danger = !workAttendance.entry_time && !workAttendance.exit_time;
                            if (success) {
                                $(`[data-date="${workAttendance.date_at}"]`).addClass('bg-success');
                            } else if (warning) {
                                $(`[data-date="${workAttendance.date_at}"]`).addClass('bg-warning');
                            } else if (danger) {
                                $(`[data-date="${workAttendance.date_at}"]`).addClass('bg-danger');
                            }
                            if (currentDate == workAttendance.date_at) {
                                if (workAttendance.entry_time && !workAttendance.exit_time) {
                                    document.getElementById('entry_time').checked = false;
                                    document.getElementById('exit_time').checked = true;
                                } else if (!workAttendance.entry_time && !workAttendance.exit_time) {
                                    document.getElementById('entry_time').checked = true;
                                    document.getElementById('exit_time').checked = false;
                                }
                            }
                        });

                        if (hasNowRegisters.length > 0) {
                            personalDetails += '</tbody></table>';
                            document.getElementById('personalDetails').innerHTML = personalDetails;
                        }
                    }
                } else {
                    $.gritter.add({
                        title: '{{ __('Alerta!') }}',
                        text: '{{ __('No se encontró el personal con la cédula indicada') }}',
                        class_name: 'growl-danger',
                        image: "{{ asset('images/screen-error.png') }}",
                        sticky: false,
                        time: 2500
                    });
                }
            }).catch(error => {
                $.gritter.add({
                    title: '{{ __('Alerta!') }}',
                    text: '{{ __('No se encontró el personal con la cédula indicada. Verifique el número e intente de nuevo') }}',
                    class_name: 'growl-danger',
                    image: "{{ asset('images/screen-error.png') }}",
                    sticky: false,
                    time: 3500
                });
            });
        }

        function refrescarToken() {
            $.ajax({
                type: 'GET',
                url: '{{ route('refresh.csrf.token') }}',
                success: function(data) {
                    $('input[name="_token"]').val(data);
                    timeoutId = setTimeout(refrescarToken, inactividad);
                }
            });
        }
    </script>
@endsection
