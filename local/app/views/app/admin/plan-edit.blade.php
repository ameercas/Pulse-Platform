@extends('../app.layouts.partial')

@section('content')
	<ul class="breadcrumb breadcrumb-page">
		<div class="breadcrumb-label text-light-gray">{{ trans('global.you_are_here') }} </div>
		<li><a href="{{ trans('global.home_crumb_url') }}">{{ trans('global.home_crumb_text') }}</a></li>
		<li>{{ trans('admin.system_administration') }}</li>
		<li><a href="#/admin/plans">{{ trans('admin.user_plans') }}</a></li>
		<li class="active">{{ trans('admin.edit_plan') }}</li>
	</ul>

	<div class="page-header">
		<h1><i class="fa fa-trophy page-header-icon"></i> {{ trans('admin.edit_plan') }}</h1>
	</div>

	<div class="panel">
		<div class="panel-body padding-sm">
<?php
//Former::setOption('default_form_type', 'vertical');

echo Former::open()
  ->class('form-horizontal validate')
  ->action(url('api/v1/admin/plan-update'))
  ->method('POST');

echo Former::hidden()
  ->name('sl')
  ->forceValue($sl);

echo '<div class="row">';
echo '<div class="col-md-10">';

echo '<legend>' . trans('global.general') . '</legend>';

echo Former::text()
  ->name('name')
  ->forceValue($plan->name)
  ->label(trans('global.name'))
  ->dataBvNotempty()
  ->autocapitalize('off')
  ->autocorrect('off')
  ->autocomplete('off')
  ->required();

echo '<hr>';
echo '<p style="margin:10px 0 ">' . trans('admin.external_urls_info') . '</p><br style="clear:both">';

$order_url = (isset($settings->order_url)) ? $settings->order_url : '';

echo Former::text()
  ->name('order_url')
  ->forceValue($order_url)
  ->placeholder('http://')
  ->label(trans('admin.order_url'))
  ->autocapitalize('off')
  ->autocorrect('off')
  ->autocomplete('off')
  ->help(trans('admin.order_url_info'));

$upgrade_url = (isset($settings->upgrade_url)) ? $settings->upgrade_url : '';

echo Former::text()
  ->name('upgrade_url')
  ->forceValue($upgrade_url)
  ->placeholder('http://')
  ->label(trans('admin.upgrade_url'))
  ->autocapitalize('off')
  ->autocorrect('off')
  ->autocomplete('off')
  ->help(trans('admin.upgrade_url_info'));

$product_id = (isset($settings->product_id)) ? $settings->product_id : '';

echo Former::text()
  ->name('product_id')
  ->forceValue($product_id)
  ->label(trans('admin.product_id'))
  ->autocapitalize('off')
  ->autocorrect('off')
  ->autocomplete('off')
  ->help(trans('admin.product_id_info'));

echo '</div>';
echo '<div class="col-md-6">';

echo '</div>';
echo '</div>';

echo '<legend>' . trans('admin.limitations') . '</legend>';
echo '<div class="row">';
echo '<div class="col-md-10">';

$interactions = (isset($settings->interactions)) ? $settings->interactions : 100;

echo Former::number()
  ->name('interactions')
  ->forceValue($interactions)
  ->step('any')
  ->label('&nbsp;')
  ->append(trans('global.interactions'))
  ->help(trans('admin.interactions_info'));


$disk_space = (isset($settings->disk_space)) ? $settings->disk_space : 1;

echo Former::number()
  ->name('disk_space')
  ->forceValue($disk_space)
  ->step('any')
  ->label(trans('admin.disk_space'))
  ->append('MB');

/*
$support_settings = (isset($settings->support)) ? $settings->support : '-';

echo Former::text()
  ->name('support')
  ->forceValue($support_settings)
  ->label(trans('admin.support'))
  ->help(trans('admin.support_info'));
*/
$max_apps_settings = (isset($settings->max_apps)) ? $settings->max_apps : 0;

$max_apps = array_combine(range(1,100), range(1,100));
array_unshift($max_apps, trans('admin.unlimited'));

echo Former::select()
	->class('select2-required form-control')
    ->name('max_apps')
    ->forceValue($max_apps_settings)
	->options($max_apps)
	->label(trans('admin.max_apps'));

$max_sites_settings = (isset($settings->max_sites)) ? $settings->max_sites : 0;

$max_sites = array_combine(range(1,100), range(1,100));
array_unshift($max_sites, trans('admin.unlimited'));

echo Former::select()
	->class('select2-required form-control')
    ->name('max_sites')
    ->forceValue($max_sites_settings)
	->options($max_sites)
	->label(trans('admin.max_sites'));

$max_beacons_settings = (isset($settings->max_beacons)) ? $settings->max_beacons : 1;

$max_beacons = array_combine(range(1,100), range(1,100));
array_unshift($max_beacons, trans('admin.unlimited'));

echo Former::select()
	->class('select2-required form-control')
    ->name('max_beacons')
    ->forceValue($max_beacons_settings)
	->options($max_beacons)
	->label(trans('admin.max_beacons'));

$max_geofences_settings = (isset($settings->max_geofences)) ? $settings->max_geofences : 1;

$max_geofences = array_combine(range(1,100), range(1,100));
array_unshift($max_geofences, trans('admin.unlimited'));

echo Former::select()
	->class('select2-required form-control')
    ->name('max_geofences')
    ->forceValue($max_geofences_settings)
	->options($max_geofences)
	->label(trans('admin.max_geofences'));

$max_boards_settings = (isset($settings->max_boards)) ? $settings->max_boards : 1;

$max_boards = array_combine(range(1,100), range(1,100));
array_unshift($max_boards, trans('admin.unlimited'));

echo Former::select()
	->class('select2-required form-control')
    ->name('max_boards')
    ->forceValue($max_boards_settings)
	->options($max_boards)
	->label(trans('admin.max_boards'));

$max_scenarios_settings = (isset($settings->max_scenarios)) ? $settings->max_scenarios : 3;

$max_scenarios = array_combine(range(1,100), range(1,100));
array_unshift($max_scenarios, trans('admin.unlimited'));

echo Former::select()
	->class('select2-required form-control')
    ->name('max_scenarios')
    ->forceValue($max_scenarios_settings)
	->options($max_scenarios)
	->label(trans('admin.max_scenarios'));

$domain_settings = (isset($settings->domain)) ? $settings->domain : true;
$checked = ($domain_settings) ? ' checked' : '';

echo '<div class="form-group"><input type="hidden" name="domain" value="0">
	<label class="control-label control-label col-lg-4 col-sm-4">' . trans('global.custom_domain') . '</label>
	<div class="col-lg-8 col-sm-8"><div class="checkbox">
	<input data-class="switcher-success" type="checkbox" name="domain" value="1"' . $checked . '></div></div></div>';

$publish_settings = (isset($settings->publish)) ? $settings->publish : true;
$checked = ($publish_settings) ? ' checked' : '';

echo '<div class="form-group"><input type="hidden" name="publish" value="0">
	<label class="control-label control-label col-lg-4 col-sm-4">' . trans('global.publish') . ' ' . trans('global.one_pages') . '</label>
	<div class="col-lg-8 col-sm-8"><div class="checkbox">
	<input data-class="switcher-success" type="checkbox" name="publish" value="1"' . $checked . '></div></div></div>';
/*
$download_settings = (isset($settings->download)) ? $settings->download : true;
$checked = ($download_settings) ? ' checked' : '';

echo '<div class="form-group"><input type="hidden" name="download" value="0">
	<label class="control-label control-label col-lg-4 col-sm-4">' . trans('global.download_app') . '</label>
	<div class="col-lg-8 col-sm-8"><div class="checkbox">
	<input data-class="switcher-success" type="checkbox" name="download" value="1"' . $checked . '></div></div></div>';

$team_settings = (isset($settings->team)) ? $settings->team : false;
$checked = ($team_settings) ? ' checked' : '';

echo '<div class="form-group"><input type="hidden" name="team" value="0">
	<label class="control-label control-label col-lg-4 col-sm-4">' . trans('global.team_management') . '</label>
	<div class="col-lg-8 col-sm-8"><div class="checkbox">
	<input data-class="switcher-success" type="checkbox" name="team" value="1"' . $checked . '></div></div></div>';
*/
echo '</div>';
echo '</div>';


echo '<legend>' . trans('admin.pricing') . '</legend>';

echo '<p style="margin:10px 0 ">' . trans('admin.pricing_info') . '</p><br style="clear:both">';


echo '<div class="row">';
echo '<div class="col-md-6">';

$monthly_settings = (isset($settings->monthly)) ? $settings->monthly : 0;

echo Former::number()
    ->name('monthly')
    ->forceValue($monthly_settings)
    ->step('any')
	->label(trans('admin.monthly'))
	->append(trans('admin.per_mo'));

echo '</div>';
echo '<div class="col-md-6">';

$currency_settings = (isset($settings->currency)) ? $settings->currency : 'USD';
$currencies = trans('currencies');

foreach($currencies as $abbr => $currency)
{
	$currency_array[$abbr] = $currency[0] . ' (' . $currency[1] . ')';
}

echo Former::select('currency')
	->class('select2-required form-control')
    ->name('currency')
    ->forceValue($currency_settings)
	->options($currency_array)
	->label(trans('admin.currency'));

echo '</div>';
echo '</div>';

echo '<div class="row">';
echo '<div class="col-md-6">';

$annual_settings = (isset($settings->annual)) ? $settings->annual : 0;

echo Former::number()
    ->name('annual')
    ->forceValue($annual_settings)
    ->step('any')
	->label(trans('admin.annual'))
	->append(trans('admin.per_mo'))
	->help(trans('admin.annual_info'));

echo '</div>';
/*
echo '<div class="col-md-6">';

$featured_settings = (isset($settings->featured)) ? $settings->featured : false;
$checked = ($featured_settings) ? ' checked' : '';

echo '<div class="form-group"><input type="hidden" name="featured" value="0">
	<label class="control-label control-label col-lg-2 col-sm-4">' . trans('admin.featured') . '</label>
	<div class="col-lg-10 col-sm-8"><div class="checkbox">
	<input data-class="switcher-success" type="checkbox" name="featured" value="1"' . $checked . '></div></div></div>';

echo '</div>';
*/
echo '</div>';

echo '<br><legend>' . trans('admin.widgets') . '</legend>';

echo '<p style="margin:10px 0 ">' . trans('admin.widgets_info') . '</p><br style="clear:both">';

echo '<div class="row">';

$widgets_settings = (isset($settings->widgets)) ? $settings->widgets : [];
foreach ($widgets as $name => $widget)
{
	$checked = (in_array($widget['dir'], $widgets_settings) || $settings == '') ? ' checked' : '';

	echo '<div class="col-xs-3">';

	echo '<div class="form-group">
		<label class="control-label col-xs-8">' . $name . '</label>
		<div class="col-xs-4"><div class="checkbox">
		<input data-class="switcher-success" type="checkbox" name="widget[]" value="' . $widget['dir'] . '"' . $checked . '></div></div></div>';
/*
	echo Former::checkbox()
		->name('confirmed')
		->value($widget['dir'])
		->label($name)
		->dataClass('switcher-success')
		->check(false);*/
	echo '</div>';
}

echo '</div>';

echo '<hr>';

echo Former::actions()
    ->lg_primary_submit(trans('global.save'))
    ->lg_default_link(trans('global.cancel'), '#/admin/plans');

echo Former::close();
?>
		</div>
	</div>

<script>
function formSubmittedSuccess(result)
{
    if(result == 'error')
    {
        return;
    }
    document.location = '#/admin/plans';
}
</script>
@stop