<div class="row">
    <div class="col-12">
        <div id="card_config_institution" class="card">
            <div class="card-header">
                <h6 class="card-title">
                    {{ __('Configurar Organización') }}
                    @include('buttons.help', [
                        'helpId' => 'institution',
                        'helpSteps' => get_json_resource('ui-guides/institution.json'),
                    ])
                </h6>
                <div class="card-btns">
                    @include('buttons.previous', ['route' => url()->previous()])
                    @include('buttons.minimize')
                </div>
            </div>
            <div class="card-body">
                @if ( count($errors->all()) > 0 )
                    @include('layouts.form-errors')
                @endif
                <div id="helpInstitutionImgs" class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">{{ __('Logotipo ') }} <label for="" class="text-danger"><h4>*</h4></label></label>
                            {!! Form::open([
                                'id' => 'formImgLogo',
                                'method' => 'POST',
                                'route' => 'upload-image.store',
                                'role' => 'form',
                                'class' => 'form',
                                'enctype' => 'multipart/form-data',
                            ]) !!}
                            @php
                                $img_logo =
                                    isset($model_institution) &&
                                    !is_null($model_institution->logo) &&
                                    file_exists(base_path($model_institution->logo->url))
                                        ? $model_institution->logo->url
                                        : null;
                            @endphp
                            @if (old('logo_id'))
                                @php
                                    $logo = App\Models\Image::find(old('logo_id'));
                                    $img_logo = file_exists(base_path($logo->url)) ? $logo->url : null;
                                @endphp
                            @endif
                            <img
                                src="{{ asset($img_logo ?? 'images/no-image2.png') }}" alt="{{ __('Logotipo') }}"
                                class="img-fluid institution-logo" style="cursor:pointer" id="institution-logo"
                                title="{{ __('Click para cargar o modificar la imagen') }}" data-toggle="tooltip"
                            >
                            <input
                                id="logo_image" type="file" name="logo_image" style="display:none"
                            >
                            <div class="row row-delete-img">
                                <div class="col-12">
                                    <div class="institution-logo text-center">
                                        <a class="img-delete" href="javascript:void(0)" id='delLogoImage'>
                                            {{ __('Eliminar') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="">
                                {{ __('Banner o Cintillo') }}
                                @if (!is_null($paramReportBanner) && $paramReportBanner->p_value === 'true')
                                    <label for="" class="text-danger"><h4>*</h4></label>
                                @endif
                            </label>
                            {!! Form::open([
                                'id' => 'formImgBanner',
                                'method' => 'POST',
                                'route' => 'upload-image.store',
                                'role' => 'form',
                                'class' => 'form',
                                'enctype' => 'multipart/form-data',
                            ]) !!}
                            @php
                                $img_banner =
                                    isset($model_institution) &&
                                    !is_null($model_institution->banner) &&
                                    file_exists(base_path($model_institution->banner->url))
                                        ? $model_institution->banner->url
                                        : null;
                            @endphp
                            @if (old('banner_id'))
                                @php
                                    $banner = App\Models\Image::find(old('banner_id'));
                                    $img_banner = file_exists(base_path($banner->url)) ? $banner->url : null;
                                @endphp
                            @endif
                            <img
                                src="{{ asset($img_banner ?? 'images/no-image3.png') }}"
                                alt="{{ __('Banner / Cintillo') }}" class="img-fluid institution-banner"
                                style="cursor:pointer" title="{{ __('Click para cargar o modificar la imagen') }}"
                                data-toggle="tooltip"
                            >
                            <input
                                type="file" id="banner_image" name="banner_image" style="display:none"
                            >
                            <div class="row row-delete-img">
                                <div class="col-12">
                                    <div class="text-center">
                                        <a
                                            class="img-delete" href="javascript:void(0)" id='delBannerImage'
                                        >
                                            {{ __('Eliminar') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                {!! Form::model($model_institution, $header_institution) !!}
                {!! Form::hidden('logo_id', old('logo_id'), ['readonly' => 'readonly', 'id' => 'logo_id']) !!}
                {!! Form::hidden('banner_id', old('banner_id'), ['readonly' => 'readonly', 'id' => 'banner_id']) !!}
                {!! Form::hidden('institution_id', isset($model_institution) ? $model_institution->id : '', [
                    'readonly' => 'readonly',
                    'id' => 'institution_id',
                ]) !!}

                <hr>
                <h6 class="md-title">{{ __('Datos Básicos') }}:</h6>
                <div id="helpInstitutionBasicData">
                    <div class="row">
                        @if (config('institution.use_onapre'))
                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('onapre_code', __('Código ONAPRE'), []) !!}
                                    {!! Form::text('onapre_code', isset($model_institution) ? $model_institution->onapre_code : old('onapre_code'), [
                                        'class' => 'form-control input-sm',
                                        'id' => 'onapre_code',
                                        'data-toggle' => 'tooltip',
                                        'title' => __('Indique el código ONAPRE asignado a la organización (requerido)'),
                                    ]) !!}
                                </div>
                            </div>
                        @endif
                        <div class="col-md-4" id="helpInstitutionRif">
                            <div class="form-group{{ $errors->has('rif') ? ' has-error' : '' }} is-required">
                                <label for="rif">{{ __('R.I.F.') }}</label>
                                <input
                                    type="text" name="rif" id="rif"
                                    class="form-control input-sm"
                                    data-toggle="tooltip"
                                    placeholder="{{ __('X000000000') }}"
                                    title="{{
                                        __(
                                            'Indique el número de registro de identificación fiscal (requerido). Debe estar formado por 10 caracteres, el primer carácter debe ser una letra: J,V,E,G o P (en mayúscula); los otros nueve carácteres deben ser números.',
                                        )
                                    }}"
                                    value="{{ isset($model_institution) ? $model_institution->rif : old('rif') }}"
                                >
                            </div>
                        </div>
                        <div class="col-md-{{ config('institution.use_onapre') ? '4' : '8' }}">
                            <div class="form-group is-required{{ $errors->has('name') ? ' has-error' : '' }}">
                                <label for="name">{{ __('Nombre') }}</label>
                                <input
                                    type="text" name="name" id="name"
                                    class="form-control input-sm"
                                    data-toggle="tooltip"
                                    title="{{ __('Indique el nombre de la organización (requerido)') }}"
                                    value="{{ isset($model_institution) ? $model_institution->name : old('name') }}"
                                >
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group is-required{{ $errors->has('acronym') ? ' has-error' : '' }}">
                                <label for="acronym">{{ __('Acronimo (Nombre corto)') }}</label>
                                <input
                                    type="text" name="acronym" id="acronym"
                                    class="form-control input-sm"
                                    data-toggle="tooltip"
                                    title="{{ __('Introduzca el nombre corto de la organización (requerido)') }}"
                                    value="{{ isset($model_institution) ? $model_institution->acronym : old('acronym') }}"
                                >
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group is-required">
                                <label for="business_name">{{ __('Razón Social') }}</label>
                                <input
                                    type="text" name="business_name" id="business_name"
                                    class="form-control input-sm"
                                    data-toggle="tooltip"
                                    title="{{ __('Introduzca la razón social') }}"
                                    value="{{ isset($model_institution) ? $model_institution->business_name : old('business_name') }}"
                                >
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group is-required">
                                <label for="country_id">{{ __('País') }}</label>
                                <select
                                    name="country_id" id="country_id"
                                    class="form-control select2 input-sm"
                                >
                                    @foreach ($countries ?? [] as $countryId => $countryName)
                                        <option
                                            value="{{ $countryId }}"
                                            {{
                                                (old('country_id') == $countryId || (isset($model_institution) && $model_institution->municipality->estate->country_id == $countryId)) ?
                                                'selected' : ''
                                            }}
                                        >
                                            {{ $countryName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group is-required">
                                <label for="estate_id">{{ __('Estado') }}</label>
                                <select
                                    name="estate_id" id="estate_id"
                                    class="form-control select2 input-sm"
                                >
                                    @foreach ($estates ?? [] as $estateId => $estateName)
                                        <option
                                            value="{{ $estateId }}"
                                            {{
                                                (old('estate_id') == $estateId || (isset($model_institution) && $model_institution->municipality->estate_id == $estateId)) ?
                                                'selected' : ''
                                            }}
                                        >
                                            {{ $estateName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group is-required">
                                <label for="municipality_id">{{ __('Municipio') }}</label>
                                <select
                                    name="municipality_id" id="municipality_id"
                                    class="form-control select2 input-sm"
                                >
                                    @foreach ($municipalities ?? [] as $municipalityId => $municipalityName)
                                        <option
                                            value="{{ $municipalityId }}"
                                            {{
                                                (old('municipality_id') == $municipalityId || (isset($model_institution) && $model_institution->municipality_id == $municipalityId)) ?
                                                'selected' : ''
                                            }}
                                        >
                                            {{ $municipalityName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group is-required">
                                <label for="city_id">{{ __('Ciudad') }}</label>
                                <select
                                    name="city_id" id="city_id"
                                    class="form-control select2 input-sm"
                                >
                                    @foreach ($cities ?? [] as $cityId => $cityName)
                                        <option
                                            value="{{ $cityId }}"
                                            {{
                                                (old('city_id') == $cityId || (isset($model_institution) && $model_institution->city_id == $cityId)) ?
                                                'selected' : ''
                                            }}
                                        >
                                            {{ $cityName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group is-required">
                                <label for="postal_code">{{ __('Código Postal') }}</label>
                                <input
                                    type="text" name="postal_code" id="postal_code"
                                    class="form-control input-sm"
                                    data-toggle="tooltip"
                                    title="{{ __('Indique el código postal (requerido)') }}"
                                    value="{{ isset($model_institution) ? $model_institution->postal_code : old('postal_code') }}"
                                >
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group is-required">
                                <label for="start_operations_date">
                                    {{ __('Fecha de inicio de operaciones') }}
                                </label>
                                <input
                                    type="date" name="start_operations_date" id="start_operations_date"
                                    class="form-control input-sm"
                                    data-toggle="tooltip"
                                    title="{{ __('Indique la fecha de inicio de operaciones (requerido)') }}"
                                    value="{{ isset($model_institution) ? $model_institution->start_operations_date : old('start_operations_date') }}"
                                >
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group is-required">
                                <label for="institution_sector_id">{{ __('Sector Económico') }}</label>
                                <select
                                    name="institution_sector_id" id="institution_sector_id"
                                    class="form-control select2"
                                >
                                    @foreach ($sectors ?? [] as $sectorId => $sectorName)
                                        <option
                                            value="{{ $sectorId }}"
                                            {{
                                                (old('institution_sector_id') == $sectorId || (isset($model_institution) && $model_institution->institution_sector_id == $sectorId)) ?
                                                'selected' : ''
                                            }}
                                        >
                                            {{ $sectorName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group is-required">
                                <label for="institution_type_id">{{ __('Tipo de Organización') }}</label>
                                <select
                                    name="institution_type_id" id="institution_type_id"
                                    class="form-control select2"
                                >
                                    @foreach ($types ?? [] as $typeId => $typeName)
                                        <option
                                            value="{{ $typeId }}"
                                            {{
                                                (old('institution_type_id') == $typeId || (isset($model_institution) && $model_institution->institution_type_id == $typeId)) ?
                                                'selected' : ''
                                            }}
                                        >
                                            {{ $typeName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="web">{{ __('Sitio Web') }}</label>
                                <input
                                    type="text" name="web" id="web"
                                    class="form-control input-sm"
                                    value="{{ isset($model_institution) ? $model_institution->web : old('web') }}"
                                    data-toggle="tooltip" title="{{ __('Indique la URL del sitio web') }}"
                                >
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="email">{{ __('Correo electrónico de contacto') }}</label>
                                <input
                                    type="email" name="email" id="email" class="form-control input-sm"
                                    value="{{ isset($model_institution) ? $model_institution->email : old('email') }}"
                                    data-toggle="tooltip" title="{{ __('Indique el correo electrónico de contacto') }}"
                                >
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group is-required">
                                <label for="legal_address">{{ __('Dirección Fiscal') }}</label>
                                <div id="legal_address_editor">
                                    <ckeditor
                                        :editor="ckeditor.editor"
                                        :config="ckeditor.editorConfig"
                                        id="legal_address"
                                        name="legal_address"
                                        ref="legal_address"
                                        data-toggle="tooltip"
                                        title="{!! __('Indique la dirección fiscal de la organización (requerido)') !!}"
                                        class="form-control"
                                        tag-name="textarea"
                                        rows="4"
                                        value="{!!
                                            isset($model_institution) ?
                                            $model_institution->legal_address :
                                            old('legal_address')
                                        !!}"
                                    ></ckeditor>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="row">
                                <div class="col-md-6 text-center">
                                    <div class="form-group">
                                        <label for="active">{{ __('Activa') }}</label>
                                        <div
                                            class="custom-control custom-switch" data-toggle="tooltip"
                                            title="Establece si el organismo esta activo"
                                        >
                                            <input
                                                type="checkbox" name="active" id="active"
                                                class="custom-control-input"
                                                value="true"
                                                {{ old('active') ?? (isset($model_institution) && $model_institution->active ? 'checked' : '') }}
                                            >
                                            <label class="custom-control-label" for="active">&nbsp;</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 text-center">
                                    <div class="form-group">
                                        <label for="default">{{ __('Organización por defecto') }}</label>
                                        <div
                                            class="custom-control custom-switch" data-toggle="tooltip"
                                            title="Establece el organismo por defecto"
                                        >
                                            <input
                                                type="checkbox" name="default" id="default"
                                                class="custom-control-input"
                                                value="true"
                                                {{ old('default') ?? (isset($model_institution) && $model_institution->default ? 'checked' : '') }}
                                            >
                                            <label class="custom-control-label" for="default">&nbsp;</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 text-center">
                                    <div class="form-group">
                                        <label for="retention_agent">{{ __('Agente de Retención') }}</label>
                                        <div
                                            class="custom-control custom-switch" data-toggle="tooltip"
                                            title="Establece si el organismo es agente de retención"
                                        >
                                            <input
                                                type="checkbox" name="retention_agent" id="retention_agent"
                                                class="custom-control-input"
                                                value="true"
                                                {{ old('retention_agent') ?? (isset($model_institution) && $model_institution->retention_agent ? 'checked' : '') }}
                                            >
                                            <label class="custom-control-label" for="retention_agent">&nbsp;</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mt-2">
                            @if (
                                old('phone_type') ||
                                old('phone_area_code') ||
                                old('phone_number') ||
                                old('phone_extension')
                            )
                                @php
                                    $initialDataPhones = [];
                                    foreach (old('phone_type') as $key => $value) {
                                        $initialDataPhones[] = [
                                            'type' => old('phone_type')[$key],
                                            'area_code' => old('phone_area_code')[$key],
                                            'number' => old('phone_number')[$key],
                                            'extension' => old('phone_extension')[$key],
                                        ];
                                    }
                                @endphp
                            @endif
                            <phones
                                ref="institutionPhones"
                                @if (isset($initialDataPhones) && count($initialDataPhones) > 0)
                                    initial_data="{{ json_encode($initialDataPhones) }}"
                                @elseif (isset($model_institution) && $model_institution->phones)
                                    initial_data="{{ json_encode($model_institution->phones) }}"
                                @endif
                            ></phones>
                        </div>
                    </div>
                </div>
                <hr>
                <h6 class="md-title">{{ __('Datos Complementarios') }}:</h6>
                <div id="helpInstitutionComplementaryData">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="legal_base">{{ __('Base Legal') }}</label>
                                <ckeditor
                                    :editor="ckeditor.editor"
                                    :config="ckeditor.editorConfig"
                                    id="legal_base"
                                    name="legal_base"
                                    ref="legal_base"
                                    data-toggle="tooltip"
                                    title="{!! __('Indique la base legal constitutiva de la organización') !!}"
                                    class="form-control"
                                    tag-name="textarea"
                                    rows="4"
                                    value="{!!
                                        isset($model_institution) ?
                                        $model_institution->legal_base :
                                        old('legal_base')
                                    !!}"
                                ></ckeditor>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="legal_form">{{ __('Forma Jurídica') }}</label>
                                <ckeditor
                                    :editor="ckeditor.editor"
                                    :config="ckeditor.editorConfig"
                                    id="legal_form"
                                    name="legal_form"
                                    ref="legal_form"
                                    data-toggle="tooltip"
                                    title="{!! __('Indique la forma jurídica de la organización') !!}"
                                    class="form-control"
                                    tag-name="textarea"
                                    rows="4"
                                    value="{!!
                                        isset($model_institution) ?
                                        $model_institution->legal_form :
                                        old('legal_form')
                                    !!}"
                                ></ckeditor>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="main_activity">{{ __('Actividad Principal') }}</label>
                                <ckeditor
                                    :editor="ckeditor.editor"
                                    :config="ckeditor.editorConfig"
                                    id="main_activity"
                                    name="main_activity"
                                    ref="main_activity"
                                    data-toggle="tooltip"
                                    class="form-control"
                                    title="{!! __(
                                        'Indique la actividad principal a la cual se dedica la organización'
                                    ) !!}"
                                    tag-name="textarea"
                                    rows="4"
                                    value="{!!
                                        isset($model_institution) ?
                                        $model_institution->main_activity :
                                        old('main_activity')
                                    !!}"
                                ></ckeditor>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="mission">{{ __('Misión') }}</label>
                                <ckeditor
                                    :editor="ckeditor.editor"
                                    :config="ckeditor.editorConfig"
                                    id="mission"
                                    name="mission"
                                    ref="mission"
                                    data-toggle="tooltip"
                                    title="{!! __('Indique la misión de la organización') !!}"
                                    class="form-control"
                                    tag-name="textarea"
                                    rows="4"
                                    value="{!! isset($model_institution) ? $model_institution->mission : old('mission') !!}"
                                ></ckeditor>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vision">{{ __('Visión') }}</label>
                                <ckeditor
                                    :editor="ckeditor.editor"
                                    :config="ckeditor.editorConfig"
                                    id="vision"
                                    name="vision"
                                    ref="vision"
                                    tag-name="textarea"
                                    rows="4"
                                    data-toggle="tooltip"
                                    title="{!! __('Indique la visión de la organización') !!}"
                                    class="form-control"
                                    value="{!! isset($model_institution) ? $model_institution->vision : old('vision') !!}"
                                ></ckeditor>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="composition_assets">{{ __('Composición de Patrimonio') }}</label>
                                <ckeditor
                                    id="composition_assets"
                                    name="composition_assets"
                                    ref="composition_assets"
                                    :editor="ckeditor.editor"
                                    :config="ckeditor.editorConfig"
                                    class="form-control"
                                    data-toggle="tooltip"
                                    title="{!! __('Indique la composición patrimonial de la organización') !!}"
                                    tag-name="textarea"
                                    rows="4"
                                    value="{!!
                                        isset($model_institution) ?
                                        $model_institution->composition_assets :
                                        old('composition_assets')
                                    !!}"
                                ></ckeditor>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 offset-md-9 text-right mt-4 mb-4" id="helpInstitutionButtons">
                            @include('layouts.form-buttons', [
                                'btnClearId' => "btnClearInstitution"
                            ])
                        </div>
                    </div>
                </div>


                <hr>
                <h6 class="md-title card-title">{{ __('Organizaciones Registradas') }}</h6>
                <div class="row">
                    {{-- Ocultando temporalmente el boton para agregar nuevas empresas --}}
                    @if (false)
                        <div class="col-12 text-right">
                            @include('buttons.new', [
                                'route' => 'javascript:void(0)',
                                'btnClass' =>
                                    'btn btn-sm btn-primary btn-custom btn-mini btn-new btn-new-institution',
                            ])
                        </div>
                    @endif
                </div>
                {!! Form::close() !!}

                <table
                    class="table table-hover table-striped dt-responsive nowrap datatable"
                    id="helpInstitutionList"
                >
                    <thead>
                        <tr>
                            <th class="col-md-1">{{ __('Logo') }}</th>
                            <th class="col-md-1">{{ __('R.I.F') }}</th>
                            @if (config('institution.use_onapre'))
                                <th class="col-md-1">{{ __('Código ONAPRE') }}</th>
                            @endif
                            <th class="col-md-6">{{ __('Nombre') }}</th>
                            <th class="col-md-1">{{ __('Activa') }}</th>
                            <th class="col-md-2">{{ __('Acción') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($institutions as $institution)
                            <tr>
                                <td class="text-center">
                                    @if (!is_null($institution->logo))
                                        <img
                                            src="{{ url($institution->logo->url) }}"
                                            alt="{{ __('logo') }}"
                                            class="img-fluid" style="max-height:50px;"
                                        >
                                    @endif
                                </td>
                                <td>
                                    {{ $institution->rif }}
                                </td>
                                @if (config('institution.use_onapre'))
                                    <td>{{ $institution->onapre_code }}</td>
                                @endif
                                <td>
                                    @if ($institution->acronym)
                                        {{ $institution->acronym }} -
                                    @endif
                                    {{ $institution->name }}
                                </td>
                                <td class="text-center">
                                    <span class="text-bold text-{{ $institution->active ? 'success' : 'danger' }}">
                                        {{ $institution->active ? __('SI') : __('NO') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a
                                        class="btn btn-info btn-xs btn-icon btn-action btn-show-institution"
                                        data-toggle="tooltip" data-id="{{ $institution->id }}"
                                        href="javascript:void(0)" title="Ver registro" v-has-tooltip
                                    >
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a class="btn btn-warning btn-xs btn-icon btn-action" data-toggle="tooltip"
                                        title="Modificar registro" v-has-tooltip
                                        href='{{ route('admin.settings.edit', $institution->id) }}'
                                    >
                                        <i class="fa fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div
    id="detailsInstitutionModal" class="modal fade" tabindex="-1"
    aria-labelledby="detailsInstitutionModal" aria-hidden="true"
>
    <div
        class="modal-dialog  modal-dialog-scrollable modal-xl text-left"
        style="max-width: 60rem; color: #636e7b; font-size: 13px"
    >
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <h6 style="font-size: 1em">
                    <i class="icofont icofont-read-book ico-2x"></i>
                    {{ __('Información detallada') }}
                </h6>
            </div>
            <div class="modal-body">
                <div class="tab-content">
                    <div class="tab-pane active" id="general" role="tabpanel">
                        {{-- Imágenes --}}
                        <div class="row justify-content-center">
                            <div class="col-8 col-lg-3">
                                <p class="text-center mb-1 font-weight-bold">{{ __('Logotipo') }}</p>
                                <img
                                    id="modal-logo" class="w-100"
                                    src="{{ asset('/images/no-image2.png', Request::secure()) }}"
                                    alt=""
                                >
                            </div>
                            <div class="col-8 col-lg-7">
                                <p class="text-center mb-1 font-weight-bold">{{ __('Banner o Cintillo') }}</p>
                                <img
                                    id="modal-banner" class="w-100" src="{{ asset('/images/no-image3.png') }}" alt=""
                                >
                            </div>
                        </div>
                        {{-- Detalles --}}
                        <div class="row col-lg-12" style="padding-top: 3rem;padding-bottom: 1rem;">
                            <u>
                                <h6>{{ __('DATOS BÁSICOS') }}:</h6>
                            </u>
                        </div>
                        <div class="row">
                            @if (config('institution.use_onapre'))
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <strong>{{ __('Código ONAPRE') }}:</strong>
                                        <div class="row">
                                            <span class="col-md-12">
                                                <span id="modal-onapre_code"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>{{ __('R.I.F.') }}:</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-rif"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-{{ config('institution.use_onapre') ? '4' : '8' }}">
                                <div class="form-group">
                                    <strong>{{ __('Nombre') }}:</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-name"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>{{ __('Acrónimo (Nombre corto)') }}</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-acronym"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>{{ __('Razón Social') }}:</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-business_name"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>{{ __('País') }}:</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-country_id"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>{{ __('Estado') }}:</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-estate_id"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>{{ __('Ciudad') }}:</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-city_id"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>{{ __('Municipio') }}:</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-municipality_id"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>{{ __('Código Postal') }}:</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-postal_code"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>{{ __('Fecha de inicio de operaciones') }}:</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-start_operations_date"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>{{ __('Sectores Económicos') }}:</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-institution_sector_id"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>{{ __('Tipo de Organización') }}:</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-institution_type_id"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>{{ __('Dirección Fiscal') }}:</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-legal_address"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>{{ __('Activa') }}:</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-active"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>{{ __('Organización por defecto') }}:</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-default"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>{{ __('Agente de Retención') }}:</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-retention_agent"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>{{ __('Sitio web') }}:</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-web"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>{{ __('Correo electrónico') }}:</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-email"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>{{ __('Teléfonos') }}:</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-phones"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 " style="padding-top: 1rem;padding-bottom: 1rem;">
                                <u>
                                    <h6 class="">{{ __('DATOS COMPLEMENTARIOS') }}:</h6>
                                </u>
                            </div>
                            <div class="col-md-4 "></div>
                            <div class="col-md-4 "></div>
                            <div class="col-md-4 ">
                                <div class="form-group">
                                    <strong>{{ __('Base Legal') }}:</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-legal_base"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>{{ __('Forma Jurídica') }}:</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-legal_form"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>{{ __('Actividad Principal') }}:</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-main_activity"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>{{ __('Misión') }}:</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-mission"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>{{ __('Visión') }}:</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-vision"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <strong>{{ __('Composición de Patrimonio') }}:</strong>
                                    <div class="row">
                                        <span class="col-md-12">
                                            <span id="modal-composition_assets"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button" class="btn btn-default btn-sm btn-round btn-modal-close"
                        data-dismiss="modal"
                    >
                        {{ __('Cerrar') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<!-- Modal 2 -->
<div
    id="detailsInstitutionModal1" class="modal fade" tabindex="-1" aria-labelledby="detailsInstitutionModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">DATOS DE LA ORGANIZACIÓN</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{-- Imágenes --}}
                <div class="row justify-content-center">
                    <div class="col-8 col-lg-3">
                        <p class="text-center mb-1 font-weight-bold">{{ __('Logotipo') }}</p>
                        <img
                            id="modal-logo" class="w-100" alt=""
                            src="{{ asset('/images/no-image2.png', Request::secure()) }}"
                        >
                    </div>
                    <div class="col-8 col-lg-7">
                        <p class="text-center mb-1 font-weight-bold">{{ __('Banner o Cintillo') }}</p>
                        <img id="modal-banner" class="w-100" src="{{ asset('/images/no-image3.png') }}" alt="">
                    </div>
                </div>
                {{-- Detalles --}}
                <h6 class="">{{ __('DATOS BÁSICOS') }}:</h6>
                <div class="row justify-content-center">
                    @if (config('institution.use_onapre'))
                        <div class="col-4">
                            <span class="font-weight-bold">{{ __('Código ONAPRE') }}</span>
                            <br>
                            <input
                                type="text" data-toggle="tooltip" class="form-control input-sm"
                                disabled="true" id="modal-onapre_code"
                            >
                        </div>
                    @endif
                    <div class="col-4">
                        <span class="font-weight-bold">{{ __('R.I.F.') }}</span>
                        <br>
                        <input
                            type="text" data-toggle="tooltip" class="form-control input-sm" disabled="true"
                            id="modal-rif"
                        >

                    </div>
                    <div class="col-{{ config('institution.use_onapre') ? '4' : '8' }}">
                        <span class="font-weight-bold">{{ __('Nombre') }}</span>
                        <br>
                        <input
                            type="text" data-toggle="tooltip" class="form-control input-sm" disabled="true"
                            id="modal-name"
                        >

                    </div>
                </div>
                <hr>
                <div class="row justify-content-center">
                    <div class="col-4">
                        <span class="font-weight-bold">{{ __('Acrónimo (Nombre corto)') }}</span>
                        <br>
                        <input
                            type="text" data-toggle="tooltip" class="form-control input-sm" disabled="true"
                            id="modal-acronym"
                        >

                    </div>
                    <div class="col-4">
                        <span class="font-weight-bold">{{ __('Razón Social') }}</span>
                        <br>
                        <input
                            type="text" data-toggle="tooltip" class="form-control input-sm" disabled="true"
                            id="modal-business_name"
                        >
                    </div>
                    <div class="col-4">
                        <span class="font-weight-bold">{{ __('País') }}</span>
                        <br>
                        <input
                            type="text" data-toggle="tooltip" class="form-control input-sm" disabled="true"
                            id="modal-country_id"
                        >
                    </div>
                </div>
                <hr>
                <div class="row justify-content-center">
                    <div class="col-4">
                        <span class="font-weight-bold">{{ __('Estado') }}</span>
                        <br>
                        <input
                            type="text" data-toggle="tooltip" class="form-control input-sm" disabled="true"
                            id="modal-estate_id"
                        >
                    </div>
                    <div class="col-4">
                        <span class="font-weight-bold">{{ __('Municipio') }}</span>
                        <br>
                        <input
                            type="text" data-toggle="tooltip" class="form-control input-sm" disabled="true"
                            id="modal-municipality_id"
                        >
                    </div>
                    <div class="col-4">
                        <span class="font-weight-bold">{{ __('Ciudad') }}</span>
                        <br>
                        <input
                            type="text" data-toggle="tooltip" class="form-control input-sm" disabled="true"
                            id="modal-city_id"
                        >
                    </div>
                </div>
                <hr>
                <div class="row justify-content-center">
                    <div class="col-4">
                        <span class="font-weight-bold">{{ __('Código Postal') }}</span>
                        <br>
                        <input
                            type="text" data-toggle="tooltip" class="form-control input-sm" disabled="true"
                            id="modal-postal_code"
                        >
                    </div>
                    <div class="col-4">
                        <span class="font-weight-bold">{{ __('Fecha de inicio de operaciones') }}</span>
                        <br>
                        <input
                            type="text" data-toggle="tooltip" class="form-control input-sm" disabled="true"
                            id="modal-start_operations_date"
                        >
                    </div>
                    <div class="col-4">
                        <span class="font-weight-bold">{{ __('Sectores Económicos') }}</span>
                        <br>
                        <input
                            type="text" data-toggle="tooltip" class="form-control input-sm" disabled="true"
                            id="modal-institution_sector_id"
                        >
                    </div>
                </div>
                <hr>
                <div class="row ">
                    <div class="col-4">
                        <span class="font-weight-bold">{{ __('Tipo de Organización') }}</span>
                        <br>
                        <input
                            type="text" data-toggle="tooltip" class="form-control input-sm" disabled="true"
                            id="modal-institution_type_id"
                        >
                    </div>
                </div>
                <hr>
                <div class="row justify-content-center">
                    <div class="col-12">
                        <span class="font-weight-bold">{{ __('Dirección Fiscal') }}</span>
                        <br>
                        <textarea id="modal-legal_address" rows="4" cols="40" disabled></textarea>
                    </div>
                </div>
                <hr>
                <div class="row justify-content-center">
                    <div class="col-4">
                        <span class="font-weight-bold">{{ __('Activa') }}</span>
                        <br>
                        <input
                            type="text" data-toggle="tooltip" class="form-control input-sm" disabled="true"
                            id="modal-active"
                        >
                    </div>
                    <div class="col-4">
                        <span class="font-weight-bold">{{ __('Organización por defecto') }}</span>
                        <br>
                        <input
                            type="text" data-toggle="tooltip" class="form-control input-sm" disabled="true"
                            id="modal-default"
                        >
                    </div>
                    <div class="col-4">
                        <span class="font-weight-bold">{{ __('Agente de Retención') }}</span>
                        <br>
                        <input
                            type="text" data-toggle="tooltip" class="form-control input-sm" disabled="true"
                            id="modal-retention_agent"
                        >
                    </div>
                </div>
                <hr>
                <div class="row justify-content-center">
                    <div class="col-4">
                        <span class="font-weight-bold">{{ __('Sitio web') }}</span>
                        <br>
                        <input
                            type="text" data-toggle="tooltip" class="form-control input-sm" disabled="true"
                            id="modal-web"
                        >
                    </div>
                    <div class="col-4"></div>
                    <div class="col-4"></div>
                </div>
                <hr>
                <h6>{{ __('DATOS COMPLEMENTARIOS') }}:</h6>
                <div class="row justify-content-center">
                    <div class="col-6">
                        <span class="font-weight-bold">
                            {{ __('Base Legal') }}
                        </span>
                        <br>
                        <textarea id="modal-legal_base" rows="4" cols="40" disabled></textarea>
                    </div>
                    <div class="col-6">
                        <span class="font-weight-bold">
                            {{ __('Forma Jurídica') }}
                        </span>
                        <br>
                        <textarea id="modal-legal_form" rows="4" cols="40" disabled></textarea>
                    </div>
                </div>
                <hr>
                <div class="row justify-content-center">
                    <div class="col-6">
                        <span class="font-weight-bold">
                            {{ __('Actividad Principal') }}
                        </span>
                        <br>
                        <textarea id="modal-main_activity" rows="4" cols="40" disabled></textarea>
                    </div>
                    <div class="col-6">
                        <span class="font-weight-bold">
                            {{ __('Misión') }}
                        </span>
                        <br>
                        <textarea id="modal-mission" rows="4" cols="40" disabled></textarea>
                    </div>
                </div>
                <hr>
                <div class="row justify-content-center">
                    <div class="col-6">
                        <span class="font-weight-bold">
                            {{ __('Visión') }}
                        </span>
                        <br>
                        <textarea id="modal-vision" rows="4" cols="40" disabled></textarea>
                    </div>
                    <div class="col-6">
                        <span class="font-weight-bold">
                            {{ __('Composición de Patrimonio') }}
                        </span>
                        <br>
                        <textarea id="modal-composition_assets" rows="4" cols="40" disabled></textarea>
                    </div>
                </div>
            </div>
            <hr>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@section('extra-js')
    @parent
    <script src="{{ asset('js/ckeditor.js') }}" nonce="{{ session()->get('nonce') }}"></script>
    <script nonce="{{ session()->get('nonce') }}">
        $(document).ready(function() {
            $('#country_id').on('change', function() {
                updateSelect($(this), $("#estate_id"), "Estate");
            });
            $('#estate_id').on('change', function() {
                updateSelect($(this), $("#municipality_id"), "Municipality");
                updateSelect($(this), $("#city_id"), "City");
            });
            $('.btn-show-institution').on('click', function() {
                showInstitution($(this).data('id'));
            });
            $('.datatable').on('draw.dt', function () {
                $('.btn-show-institution').on('click', function() {
                    if (!$('.modal').hasClass('show')) {
                        showInstitution($(this).data('id'));
                    }
                });
            });
            $('#delBannerImage').on('click', function() {
                deleteImage($(this), $('#banner_id').val(), '3');
            })
            $('.institution-banner').on('click', function() {
                $('input[name=banner_image]').click();
            })
            $('#delLogoImage').on('click', function() {
                deleteImage($(this), $('#logo_id').val(), '2');
            })
            $('#institution-logo').on('click', function() {
                $('input[name=logo_image]').click();
            });
            $('#logo_image').on('change', function() {
                uploadSingleImage('formImgLogo', 'logo_image', 'logo_id', 'institution-logo');
            });
            $('#banner_image').on('change', function() {
                uploadSingleImage('formImgBanner', 'banner_image', 'banner_id', 'institution-banner');
            });
            $('#btnClearInstitution').on('click', function() {
                clearInstitutionForm();
            });
            if (typeof CkEditor !== 'undefined') {
                $.each([
                    'legal_address', 'legal_base', 'legal_form', 'main_activity',
                    'mission', 'vision', 'composition_assets'
                ], function(index, element_id) {
                    CkEditor.create(document.querySelector(`#${element_id}`), {
                        toolbar: [
                            'heading', '|',
                            'bold', 'italic', 'blockQuote', 'link', 'numberedList',
                            'bulletedList', '|',
                            'insertTable'
                        ],
                        language: '{{ app()->getLocale() }}',
                    }).then(editor => {
                        window.editor = editor;
                    }).catch(error => {
                        logs('setting-institution', 489, error);
                    });
                });
            }
            @if (!is_null($paramMultiInstitution))
                $(".btn-new-institution").on('click', function() {
                    clearInstitutionForm();
                });
            @endif

            @if (old('country_id') !== '')
                console.log({{ old('country_id') }});
                $('#estate_id').prop('disable', false);
                $("#country_id").click();
            @endif
            @if (old('estate_id') !== '')
                $('#estate_id').val({{ old('estate_id') }});
                $('#municipality_id').prop('disable', false);
                $("#estate_id").click();
            @endif
            @if (old('municipality_id') !== '')
                $('#municipality_id').val({{ old('municipality_id') }});
                $('#city_id').prop('disable', false);
                $('#city_id').val({{ old('city_id') }});
                $("#municipality_id").click();
            @endif
        });

        const clearInstitutionForm = () => {
            const form = $("#card_config_institution form");
            const clearEl = {
                val: [
                    'input[type=text]',
                    'input[type=date]',
                    'select',
                    'textarea',
                    "#logo_id",
                    "#banner_id"
                ],
                attr: [

                ]
            };
            $.each(clearEl.val, function(index, el) {
                form.find(el).val('');
            });
            document.getElementById('rif').removeAttribute('value');
            document.getElementById('name').removeAttribute('value');
            document.getElementById('acronym').removeAttribute('value');
            document.getElementById('business_name').removeAttribute('value');
            document.getElementById('postal_code').removeAttribute('value');
            document.getElementById('start_operations_date').removeAttribute('value');
            document.getElementById('web').removeAttribute('value');
            document.getElementById('email').removeAttribute('value');
            document.getElementById('legal_address').innerText = '';
            document.getElementById('legal_address').textContent = '';
            form.find('.select2').trigger('change');
            form.find('input[type=checkbox]').attr('checked', false);
            form.find('input[type=radio]').attr('checked', false);

            form.find(".institution-logo").attr(
                'src',
                "{{ asset('/images/no-image2.png', Request::secure()) }}"
            );
            form.find(".institution-banner").attr(
                'src',
                "{{ asset('/images/no-image3.png', Request::secure()) }}"
            );
            app.$refs.institutionPhones._data.phones = [];
            $('#form_institution .ck-content').children().html('<p>&nbsp;</p>');
        }

        /**
         * Carga datos de la organización seleccionada
         *
         * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
         *
         * @param  {integer} id Identificador de la Organización a cargar
         */
        var loadInstitution = function(id) {
            axios.get(`get-institution/details/${id}`).then(response => {
                if (response.data.result) {
                    var institution = response.data.institution;
                    $model_institution = response.data.institution;

                    var activeSwitchRemoveClass = (institution.active) ? 'off' : 'on';
                    var activeSwitchAddClass = (institution.active) ? 'on' : 'off';
                    var defaultSwitchRemoveClass = (institution.default) ? 'off' : 'on';
                    var defaultSwitchAddClass = (institution.default) ? 'on' : 'off';
                    var retAgentSwitchRemoveClass = (institution.retention_agent) ? 'off' : 'on';
                    var retAgentSwitchAddClass = (institution.retention_agent) ? 'on' : 'off';

                    $(".institution-logo").attr('src',
                        "{{ asset('/images/no-image2.png', Request::secure()) }}");
                    $("#logo_id").val('');
                    $(".institution-banner").attr('src', "{{ asset('/images/no-image3.png') }}");
                    $("#banner_id").val('');

                    if (institution.logo) {
                        $(".institution-logo").attr('src', `${window.app_url}/${institution.logo.url}`);
                        $(".institution-logo").closest('.form-group').find('.row-delete-img').show();
                        $("#logo_id").val(institution.logo.id);
                    }

                    if (institution.banner) {
                        $(".institution-banner").attr('src', `${window.app_url}/${institution.banner.url}`);
                        $(".institution-banner").closest('.form-group').find('.row-delete-img').show();
                        $("#banner_id").val(institution.banner.id);
                    }

                    $("#institution_id").val(institution.id);
                    $("#onapre_code").val(institution.onapre_code);
                    $("#rif").val(institution.rif);
                    $("input[name=name]").val(institution.name);
                    $("#acronym").val(institution.acronym);
                    $("#business_name").val(institution.business_name);

                    if ($('#country_id').find("option[value='" + institution.municipality.estate.country.id +
                            "']").length) {
                        $('#country_id').val(institution.municipality.estate.country.id).trigger('change');
                    }
                    if ($('#city_id').find("option[value='" + institution.city_id + "']").length) {

                        $('#city_id').val(institution.city_id).trigger('change');
                    }


                    getselect($("#country_id"), institution.municipality.estate.country.id, institution
                        .municipality.estate
                        .id, $("#estate_id"), "Estate");



                    getselect($("#estate_id"), institution.municipality.estate.id, institution.municipality.id,
                        $(
                            "#municipality_id"), "Municipality");


                    $("#city_id").val(institution.city_id);

                    $("#postal_code").val(institution.postal_code);
                    $("#start_operations_date").val(institution.start_operations_date);
                    $("#institution_sector_id").val(institution.institution_sector_id);
                    $("#institution_sector_id").trigger('change');
                    $("#institution_type_id").val(institution.institution_type_id);
                    $("#institution_type_id").trigger('change');

                    $("#legal_address").val(institution.legal_address);


                    $("#web").val(institution.web);
                    $("#social_networks").val(institution.social_networks);
                    $("#social_networks").trigger('change');
                    $('#active').attr('checked', institution.active);
                    $('#active.bootstrap-switch').removeClass(`bootstrap-switch-${activeSwitchRemoveClass}`);
                    $('#active.bootstrap-switch').addClass(`bootstrap-switch-${activeSwitchAddClass}`);
                    $('#default').attr('checked', institution.default);
                    $('#default.bootstrap-switch').removeClass(`bootstrap-switch-${defaultSwitchRemoveClass}`);
                    $('#default.bootstrap-switch').addClass(`bootstrap-switch-${defaultSwitchAddClass}`);
                    $('#retention_agent').attr('checked', institution.retention_agent);
                    $('#retention_agent.bootstrap-switch').removeClass(
                        `bootstrap-switch-${retAgentSwitchRemoveClass}`);
                    $('#retention_agent.bootstrap-switch').addClass(
                        `bootstrap-switch-${retAgentSwitchAddClass}`);
                    $("#legal_base").val(institution.legal_base);
                    $("#legal_form").val(institution.legal_form);
                    $("#main_activity").val(institution.main_activity);
                    $("#mission").val(institution.mission);
                    $("#vision").val(institution.vision);
                    $("#composition_assets").val(institution.composition_assets);


                    // Lo envía a la cabecera del formulario
                    var targetOffset = $('#card_config_institution').offset().top;
                    $('html, body').animate({
                        scrollTop: targetOffset
                    }, 0);

                    // Enfoca el input de onapre
                    $("#onapre_code").focus();
                    if ($('#estate_id').find("option[value='" + institution.municipality.estate.id + "']")
                        .length) {

                        $('#estate_id').val(institution.municipality.estate.id).trigger('change');
                    }
                }
            }).catch(error => {
                console.log(error);
                logs('setting-institution', 594, error, 'loadInstitution');
            });
        }
    </script>
    <script nonce="{{ session()->get('nonce') }}">
        /**
         * Abre una modal con los datos de la institución seleccionada
         *
         * @author Angelo Osorio <adosorio@cenditel.gob.ve> | <danielking.321@gmail.com>
         *
         * @param  {integer} id Identificador de la Organización a cargar
         */
        var showInstitution = function(id) {
            if (isModalOpen) {
                return;
            }
            axios.get(`get-institution/details/${id}`).then(response => {
                if (response.data.result) {
                    isModalOpen = true;
                    var institution = response.data.institution;
                    var activeInst = (institution.active) ? 'SI' : 'NO';
                    var defaultInst = (institution.default) ? 'SI' : 'NO';
                    var retAgentInst = (institution.retention_agent) ? 'SI' : 'NO';

                    if (institution.logo) {
                        $("#modal-logo").attr('src', `${window.app_url}/${institution.logo.url}`);
                    }
                    if (institution.banner) {
                        $("#modal-banner").attr('src', `${window.app_url}/${institution.banner.url}`);
                    }
                    $("#detailsInstitutionModalLabel").html(institution.name);
                    if (institution.onapre_code) {
                        $("#modal-onapre_code").html(institution.onapre_code);

                    }
                    $("#modal-name").html(institution.name);
                    $("#modal-rif").html(institution.rif);

                    $("#modal-acronym").html(institution.acronym);
                    $("#modal-business_name").html(institution.business_name);
                    $("#modal-country_id").html(institution.municipality.estate.country.name);
                    $("#modal-estate_id").html(institution.municipality.estate.name);
                    $("#modal-municipality_id").html(institution.municipality.name);
                    getCurrentData(institution.city_id, "get-city", "#modal-city_id");
                    $("#modal-postal_code").html(institution.postal_code);
                    $("#modal-start_operations_date").html(institution.start_operations_date);
                    if (institution.organism_adscript_id) {
                        $("#modal-organism_adscript_id").html(institution.organism_adscript_id);
                    }
                    getCurrentData(institution.institution_sector_id, "get-sector",
                        "#modal-institution_sector_id");
                    getCurrentData(institution.institution_type_id, "get-type", "#modal-institution_type_id");
                    var legal = institution.legal_address;
                    $("#modal-legal_address").html($(legal).text());
                    if (institution.web) {
                        $("#modal-web").html(institution.web);
                    }
                    if (institution.email) {
                        $("#modal-email").html(institution.email);
                    }
                    if (institution.phones) {
                        let phoneTypes = {
                            'T': 'Teléfono',
                            'M': 'Movil',
                            'F': 'Fax',
                        };
                        let phones = '<ul>';
                        institution.phones.forEach(phone => {
                            phones += `<li>${phoneTypes[phone.type]}: (${phone.area_code})-${phone.number}${phone.extension ? ' Extensión: ' + phone.extension : ''}</li>`;
                        });
                        phones += '</ul>';
                        $("#modal-phones").html(phones);
                    }
                    $('#modal-active').html(activeInst);
                    $('#modal-default').html(defaultInst);
                    $('#modal-retention_agent').html(retAgentInst);
                    if (institution.legal_base) {
                        var legal_base_modal = institution.legal_base;
                        $("#modal-legal_base").html($(legal_base_modal).text());
                    }
                    if (institution.legal_form) {
                        var legal_form_modal = institution.legal_form;
                        $("#modal-legal_form").html($(legal_form_modal).text());
                    }
                    if (institution.main_activity) {
                        var legal_main_activity_modal = institution.main_activity;
                        $("#modal-main_activity").html($(legal_main_activity_modal).text());
                    }
                    if (institution.mission) {
                        var legal_mission_modal = institution.mission;
                        $("#modal-mission").html($(legal_mission_modal).text());
                    }
                    if (institution.vision) {
                        var legal_vision_modal = institution.vision;
                        $("#modal-vision").html($(legal_vision_modal).text());
                    }
                    if (institution.composition_assets) {
                        var composition_assets_modal = institution.composition_assets;
                        $("#modal-composition_assets").html($(composition_assets_modal).text());
                    }

                    // Abre la modal
                    $('#detailsInstitutionModal').modal('show');
                    $('#detailsInstitutionModal').on('hidden.bs.modal', function () {
                        isModalOpen = false; // Cambia el estado a cerrado cuando el modal se oculta
                    });
                }
            }).catch(error => {
                logs('setting-institution', 594, error, 'loadInstitution');
            });
        }

        function getselect(parent_element, id, target_element_id, target_element, target_model, module_name) {

            var module_name = (typeof(module_name) !== "undefined") ? '/' + module_name : '';
            var parent_id = id;
            var parent_name = parent_element.attr('id');

            target_element.empty();

            axios.get(
                `/get-select-data/${parent_name}/${parent_id}/${target_model}${module_name}`
            ).then(response => {
                if (response.data.result) {
                    target_element.attr('disabled', false);
                    $.each(response.data.records, function(index, record) {

                        if (record['id'] == target_element_id) {
                            target_element.append(
                                `<option value="${record['id']}" selected >${record['name']}</option>`
                            );


                        } else {
                            target_element.append(
                                `<option value="${record['id']}" >${record['name']}</option>`);

                        }
                    });
                }
            }).catch(error => {

            })

        }
        /**
         * Busca el nombre del dato requerido y lo agrega a la modal después de realizada la consulta
         *
         * @author Angelo Osorio <adosorio@cenditel.gob.ve> | <danielking.321@gmail.com>
         *
         * @param  {integer} id Identificador de la consulta
         * @param  {string} url URL de la consulta
         * @param  {string} target ID del target a cambiar
         */
        function getCurrentData(id, url, target) {
            axios.get(`${url}/${id}`).then(response => {
                $(target).html(response.data.result.name);
            });
        }
    </script>
@endsection
