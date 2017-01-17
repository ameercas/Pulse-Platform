@extends('../app.layouts.partial')

@section('content')
		<ul class="breadcrumb breadcrumb-page">
			<div class="breadcrumb-label text-light-gray">{{ trans('global.you_are_here') }} </div>
			<li><a href="{{ trans('global.home_crumb_url') }}">{{ trans('global.home_crumb_text') }}</a></li>
			<li><a href="#/boards">{{ trans('global.scenario_boards') }}</a></li>
			<li class="active">{{ trans('global.new_scenario_board') }}</li>
		</ul>

		<div class="page-header">
			<div class="row">
				<h1 class="col-xs-12 col-sm-4 text-center text-left-sm"><i class="ion-android-notifications page-header-icon"></i> {{ trans('global.new_scenario_board') }}</h1>

				<div class="col-xs-12 col-sm-8">
					<div class="row">
						<hr class="visible-xs no-grid-gutter-h">

					</div>
				</div>
			</div>
		</div>

		  <div class="panel"> 
		   <div class="panel-body padding-sm padding-t"> 

			<div class="note note-info">{{ trans('global.new_scenario_board_info') }}</div>
<?php
echo Former::open()
	->class('form ajax ajax-validate')
	->action(url('api/v1/scenario/board'))
	->method('POST');

echo Former::text()
    ->name('name')
    ->autocorrect('off')
	->required()
	->dataBvNotempty()
	->label(trans('global.name'));

echo '<hr class="no-grid-gutter-h" style="clear:both">';

echo '<div class="select2-primary">';
echo Former::select('apps')
	->class('select2-multiple')
    ->name('apps[]')
    ->multiple(true)
	->options($apps_select)
	//->fromQuery($apps, 'name', 'id')
	->label(trans('global.apps'))
	/*->help(trans('global.board_apps_help'))*/;
echo '</div>';

echo '<div class="select2-primary">';
echo Former::select('sites')
  ->class('select2-multiple')
  ->name('sites[]')
  ->multiple(true)
  ->options($sites_select)
  //->fromQuery($apps, 'name', 'id')
  ->label(trans('global.one_pages'))
  ->help(trans('global.board_one_pages_help'));
echo '</div>';

echo '<hr class="no-grid-gutter-h" style="clear:both">';

echo Former::select('timezone')
	->class('select2-required form-control')
    ->name('timezone')
    ->forceValue(Auth::user()->timezone)
	->options(trans('timezones.timezones'))
	->help(trans('global.board_timezone_help'))
	->label(trans('global.timezone'));

echo '<hr class="no-grid-gutter-h" style="clear:both">';

$field_name = 'photo';
$field_value = '';
$field_help = trans('global.board_photo_help');

?>
<div class="form-group">
	<label for="name" class="control-label col-lg-2 col-sm-4">{{ trans('global.photo') }}</label>
	<div class="col-lg-10 col-sm-8">
	
	<input type="hidden" name="{{ $field_name }}" id="{{ $field_name }}" value="{{ $field_value }}">

	<div class="btn-group" role="group">
		<button type="button" class="btn btn-primary img-browse" data-id="{{ $field_name }}"><i class="fa fa-picture-o"></i> {{ trans('global.select_image') }}</button>
		<button type="button" class="btn btn-danger img-remove" data-id="{{ $field_name }}" title="{{ trans('global.remove_image') }}"><i class="fa fa-remove"></i></button>
	</div>

	<div id="{{ $field_name }}-image">
<?php
if($field_value != '')
{
    echo '<img src="' . url($field_value) . '" class="thumbnail widget-thumb" style="margin-top:10px;">';
}
?>
	</div>

		<p class="help-block">{{ trans('global.board_photo_help') }}</p>
	</div>
</div>

<?php
echo '<hr class="no-grid-gutter-h" style="clear:both">';

echo Former::actions(
    Former::submit(trans('global.save'))->class('btn-lg btn-primary btn')->id('btn-submit'),
    Former::link(trans('global.cancel'))->class('btn-lg btn-default btn')->href('#/boards')
);

echo Former::close();
?>
		   </div> 
		  </div>


<script>
$('.select2-multiple').select2();

var elfinderUrl = 'elfinder/standalonepopup/';

$('.img-browse').on('click', function()
{
	/* trigger the reveal modal with elfinder inside*/
	$.colorbox(
	{
		href: elfinderUrl + $(this).attr('data-id') + '/processWidgetFile',
		fastIframe: true,
		iframe: true,
		width: '70%',
		height: '80%'
	});

	return false;
});

$('.img-remove').on('click', function()
{
	$('#' + $(this).attr('data-id') + '-image').html('');
	$('#' + $(this).attr('data-id')).val('');

	return false;
});

// Callback after elfinder selection
window.processWidgetFile = function(filePath, requestingField)
{
    if($('#' + requestingField).attr('type') == 'text')
    {
	    $('#' + requestingField).val(decodeURI(filePath));
    }

    if($('#' + requestingField + '-image').length)
    {
		var w = (typeof $('#' + requestingField + '-image').attr('data-w') !== 'undefined') ? $('#' + requestingField + '-image').attr('data-w') : 140;
		var h = (typeof $('#' + requestingField + '-image').attr('data-h') !== 'undefined') ? $('#' + requestingField + '-image').attr('data-h') : 105;
		var img = decodeURI(filePath);
		var thumb = '{{ url('/api/v1/thumb/nail?') }}w=' + w + '&h=' + h + '&img=' + filePath;

		$('#' + requestingField + '-image').addClass('bg-loading');

		$('<img/>').attr('src', decodeURI(thumb)).load(function() {
			$(this).remove();
			$('#' + requestingField + '-image').html('<img src="' + thumb + '" class="thumbnail" style="max-width:100%; margin:10px 0 0 0">');
			$('#' + requestingField + '-image').removeClass('bg-loading');
		});

        $('#' + requestingField).val(img);
    }
}

function formSubmittedSuccess(r)
{
    // Increment count
    var count = parseInt($('#count_boards').text());
    $('#count_boards').text(count+1);

	if(r.result == 'success') document.location = '#/board/' + r.sl;
}
</script>
@stop