@extends('citizenservice::layouts.master')

@section('maproute-icon')
    <i class="icofont icofont-users-social"></i>
@stop

@section('maproute-icon-mini')
    <i class="icofont icofont-users-social"></i>
@stop

@section('maproute-actual')
    {{ __('Atención / Ciudadano') }}
@stop

@section('maproute-title')
    {{ __('Catacterización de Comunidad') }}
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="cardCitizenServiceCommunityProfilingForm">
                <div class="card-header">
                        <h6 class="card-title">
                            {{ __('Catacterización de Comunidad') }}
                            @include('buttons.help', [
                                'helpId' => 'CitizenServiceCommunityProfilingForm',
                                'helpSteps' => get_json_resource(
                                    'ui-guides/community-profilings/community_profiling_form.json',
                                    'citizenservice'
                                )
                            ])
                        </h6>

                        <div class="card-btns">
                            @include('buttons.previous', ['route' => url()->previous()])
                            @include('buttons.minimize')
                        </div>
                </div>
                <citizenservice-community-profiling-form
                    route_list="{{ url('citizenservice/community-profilings') }}"
                    @if (isset($communityProfiling))
                        initial_data="{{ json_encode($communityProfiling) }}"
                    @endif
                ></citizenservice-community-profiling-form>
            </div>
        </div>
    </div>
@endsection
