<div class="modal-dialog" style="width:800px">
<?php
echo Former::open()
  ->class('form-horizontal validate')
  ->action(url('api/v1/app/app-settings'))
  ->method('POST');

echo Former::hidden()
  ->value($sl)
  ->name('sl');
?>
  <div class="modal-content">
    <div class="modal-header">
      <button class="close" type="button" data-dismiss="modal">×</button>
      <?php echo trans('global.app_settings') ?>
    </div>
    <div class="modal-body">

      <div class="container-fluid">

        <ul class="nav nav-tabs">
          <li class="active">
            <a href="#tab-general" data-toggle="tab" onclick="return false;">{{ trans('global.general') }}</a>
          </li>
          <li>
            <a href="#tab-mobile-app" data-toggle="tab" onclick="return false;">{{ trans('global.mobile_app') }}</a>
          </li>
          <li>
            <a href="#tab-social" data-toggle="tab" onclick="return false;">{{ trans('global.social_share') }}</a>
          </li>
          <li>
            <a href="#tab-tracking" data-toggle="tab" onclick="return false;">{{ trans('global.tracking_codes') }}</a>
          </li>
          <li>
            <a href="#tab-js" data-toggle="tab" onclick="return false;">{{ trans('global.js') }}</a>
          </li>
          <li>
            <a href="#tab-css" data-toggle="tab" onclick="return false;">{{ trans('global.css') }}</a>
          </li>
        </ul>

        <div class="tab-content tab-content-bordered">
          <div class="tab-pane fade active in" id="tab-general">

<?php
echo Former::text()
  ->name('name')
  ->forceValue($app->name)
  ->label(trans('global.name'))
  ->dataFvNotempty()
  ->dataFvNotemptyMessage(trans('global.please_enter_a_value'))
  ->required();

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';

echo Former::text()
  ->name('local_domain')
  ->forceValue($app->local_domain)
  ->label(trans('global.domain'))
  ->help(' ')
  ->prepend($protocol . \Request::server('HTTP_HOST') . '/mobile/');


if ($domain)
{
  echo Former::text()
    ->name('domain')
    ->forceValue($app->domain)
    ->label(trans('global.custom_domain'))
    ->help(trans('global.custom_domain_info', array('host' => \Request::server('HTTP_HOST'))))
    ->prepend('http://');
}

echo '<hr class="no-grid-gutter-h grid-gutter-margin-b no-margin-t">';

echo Former::select('language')
  ->class('select2-required form-control')
  ->name('language')
  ->id('language')
  ->forceValue($app->language)
  ->options(trans('languages.languages'))
  ->label(trans('global.language'));

echo Former::select('timezone')
  ->class('select2-required form-control')
  ->name('timezone')
  ->forceValue($app->timezone)
  ->options(trans('timezones.timezones'))
  ->label(trans('global.timezone'));

$settings = json_decode($app->settings);

$head_tag = (isset($settings->head_tag)) ? $settings->head_tag : '';
$end_of_body_tag = (isset($settings->end_of_body_tag)) ? $settings->end_of_body_tag : '';
$js = (isset($settings->js)) ? $settings->js : '';
$css = (isset($settings->css)) ? $settings->css : '';
?>

          </div>
          <div class="tab-pane fade" id="tab-mobile-app">
<?php

$field_name = 'header';
$img_value = (isset($settings->header)) ? $settings->header : '';

?>
<div class="form-group">
	<label for="name" class="control-label col-lg-2 col-sm-4">{{ trans('global.header') }}</label>
	<div class="col-lg-10 col-sm-8">
	
		<input type="hidden" name="{{ $field_name }}" id="{{ $field_name }}" value="{{ $img_value }}">

		<div class="btn-group" role="group">
			<button type="button" class="btn btn-primary img-browse" data-id="{{ $field_name }}"><i class="fa fa-picture-o"></i> {{ trans('global.select_image') }}</button>
			<button type="button" class="btn btn-danger img-remove" data-id="{{ $field_name }}" title="{{ trans('global.remove_image') }}"><i class="fa fa-remove"></i></button>
		</div>

		<div id="{{ $field_name }}-image">
<?php
if($img_value != '')
{
    echo '<img src="' . $img_value . '" class="thumbnail widget-thumb" style="margin-top:10px;max-width:280px">';
}
?>
		</div>

		<p class="help-block">{{ trans('global.header_info') }}</p>
	</div>
</div>
          </div>
          <div class="tab-pane fade" id="tab-social">
<?php
echo '<div class="row">';
echo '<div class="col-md-6">';

$social_buttons = array(
  'email' => 1, 
  'twitter' => 1, 
  'facebook' => 1, 
  'googleplus' => 1, 
  'linkedin' => 1, 
  'pinterest' => 1
);

$social = (isset($settings->social)) ? (array) $settings->social : $social_buttons;

foreach ($social as $button => $checked)
{
  $check = ((boolean) $checked) ? ' checked' : '';
  echo '<div class="form-group">';
  echo '<div class="col-lg-10 col-sm-8"><div class="checkbox"><label for="social_' . $button . '" class=""><input class="form-control" type="hidden" name="social[' . $button . ']" value="0">';
  echo '<input type="checkbox" data-class="" name="social[' . $button . ']" value="1" novalidate' . $check . '><span class="text">' . trans('global.' . (string) $button) . '</span>';
  echo '</div>';
  echo '</div>';
  echo '</div>';
}

echo '</div>';
echo '<div class="col-md-6">';

$social_size = (isset($settings->social_size)) ? $settings->social_size : 12;

$social_sizes = array_combine(range(8,24), range(8,24));

echo Former::select('role')
  ->class('select2-required form-control')
  ->style('width:90px')
  ->name('social_size')
  ->forceValue($social_size)
  ->options($social_sizes)
  ->label(trans('global.size'));

$social_icons_only = (isset($settings->social_icons_only)) ? $settings->social_icons_only : 0;

echo Former::checkbox()
  ->name('social_icons_only')
  ->label('')
  ->text('<span class="text">' . trans('global.icons_only') . '</span>')
  ->check((boolean) $social_icons_only)
  ->dataClass('switcher-success')
  ->novalidate();

$social_show_count = (isset($settings->social_show_count)) ? $settings->social_show_count : 0;

echo Former::checkbox()
  ->name('social_show_count')
  ->label('')
  ->text('<span class="text">' . trans('global.show_count') . '</span>')
  ->check((boolean) $social_show_count)
  ->dataClass('switcher-success')
  ->novalidate();

echo '</div>';
echo '</div>';
?>

          </div>
          <div class="tab-pane fade" id="tab-tracking">
<?php
echo '<p>' . trans('global.head_tag_tracking') . '</p>';

echo '<textarea name="head_tag" id="head_tag" rows="6" class="form-control">' . $head_tag . '</textarea>';

echo '<br><p>' . trans('global.end_of_body_tag_tracking') . '</p>';

echo '<textarea name="end_of_body_tag" id="end_of_body_tag" rows="6" class="form-control">' . $end_of_body_tag . '</textarea>';
?>

          </div>
          <div class="tab-pane fade" id="tab-js">
<?php
echo '<p>' . trans('global.global_js_msg') . '</p>';

echo '<textarea name="js" id="js" rows="12" class="form-control">' . $js . '</textarea>';
?>


          </div>
          <div class="tab-pane fade" id="tab-css">
<?php
echo '<p>' . trans('global.global_css_msg') . '</p>';

echo '<textarea name="css" id="css" rows="12" class="form-control">' . $css . '</textarea>';
?>
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
function formSubmittedSuccess(result, r)
{
  if (result == 'error')
  {
    $('#local_domain').closest('.form-group').addClass('has-error');
    $('#local_domain').parent().next().html(r.msg);
  }
  else
  {
    parent.$('#app_name').editable('setValue', $('#name').val());
    /*parent.reloadPreview();*/
    local_domain = $('#local_domain').val();
    document.getElementById('device-screen').contentDocument.location = r.url;
    angular.element(document.getElementById('qrModal')).scope().url = r.url;
    parent.showSaved();
    $modal.modal('hide');
  }
}

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
			$('#' + requestingField + '-image').html('<img src="' + filePath + '" class="thumbnail" style="max-width:280px; margin:10px 0 0 0">');
			$('#' + requestingField + '-image').removeClass('bg-loading');
		});

    $('#' + requestingField).val(img);
    }
}
</script>