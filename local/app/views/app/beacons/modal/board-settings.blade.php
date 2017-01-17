<div class="modal-dialog" style="width:800px">
<?php
echo Former::open()
	->class('form-horizontal validate')
	->action(url('api/v1/scenario/board-settings'))
	->method('POST');

echo Former::hidden()
    ->value($sl)
    ->name('sl');
?>
	<div class="modal-content">
		<div class="modal-header">
			<button class="close" type="button" data-dismiss="modal">×</button>
			<?php echo trans('global.board_settings') ?>
        </div>
		<div class="modal-body">

			<div class="container-fluid">

				<ul class="nav nav-tabs">
					<li class="active">
						<a href="#tab-general" data-toggle="tab" onclick="return false;">{{ trans('global.general') }}</a>
					</li>
					<li>
						<a href="#tab-photo" data-toggle="tab" onclick="return false;">{{ trans('global.photo') }}</a>
					</li>
				</ul>

				<div class="tab-content tab-content-bordered">
					<div class="tab-pane fade active in" id="tab-general">

<?php
echo Former::text()
    ->name('name')
    ->forceValue($scenario_board->name)
	->label(trans('global.name'))
	->dataFvNotempty()
	->dataFvNotemptyMessage(trans('global.please_enter_a_value'))
    ->required();

echo '<div class="select2-primary" style="margin-bottom:18px">';
echo Former::select('apps')
	->class('select2-multiple')
    ->name('apps[]')
    ->multiple(true)
    ->select($scenario_board->apps->lists('id'))
	->options($apps_select)
	->label(trans('global.apps'));
echo '</div>';

echo '<div class="select2-primary" style="margin-bottom:18px">';
echo Former::select('sites')
	->class('select2-multiple')
    ->name('sites[]')
    ->multiple(true)
    ->select($scenario_board->sites->lists('id'))
	->options($sites_select)
	->label(trans('global.one_pages'));
echo '</div>';

echo Former::select('timezone')
	->class('select2-required form-control')
    ->name('timezone')
    ->forceValue($scenario_board->timezone)
	->options(trans('timezones.timezones'))
	->label(trans('global.timezone'));

?>
					</div>

					<div class="tab-pane fade" id="tab-photo">
<?php

$field_name = 'photo';
$field_value = ($scenario_board->photo_file_name != '') ? $scenario_board->photo->url() : '';
$img_value = ($scenario_board->photo_file_name != '') ? $scenario_board->photo->url() : '';
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
if($img_value != '')
{
    echo '<img src="' . $img_value . '" class="thumbnail widget-thumb" style="margin-top:10px;max-width:280px;">';
}
?>
		</div>

		<p class="help-block">{{ trans('global.board_photo_help') }}</p>
	</div>
</div>

					</div>
				</div>


			</div>

		</div>
		<div class="modal-footer">
			<button class="btn btn-primary" type="submit"><?php echo Lang::get('global.save') ?></button>
			<button class="btn" data-dismiss="modal" type="button"><?php echo Lang::get('global.close') ?></button>
		</div>
<?php
echo Former::close();
?>
	</div>
</div>
<script>

var elfinderUrl = 'elfinder/standalonepopup/';

$('.img-browse').on('click', function()
{
	// trigger the reveal modal with elfinder inside
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
		var w = (typeof $('#' + requestingField + '-image').attr('data-w') !== 'undefined') ? $('#' + requestingField + '-image').attr('data-w') : 280;
		var h = (typeof $('#' + requestingField + '-image').attr('data-h') !== 'undefined') ? $('#' + requestingField + '-image').attr('data-h') : 210;
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

function formSubmittedSuccess()
{
    parent.$('#board_name').editable('setValue', $('#name').val());
    parent.showSaved();
    $modal.modal('hide');
}
</script>