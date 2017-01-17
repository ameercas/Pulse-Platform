@extends('../app.layouts.partial')

@section('content')
	<ul class="breadcrumb breadcrumb-page">
		<div class="breadcrumb-label text-light-gray">{{ trans('global.you_are_here') }} </div>
		<li><a href="{{ trans('global.home_crumb_url') }}">{{ trans('global.home_crumb_text') }}</a></li>
		<li><a href="#/geofences">{{ trans('global.geofences') }}</a></li>
		<li class="active">{{ trans('global.edit_geofence') }}</li>
	</ul>

	<div class="page-header">
		<h1><i class="fa fa-map-marker page-header-icon"></i> {{ trans('global.edit_geofence') }}</h1>
	</div>

<?php
echo Former::open()
	->class('form ajax ajax-validate')
	->action(url('api/v1/geofence/save'))
	->method('POST');

echo Former::hidden()
		->name('sl')
		->forceValue($sl);
?>
		  <div class="panel"> 
		   <div class="panel-body padding-sm">
<?php

$value = (isset($geofence->locationGroup->id)) ? json_encode(['id' => $geofence->locationGroup->id, 'text' => $geofence->locationGroup->name]) : '';

echo Former::text()
    ->name('group')
    ->useDatalist($location_groups, 'name')
    ->value($value)
	->class('select2-datalist form-control')
    ->autocomplete('off')
    ->help(trans('global.location_group_info'))
	->label(trans('global.location'));

echo '<hr class="no-grid-gutter-h" style="clear:both">';

echo Former::text()
    ->name('name')
    ->autocomplete('off')
    ->help(trans('global.geofence_name_info'))
	->dataBvNotempty()
    ->autofocus()
    ->required()
    ->forceValue($geofence->name)
	->label(trans('global.name'));

echo '<hr class="no-grid-gutter-h" style="clear:both">';

echo Former::text()
    ->name('location')
    ->autocomplete('off')
    ->autofocus()
    ->forceValue(($geo != NULL) ? '' : $geofence_location)
    ->help(trans('global.location_map_info'))
	->label(trans('global.map_location'));

echo '<hr class="no-grid-gutter-h" style="clear:both">';

echo Former::actions(
    Former::submit(trans('global.save'))->class('btn-lg btn-primary btn')->id('btn-submit'),
    Former::link(trans('global.cancel'))->class('btn-lg btn-default btn')->href('#/geofences')
);
?>
			 </div>
		   </div>
		</div>
<?php
echo Former::close();
?>
<script>
var optsMap = {
	zoom: {{ ($geo != NULL) ? 1 : 16; }},
	center: L.latLng([ {{ $geofence_location }} ]),
	zoomControl: false,
	attributionControl: false
};

$('#location').leafletLocationPicker({
	locationFormat: '{lat},{lng}',
	width: 420,
	height: 280,
	position: 'bottomleft',
	cursorSize: '20px',
	map: optsMap
});

function formSubmittedSuccess(r)
{
    if(r.result == 'error')
    {
        return;
    }

	// Open geofence overview
	document.location = '#/geofences';
}
</script>
@stop