@extends('../app.layouts.partial')

@section('content')
	<ul class="breadcrumb breadcrumb-page">
		<div class="breadcrumb-label text-light-gray">{{ trans('global.you_are_here') }} </div>
		<li><a href="{{ trans('global.home_crumb_url') }}">{{ trans('global.home_crumb_text') }}</a></li>
		<li><a href="#/beacons">{{ trans('global.beacons') }}</a></li>
		<li class="active">{{ trans('global.new_beacon') }}</li>
	</ul>

	<div class="page-header">
		<h1><i class="fa fa-dot-circle-o page-header-icon"></i> {{ trans('global.new_beacon') }}</h1>
	</div>

<?php
echo Former::open()
	->class('form ajax ajax-validate')
	->action(url('api/v1/beacon/save'))
	->method('POST');
?>
		  <div class="panel"> 
		   <div class="panel-body padding-sm padding-t"> 
			<div class="note note-info">{{ trans('global.add_beacon_global_info') }}</div>
<?php
echo Former::text()
    ->name('name')
    ->autocomplete('off')
    ->placeholder(trans('global.beacon_name_info'))
	->dataBvNotempty()
    ->required()
    ->help(trans('global.beacon_name_info_info'))
	->label(trans('global.name'));
	
echo Former::text()
    ->name('group')
    ->useDatalist($location_groups, 'name')
    /*->value('{"id": "1", "text": "Store" }')*/
	->class('select2-datalist form-control')
    ->autocomplete('off')
	->label(trans('global.group'));

echo '<hr class="no-grid-gutter-h" style="clear:both">';

echo Former::text()
    ->name('uuid')
    ->autocomplete('off')
    ->placeholder(trans('global.uuid_info'))
    ->forceValue('')
    ->required()
	->dataBvNotempty()
	->label(trans('global.uuid'))
    ->append('<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . trans('global.vendors') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu dropdown-menu-right">
          <li><a href="javascript:void(0);" onclick="$(\'#uuid\').val(\'fda50693-a4e2-4fb1-afcf-c6eb07647825\')">AXAET</a></li>
          <li><a href="javascript:void(0);" onclick="$(\'#uuid\').val(\'ACFD065E-C3C0-11E3-9BBE-1A514932AC01\')">BlueUp</a></li>
          <li><a href="javascript:void(0);" onclick="$(\'#uuid\').val(\'B9407F30-F5F8-466E-AFF9-25556B57FE6D\')">Estimote</a></li>
          <li><a href="javascript:void(0);" onclick="$(\'#uuid\').val(\'11E44F09-4EC4-407E-9203-CF57A50FBCE0\')">GeLo</a></li>
          <li><a href="javascript:void(0);" onclick="$(\'#uuid\').val(\'F7826DA6-4FA2-4E98-8024-BC5B71E0893E\')">Kontakt.io</a></li>
          <li><a href="javascript:void(0);" onclick="$(\'#uuid\').val(\'A4956969-C5B1-4B44-B512-1370F02D74DE\')">Lightblue Bean</a></li>
          <li><a href="javascript:void(0);" onclick="$(\'#uuid\').val(\'52414449-5553-4E45-5457-4F524B53434F\')">Radius Network</a></li>
          <li><a href="javascript:void(0);" onclick="$(\'#uuid\').val(\'24DDF411-8CF1-440C-87CD-E368DAF9C93E\')">RECO</a></li>
          <li><a href="javascript:void(0);" onclick="$(\'#uuid\').val(\'92AB49BE-4127-42F4-B532-90FAF1E26491\')">TwoCanoes</a></li>
        </ul>');

    //->append(Former::button('<i class="fa fa-random"></i>')->tooltip(trans('global.generate_uuid'))->tooltipPlacement('top')->id('generate_uuid'));

echo Former::number()
    ->name('major')
    ->autocomplete('off')
    ->required()
	->label(trans('global.major'));

echo Former::number()
    ->name('minor')
    ->autocomplete('off')
    ->help(trans('global.help_major_minor'))
    ->required()
	->label(trans('global.minor'));

echo '<hr class="no-grid-gutter-h" style="clear:both">';

echo Former::actions(
    Former::submit(trans('global.save'))->class('btn-lg btn-primary btn')->id('btn-submit'),
    Former::link(trans('global.cancel'))->class('btn-lg btn-default btn')->href('#/beacons')
);

?>
			</div>
		  </div>
		</div>
<?php
echo Former::close();
?>
<script>
<?php /*
var optsMap = {
	zoom: {{ ($geo['latitude'] == 0) ? 1 : 16; }},
	center: L.latLng([ {{ $geo['latitude'] }}, {{ $geo['longitude'] }} ]),
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
*/ ?>
$('#generate_uuid').on('click', function() {
	$('#uuid').val( guid() );
});

function formSubmittedSuccess(r)
{
    if(r.result == 'error')
    {
        return;
    }

    /* Increment Beacon count */
    var count = parseInt($('#count_beacons').text());
    $('#count_beacons').text(count+1);

	/* Open beacon overview */
	document.location = '#/beacons';
}
</script>
@stop