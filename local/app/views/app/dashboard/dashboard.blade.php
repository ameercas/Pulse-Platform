@extends('../app.layouts.partial')

@section('content')
<ul class="breadcrumb breadcrumb-page">
  <div class="breadcrumb-label text-light-gray">{{ trans('global.you_are_here') }} </div>
  <li><a href="{{ trans('global.home_crumb_url') }}">{{ trans('global.home_crumb_text') }}</a></li>
  <li class="active">{{ trans('global.intro') }}</li>
</ul>
<div class="page-header">
  <div class="row">
    <h1 class="col-xs-12 col-sm-4 text-center text-left-sm" style="height:32px"><i class="ion-speedometer page-header-icon"></i> {{ trans('global.welcome_user', ['name' => $username]) }}</h1>
  </div>
</div>
<div class="row" style="margin-top: 20px;">
  <div class="col-md-4 col-md-push-8">
    <?php
      if(\Auth::user()->can('user_management'))
      {
        if(\Auth::user()->parent_id == NULL)
        {
      ?>
						<div class="stat-panel">
							<div class="stat-cell bg-info valign-middle">
								<i class="fa fa-rocket bg-icon"></i>
								<span class="text-xlg"><strong>{{ $plan->name }}</strong></span><br>
								<span class="text-bg">{{ trans('admin.your_current_plan', ['plan' => '<strong>' . $plan->name . '</strong>']) }}</span><br>
								<span class="text-sm"><a href="#/account">&rsaquo; {{ trans('admin.manage_subscription') }}</a></span>
							</div>
						</div>
    <?php
      }
      }
      ?>
    <accordion close-others="false" id="faq">
      <?php if ($reseller->app_name != '') { ?>
      <accordion-group ng-init="download_app = true" is-open="download_app">
        <accordion-heading>
          <i class="icon fa fa-download"></i> &nbsp; {{ trans('global.download') }} {{ $reseller->app_name }}
        </accordion-heading>
        <div class="row">
          <?php if ($reseller->app_link_ios != '') { ?>
          <div class="col-lg-6 text-center">
            <a href="{{ $reseller->app_link_ios }}" target="_blank">
            <img src="{{ url('assets/images/app-stores/Download_on_the_App_Store_Badge_US-UK_135x40.svg') }}" style="width:100%; margin:5px 0; max-height:64px">
            </a>
          </div>
          <?php } ?>
          <?php if ($reseller->app_link_android != '') { ?>
          <div class="col-lg-6 text-center">
            <a href="{{ $reseller->app_link_android }}" target="_blank">
            <img src="{{ url('assets/images/app-stores/Get_it_on_Google_play.svg') }}" style="width:100%; margin:5px 0; max-height:64px">
            </a>
          </div>
          <?php } ?>
        </div>
      </accordion-group>
      <?php } ?>

      <accordion-group ng-init="page_content = <?php echo ($reseller->app_name == '') ? 'true': 'false'; ?>" is-open="page_content">
        <accordion-heading>
          <i class="fa fa-info-circle"></i> &nbsp; Where do I start?
        </accordion-heading>
        <p class="lead">You can start testing scenarios with a Bluetooth enabled Android or iOS device.</p>
        <p class="lead">After adding one or more <a href="#/geofences">regions</a> or <a href="#/beacons">beacons</a>, you can manage scenarios with a <a href="#/boards">scenario board</a>.</p>
        <p class="lead">Finally, create an <a href="#/apps">app</a> or <a href="#/web">landing page</a> to attach to your scenario board and scan its QR code with the {{ $reseller->app_name }} app.</p>
      </accordion-group>
      <accordion-group>
        <accordion-heading>
          <i class="fa fa-dot-circle-o"></i> &nbsp; What is a beacon?
        </accordion-heading>
        <p class="lead">Beacons transmit small amounts of data via Bluetooth Low Energy (BLE) up to 80 meters, and as a result are often used for indoor location technology, although beacons can be used outside as well.</p>
        <small><i><a href="http://www.webopedia.com/TERM/B/beacon.html" target="_blank">Source</a></i></small>
      </accordion-group>
      <accordion-group>
        <accordion-heading>
          <i class="fa fa-map-marker"></i> &nbsp; What is a region?
        </accordion-heading>
        <p class="lead">Regions are also known as geofences.</p>
        <p class="lead">Geo-fence apps and tools monitor when mobile devices or other physical objects enter or exit an established geo-fenced area and provide administrators with alerts when there’s a change in status for a device.</p>
        <small><i><a href="http://www.webopedia.com/TERM/G/geo-fence.html" target="_blank">Source</a></i></small>
      </accordion-group>

    </accordion>
  </div>
  <div class="col-md-8 col-md-pull-4">
    <div class="row">
      <div class="col-md-12">
        <?php
          $interactions_range = '';
          $interactions_total = 0;
          
          foreach($interactions_by_day as $date => $interactions) {
              $oDate = new DateTime($date);
              $date = $oDate->format('Y-m-d');
          
                 $interactions_range .= "{ day: '" . $date . "', interactions: " . $interactions . " }";	
                 $interactions_total += $interactions;
          
              if ($oDate->format('Y-m-d') != $date_end) $interactions_range .= ',';
          }
          ?>
        <script>
          var monthNames = ['<?php echo trans('global.january') ?>', '<?php echo trans('global.february') ?>', '<?php echo trans('global.march') ?>', '<?php echo trans('global.april') ?>', '<?php echo trans('global.may') ?>', '<?php echo trans('global.june') ?>', '<?php echo trans('global.july') ?>', '<?php echo trans('global.august') ?>', '<?php echo trans('global.september') ?>', '<?php echo trans('global.october') ?>', '<?php echo trans('global.november') ?>', '<?php echo trans('global.december') ?>'];
          var monthNamesAbbr = ['<?php echo trans('global.january_abbr') ?>', '<?php echo trans('global.february_abbr') ?>', '<?php echo trans('global.march_abbr') ?>', '<?php echo trans('global.april_abbr') ?>', '<?php echo trans('global.may_abbr') ?>', '<?php echo trans('global.june_abbr') ?>', '<?php echo trans('global.july_abbr') ?>', '<?php echo trans('global.august_abbr') ?>', '<?php echo trans('global.september_abbr') ?>', '<?php echo trans('global.october_abbr') ?>', '<?php echo trans('global.november_abbr') ?>', '<?php echo trans('global.december_abbr') ?>'];
          
          var stats_data = [
          <?php echo $interactions_range ?>
          ];
          Morris.Line({
              element: 'hero-graph',
              data: stats_data,
              xkey: 'day',
          	yLabelFormat: function(y){return y != Math.round(y)?'':y;},
              ykeys: ['interactions'],
              labels: ['{{ trans('global.interactions') }}'],
              lineColors: ['#fff'],
              lineWidth: 2,
              pointSize: 4,
              gridLineColor: 'rgba(255,255,255,.5)',
              resize: true,
              gridTextColor: '#fff',
              gridIntegers: true,
              xLabels: "day",
              xLabelFormat: function(d) {
                  return monthNamesAbbr[d.getMonth()] + ' ' + d.getDate(); 
              },
          });
        </script>
        <div class="stat-panel">
          <div class="stat-row">
            <div class="stat-cell col-sm-4 padding-sm-hr bordered no-border-r valign-top">
              <h4 class="padding-sm no-padding-t padding-xs-hr" style="white-space:normal"><i class="fa fa-bar-chart-o text-primary fa-border"></i> {{ trans('global.interactions') }} <span class="interaction-range"></span></h4>
              <ul class="list-group no-margin">
                <li class="list-group-item no-border-hr padding-xs-hr no-bg no-border-radius">
                  {{ trans('global.scenarios') }} <span class="label label-success pull-right">
                  {{ number_format($interactions_place->count(),	0, trans('i18n.dec_point'), trans('i18n.thousands_sep')) }}</span>
                </li>
                <li class="list-group-item no-border-hr padding-xs-hr no-bg no-border-radius">
                  {{ trans('global.app_usage') }} <span class="label label-warning pull-right">
                  {{ number_format($interactions_total - $interactions_place->count(),	0, trans('i18n.dec_point'), trans('i18n.thousands_sep')) }}</span>
                </li>
                <li class="list-group-item no-border-hr padding-xs-hr no-bg no-border-radius">
                  {{ trans('global.total') }} <span class="label label-primary pull-right">
                  {{ number_format($interactions_total,	0, trans('i18n.dec_point'), trans('i18n.thousands_sep')) }}</span>
                </li>
              </ul>
            </div>
            <div class="stat-cell col-sm-8 bg-primary padding-sm valign-middle">
              <div id="hero-graph" class="graph" style="height: 200px;"></div>
            </div>
          </div>
        </div>
      </div>
      <script>
        var easyPieChartDefaults = {
          animate: 2000,
          scaleColor: false,
          lineWidth: 6,
          lineCap: 'square',
          size: 90,
          trackColor: '#e5e5e5'
        }
        $('.easy-pie-chart').easyPieChart($.extend({}, easyPieChartDefaults, {
          barColor: CmsAdmin.settings.consts.COLORS[1]
        }));
      </script>
      <style type="text/css">
        .stat-row {
        white-space:nowrap;
        }
      </style>
      <div class="col-md-4">
        <?php
          $interactions = $interactions_this_month->count();
          $fontSize = 17;
          
          if ($interactions >= 100000) $fontSize = 16;
          if ($interactions >= 1000000) $fontSize = 15;
          ?>
        <div class="stat-panel text-center">
          <div class="stat-row">
            <div class="stat-cell bg-dark-gray padding-sm text-xs text-semibold">
              <i class="ion-ios-analytics"></i>&nbsp;&nbsp;{{ trans('global.interactions') }} <span class="current_month"></span>
            </div>
          </div>
          <div class="stat-row">
            <div class="stat-cell bordered no-border-t no-padding-hr">
              <div class="pie-chart easy-pie-chart" data-percent="{{ ($interactions / $plan->settings->interactions) * 100 }}">
                <div class="pie-chart-label" style="font-size:{{ $fontSize }}px">{{ number_format($interactions, 0, trans('i18n.dec_point'), trans('i18n.thousands_sep')) }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-panel text-center">
          <div class="stat-row">
            <div class="stat-cell bg-dark-gray padding-sm text-xs text-semibold">
              <i class="fa fa-th"></i>&nbsp;&nbsp;{{ trans('global.apps') }}
            </div>
          </div>
          <div class="stat-row">
            <div class="stat-cell bordered no-border-t no-padding-hr">
              <div class="pie-chart easy-pie-chart" data-percent="{{ ($count_apps / $plan->settings->max_apps) * 100 }}">
                <div class="pie-chart-label">{{ number_format($count_apps,	0, trans('i18n.dec_point'), trans('i18n.thousands_sep')) }} / {{ $plan->settings->max_apps }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-panel text-center">
          <div class="stat-row">
            <div class="stat-cell bg-dark-gray padding-sm text-xs text-semibold">
              <i class="fa fa-laptop"></i>&nbsp;&nbsp;{{ trans('global.one_pages') }}
            </div>
          </div>
          <div class="stat-row">
            <div class="stat-cell bordered no-border-t no-padding-hr">
              <div class="pie-chart easy-pie-chart" data-percent="{{ ($count_sites / $plan->settings->max_sites) * 100 }}">
                <div class="pie-chart-label">{{ number_format($count_sites,	0, trans('i18n.dec_point'), trans('i18n.thousands_sep')) }} / {{ $plan->settings->max_sites }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-panel text-center">
          <div class="stat-row">
            <div class="stat-cell bg-dark-gray padding-sm text-xs text-semibold">
              <i class="ion-android-notifications"></i>&nbsp;&nbsp;{{ trans('global.scenario_boards') }}
            </div>
          </div>
          <div class="stat-row">
            <div class="stat-cell bordered no-border-t no-padding-hr">
              <div class="pie-chart easy-pie-chart" data-percent="{{ ($count_boards / $plan->settings->max_boards) * 100 }}">
                <div class="pie-chart-label">{{ number_format($count_boards,	0, trans('i18n.dec_point'), trans('i18n.thousands_sep')) }} / {{ $plan->settings->max_boards }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-panel text-center">
          <div class="stat-row">
            <div class="stat-cell bg-dark-gray padding-sm text-xs text-semibold">
              <i class="fa fa-dot-circle-o"></i>&nbsp;&nbsp;{{ trans('global.beacons') }}
            </div>
          </div>
          <div class="stat-row">
            <div class="stat-cell bordered no-border-t no-padding-hr">
              <div class="pie-chart easy-pie-chart" data-percent="{{ ($count_beacons / $plan->settings->max_beacons) * 100 }}">
                <div class="pie-chart-label">{{ number_format($count_beacons,	0, trans('i18n.dec_point'), trans('i18n.thousands_sep')) }} / {{ $plan->settings->max_beacons }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-panel text-center">
          <div class="stat-row">
            <div class="stat-cell bg-dark-gray padding-sm text-xs text-semibold">
              <i class="fa fa-map-marker"></i>&nbsp;&nbsp;{{ trans('global.geofences') }}
            </div>
          </div>
          <div class="stat-row">
            <div class="stat-cell bordered no-border-t no-padding-hr">
              <div class="pie-chart easy-pie-chart" data-percent="{{ ($count_geofences / $plan->settings->max_geofences) * 100 }}">
                <div class="pie-chart-label">{{ number_format($count_geofences,	0, trans('i18n.dec_point'), trans('i18n.thousands_sep')) }} / {{ $plan->settings->max_geofences }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  var _d_start = Date.parse('<?php echo $date_start ?>');
  var _d_end = Date.parse('<?php echo $date_end ?>');

  var d_start = monthNames[_d_start.getMonth()] + ' ' + _d_start.toString('d, yyyy');
  var d_end = monthNames[_d_end.getMonth()] + ' ' + _d_end.toString('d, yyyy');
  
  var d_string = (d_start == d_end) ? d_start : d_start + ' - ' + d_end;
  
  $('#stats-range span').html(d_string);
  
   var d_start_short_y = (_d_start.getYear() != _d_end.getYear()) ? 'd, yy' : 'd ';
   var d_end_short_y = (_d_start.getYear() != _d_end.getYear()) ? 'd, yy' : 'd ';
  
  var d_start_short = monthNamesAbbr[_d_start.getMonth()] + ' ' + _d_start.toString(d_start_short_y);
  var d_end_short = monthNamesAbbr[_d_end.getMonth()] + ' ' + _d_end.toString(d_end_short_y);
  
  var d_string_short = (d_start_short == d_end_short) ? d_start_short : d_start_short + ' - ' + d_end_short;

  $('.interaction-range').html(d_string_short);

  var current_month = monthNames[{{ (date('m') - 1) }}];
  $('.current_month').html(current_month);
</script>
@stop