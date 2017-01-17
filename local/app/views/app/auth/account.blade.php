@extends('../app.layouts.partial')

@section('content')
	<ul class="breadcrumb breadcrumb-page">
		<div class="breadcrumb-label text-light-gray">{{ trans('global.you_are_here') }} </div>
		<li><a href="{{ trans('global.home_crumb_url') }}">{{ trans('global.home_crumb_text') }}</a></li>
		<li>{{ trans('global.settings') }}</li>
		<li class="active">{{ trans('global.account') }}</li>
	</ul>

	<div class="page-header">
		<div class="row">
			<h1 class="col-xs-12 text-center text-left-sm"><i class="fa fa-credit-card-alt page-header-icon" style="height:28px"></i> {{ trans('admin.your_current_plan', ['plan' => '<strong>' . Auth::user()->plan->name . '</strong>']) }}<?php if (\Config::get('avangate.active') && Auth::user()->remote_id != '') { ?> {{ trans('admin.manage_payment_account', ['url' => 'https://secure.avangate.com/myaccount/']) }}<?php } ?></h1>

		</div>
	</div>

<?php
if(\Config::get('avangate.active', false) || \Config::get('payment-gateways.active', false)) {

if (\Config::get('payment-gateways.active', false)) {
?>
	<ul class="nav nav-tabs">
		<li ng-class="{active: selectedTab == 'subscription'}">
			<a href="javascript:void(0);" ng-click="selectedTab = 'subscription';"><span class="fa fa-trophy"></span> &nbsp; {{ trans('admin.subscription') }}</a>
		</li>
		<li ng-class="{active: selectedTab == 'invoices'}">
			<a href="javascript:void(0);" ng-click="selectedTab = 'invoices';"><span class="fa fa-file-text-o"></span> &nbsp; {{ trans('admin.invoices') }}</a>
		</li>
	</ul>

	<div class="panel-body no-padding" ng-init="selectedTab = 'subscription';">
		<div class="tab-content">
			<div class="tab-pane fade" ng-class="{'in active': selectedTab == 'subscription'}">
<?php
}
?>

<style type="text/css">
.plan-align-left {
	text-align:left;
	padding-left:20px !important;
}
ul.plan-features.active li {
  background-color:#f5f9f2 !important;
}
.plan-item-value {
  float: right;
  font-weight: bold;
  margin-right:10px;
}
.plans-panel, .table {
  margin-bottom: 0 !important;
}
.tab-content {
  padding: 0 !important;
}
</style>
				<div class="plans-panel">
					<div class="plans-container">
<?php
$i=1;
foreach($plans as $plan)
{
	$sl = \App\Core\Secure::array2string(array('plan_id' => $plan->id));

	$settings = $plan->settings;
	if ($settings != '') $settings = json_decode($settings);

	if ($plan->id == Auth::user()->plan_id)
  {
    $settings_current = $plan->settings;
	  if ($settings_current != '') $settings_current = json_decode($settings_current);
  }

	$plan_widgets = (isset($settings->widgets)) ? $settings->widgets : array();
	$plan_widgets_count = (isset($settings->widgets)) ? count($settings->widgets) : 0;
	if ($settings == '' || count($plan_widgets) == count($widgets)) $plan_widgets_count = trans('admin.all');

	$support = (isset($settings->support)) ? $settings->support : '-';
	$domain = (isset($settings->domain)) ? (boolean) $settings->domain : true;
	$domain_icon = ($domain) ? '<i class="fa fa-check icon-active"></i>' : '<i class="fa fa-times icon-nonactive"></i>';
	$download = (isset($settings->download)) ? (boolean) $settings->download : true;
	$download_icon = ($download) ? '<i class="fa fa-check icon-active"></i>' : '<i class="fa fa-times icon-nonactive"></i>';
	$monthly = (isset($settings->monthly)) ? $settings->monthly : 0;
	$annual = (isset($settings->annual)) ? 12 * $settings->annual : 0;
	$currency = (isset($settings->currency)) ? $settings->currency : 'USD';
	$currencies = trans('currencies');
	$currency_symbol = $currencies[$currency][1];

	$apps = (isset($settings->max_apps)) ? $settings->max_apps : 0;
	if ($apps == 0) $apps = trans('admin.unlimited');

  $interactions = (isset($settings->interactions)) ? $settings->interactions : 100;
  $disk_space = (isset($settings->disk_space)) ? $settings->disk_space : 1;
  $max_sites_settings = (isset($settings->max_sites)) ? $settings->max_sites : 0;
  $max_boards_settings = (isset($settings->max_boards)) ? $settings->max_boards : 1;
  $max_scenarios_settings = (isset($settings->max_scenarios)) ? $settings->max_scenarios : 3;
  $max_beacons_settings = (isset($settings->max_beacons)) ? $settings->max_beacons : 1;
  $max_geofences_settings = (isset($settings->max_geofences)) ? $settings->max_geofences : 1;

  $order_url = (isset($settings->order_url)) ? $settings->order_url . '&CUSTOMERID=' . Auth::user()->id : '';

  if ($reseller->settings->AVGAFFILIATE != '') $order_url .= '&AVGAFFILIATE=' . $reseller->settings->AVGAFFILIATE;

  if (isset($settings_current))
  {
    $upgrade_url = (isset($settings_current->upgrade_url)) ? $settings_current->upgrade_url . '&CUSTOMERID=' . Auth::user()->id : '';
  }
  else
  {
    $upgrade_url = '';
  }

  if ($reseller->settings->AVGAFFILIATE != '') $upgrade_url .= '&AVGAFFILIATE=' . $reseller->settings->AVGAFFILIATE;

?>
				<div class="plan-col col-sm-3">
					<div class="plan-header <?php echo ($plan->id == Auth::user()->plan->id) ? 'bg-light-green': 'bg-primary'; ?> <?php echo ($i%2) ? 'darken': 'darker'; ?>">{{ $plan->name }}</div>
					<div class="plan-pricing <?php echo ($plan->id == Auth::user()->plan->id) ? 'bg-light-green': 'bg-primary'; ?><?php echo ($i%2) ? '': ' darken'; ?>"><span class="plan-currency">{{ $currency_symbol }}</span><span class="plan-value">{{ str_replace('.00', '', number_format($monthly, 2)) }}</span><span class="plan-period">{{ trans('admin.per_mo') }}</span></div>
					<ul class="plan-features<?php if ($plan->id == Auth::user()->plan->id) echo ' active'; ?>">
						<li class="plan-align-left">{{ trans('global.interactions') }}: <span class="plan-item-value">{{ number_format($interactions) }}</span></li>
						<li class="plan-align-left">{{ trans('global.scenario_boards') }}: <span class="plan-item-value">{{ $max_boards_settings }}</span></li>
						<li class="plan-align-left">{{ trans('global.scenarios') }}: <span class="plan-item-value">{{ $max_scenarios_settings }}</span></li>
						<li class="plan-align-left">{{ trans('global.beacons') }}: <span class="plan-item-value">{{ $max_beacons_settings }}</span></li>
						<li class="plan-align-left">{{ trans('global.geofences') }}: <span class="plan-item-value">{{ $max_geofences_settings }}</span></li>
						<li class="plan-align-left">{{ trans('admin.apps') }}: <span class="plan-item-value">{{ $apps }}</span></li>
						<li class="plan-align-left">{{ trans('global.one_pages') }}: <span class="plan-item-value">{{ $max_sites_settings }}</span></li>
						<li class="plan-align-left">{{ trans('global.custom_domain') }} <a href="javascript:void(0);" data-toggle="tooltip" title="{{ trans('global.custom_domain_info') }}"><i class="icon ion-help-circled"></i></a>  <span class="plan-item-value">{{ $domain_icon }}</span></li>
<?php /*
						<li class="plan-align-left">{{ trans('global.download_app') }} <a href="javascript:void(0);" data-toggle="tooltip" title="{{ trans('global.download_app_info') }}"><i class="icon ion-help-circled"></i></a> <span class="plan-item-value">{{ $download_icon }}</span></li>
            */ ?>
						<li class="plan-align-left">{{ trans('admin.widgets') }}: {{ $plan_widgets_count }} [<a href="javascript:void(0);" onclick="$('.widget-details').toggle()">{{ trans('global.show') }}</a>]</li>
<?php
foreach ($widgets as $widget_name => $widget)
{
	if ($widget['active'])
	{
		$included = (in_array($widget['dir'], $plan_widgets)) ? '<i class="fa fa-check icon-active"></i>' : '<i class="fa fa-times icon-nonactive"></i>';
		if($settings == '') $included = '<i class="fa fa-check icon-active"></i>';
		echo '<li class="widget-details plan-align-left" style="display:none">' . $widget_name . ' <span class="plan-item-value">' . $included . '</span></li>';
	}
}

if (\Config::get('payment-gateways.active', false)) {

  if (! isset($settings->order_url) || $settings->order_url == '') {
    if ((int) $monthly > 0 && ((! $expired && Auth::user()->plan->sort < $plan->sort) || $expired)) {
?>
<a href="#/order-subscription/{{ $sl }}" class="<?php echo ($plan->id == Auth::user()->plan->id) ? 'bg-light-green': 'bg-primary'; ?> <?php echo ($i%2) ? 'darken': 'darker'; ?>">{{ trans('admin.upgrade') }} <i class="fa fa-arrow-right"></i></a>
<?php 
    } 
  } else {
    if ((int) $monthly > 0 && ((! $expired && Auth::user()->plan->sort < $plan->sort) || $expired)) {
?>
<a href="{{ $settings->order_url }}" class="payment <?php echo ($plan->id == Auth::user()->plan->id) ? 'bg-light-green': 'bg-primary'; ?> <?php echo ($i%2) ? 'darken': 'darker'; ?>">{{ trans('admin.upgrade') }} <i class="fa fa-arrow-right"></i></a>
<?php 
    } 
  }

  if (! isset($settings->upgrade_url) || $settings->upgrade_url == '') {
    if ((int) $monthly > 0 && ((! $expired && Auth::user()->plan->sort == $plan->sort) || $expired)) {
?>
<a href="#/order-subscription/{{ $sl }}" class="<?php echo ($plan->id == Auth::user()->plan->id) ? 'bg-light-green': 'bg-primary'; ?> <?php echo ($i%2) ? 'darken': 'darker'; ?>">{{ trans('admin.extend_subscription') }} <i class="fa fa-arrow-right"></i></a>
<?php 
    } 
  } else {
    if ((int) $monthly > 0 && ((! $expired && Auth::user()->plan->sort == $plan->sort) || $expired)) {
?>
<a href="{{ $settings->upgrade_url }}" class="payment <?php echo ($plan->id == Auth::user()->plan->id) ? 'bg-light-green': 'bg-primary'; ?> <?php echo ($i%2) ? 'darken': 'darker'; ?>">{{ trans('admin.extend_subscription') }} <i class="fa fa-arrow-right"></i></a>
<?php 
    } 
  }

} else {

  if (Auth::user()->plan->sort <= $plan->sort) { 
    if (Auth::user()->plan->sort == $plan->sort)
    {
      if (Auth::user()->remote_id == '')
      {
        // No paid client yet
      }
    }
    else
    {
      // Upgrade or Order
      if (Auth::user()->remote_id == '')
      {
        // Order
  ?>
  <a href="javascript:void(0);" pay-href="{{ $order_url }}" class="payment <?php echo ($plan->id == Auth::user()->plan->id) ? 'bg-light-green': 'bg-primary'; ?> <?php echo ($i%2) ? 'darken': 'darker'; ?>" target="_blank">{{ trans('admin.upgrade') }} <i class="fa fa-arrow-right"></i></a>
  <?php
      }
      else
      {
        // Upgrade
  ?>
  <a href="javascript:void(0);" pay-href="{{ $upgrade_url }}" class="payment <?php echo ($plan->id == Auth::user()->plan->id) ? 'bg-light-green': 'bg-primary'; ?> <?php echo ($i%2) ? 'darken': 'darker'; ?>" target="_blank">{{ trans('admin.upgrade') }} <i class="fa fa-arrow-right"></i></a>
  <?php
      }
    }
  }
}
?>
					</ul>
				</div>
<?php
	$i++;
}
?>
					</div>
				</div>

<?php if (\Config::get('payment-gateways.active', false)) { ?>
			</div>

			<div class="tab-pane fade" ng-class="{'in active': selectedTab == 'invoices'}">
<?php
if (count($orders) == 0)
{
	echo '<p class="lead" style="margin:20px">' . trans('admin.no_invoices') . '</p>';
}
else
{
?>
					<table class="table table-striped">
						<thead>
							<tr>
								<th>{{ trans('admin.order') }}</th>
								<th>{{ trans('admin.payment_method') }}</th>
								<th>{{ trans('admin.date') }}</th>
								<th class="text-right">{{ trans('admin.total') }}</th>
							</tr>
						</thead>
						<tbody>
<?php
foreach ($orders as $order)
{
	$sl_invoice = \App\Core\Secure::array2string(array('invoice_id' => $order->id));
?>
							<tr>
								<td><a href="javascript:void(0);" data-modal="{{ url('/app/modal/account/invoice?sl=' . $sl_invoice) }}" style="text-decoration:underline">{{ trans('admin.order_line', array('plan' => $order->plan_name, 'date' => $order->expires)) }} - {{ trans('admin.invoice') }} #{{ $order->invoice }}</a></td>
								<td>{{ trans('admin.' . $order->payment_method) }}</td>
								<td>{{ $order->invoice_date }}</td>
								<td class="text-right">{{ $order->cost_str }}</td>
							</tr>
<?php
	$i++;
}
?>
						</tbody>
					</table>
<?php
}
?>
				</div>
<?php
}
?>

<script>
$('.payment').on('click', function() {
  var href = $(this).attr('pay-href');

  swal({
    title: "{{ trans('admin.upgrade') }}",
    text: "{{ trans('admin.upgrade_before_link') }}",
    type: "success",
    showCancelButton: false,
    confirmButtonClass: "btn-success",
    confirmButtonText: "{{ trans('global.got_it') }}",
    cancelButtonText: "{{ trans('global.cancel') }}",
    closeOnConfirm: true,
    closeOnCancel: true
  },
  function(isConfirm)
  {
    if(isConfirm)
    {
      window.open(href, '_payment');
    }
  });
});
</script>
<?php } else { ?>

<p class="lead">
{{ trans('admin.contact_us_for_account') }}
</p>

<?php } ?>
@stop