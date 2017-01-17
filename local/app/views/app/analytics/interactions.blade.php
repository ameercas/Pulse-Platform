@extends('../app.layouts.partial')
@section('content')
<script src="{{ url('/assets/js/leaflet-heat.js') }}"></script>
<ul class="breadcrumb breadcrumb-page">
  <div class="breadcrumb-label text-light-gray">{{ trans('global.you_are_here') }} </div>
  <li><a href="{{ trans('global.home_crumb_url') }}">{{ trans('global.home_crumb_text') }}</a></li>
  <li><a href="#/boards">{{ trans('global.scenario_boards') }}</a></li>
  <li>{{ trans('global.analytics') }}</li>
  <?php
    if($location === false) {
    ?>
  <li class="active">{{ trans('global.interactions') }}</li>
  <?php
    } elseif(isset($location->name)) {
    ?>
  <li><a href="#/interactions">{{ trans('global.interactions') }}</a></li>
  <li class="active">{{ $location->name }}</li>
  <?php
    } elseif(isset($location->name)) {
    	$sl_location = \App\Core\Secure::array2string(array('location_id' => $location->id));
    ?>
  <li><a href="#/interactions">{{ trans('global.interactions') }}</a></li>
  <li><a href="#/interactions/{{ $sl_location }}">{{ $location->name }}</a></li>
  <li class="active">{{ $location->name }}</li>
  <?php
    }
    ?>
</ul>
<?php
  $title = (isset($location->name)) ? $location->name : trans('global.interactions');
  ?>
<div class="page-header">
  <div class="row">
    <h1 class="col-xs-12 col-sm-4 text-center text-left-sm" style="height:32px"><i class="ion-ios-analytics page-header-icon"></i> {{ $title }}</h1>
    <div class="col-xs-12 col-sm-8">
      <div class="row">
        <hr class="visible-xs no-grid-gutter-h">
        <?php
          if(count($location) > 0 && 1==2)
          {
            $filter_title = (isset($location->name)) ? $location->name : trans('global.filter');
          	$filter_title = ($location !== false) ? $location->name : $filter_title;
          ?>
        <div class="pull-right col-xs-12 col-sm-auto">
          <div class="btn-group" style="width:100%;">
            <button class="btn btn-primary btn-labeled dropdown-toggle" style="width:100%;" type="button" data-toggle="dropdown"><span class="btn-label icon fa fa-filter"></span> {{ $filter_title }} &nbsp; <span class="fa fa-caret-down"></span></button>
            <ul class="dropdown-menu dropdown-menu-right" role="menu">
              <li><a href="#/interactions/{{ $date_start }}/{{ $date_end }}/" tabindex="-1">{{ trans('global.remove_filter') }}</a></li>
              <li class="divider"></li>
              <?php
                $location_name_old = '';
                foreach($locations as $location_select)
                {
                	if($location_name_old != $location_select->location_name)
                	{
                		$sl_location = \App\Core\Secure::array2string(array('location_id' => $location_select->location_id));
                		$class = ($location === false && isset($location->id) && $location->id == $location_select->location_id) ? ' active' : '';
                		echo '<li class="nav-header nav-link' . $class . '"><a href="#/interactions/' . $sl_location . '">' . $location_select->location_name . '</a></li>';
                	}
                
                	$location_name_old = $location_select->location_name;
                
                    $sl_location = \App\Core\Secure::array2string(array('location_id' => $location_select->id));
                    $class = (isset($location->id) && $location->id == $location_select->id) ? 'active': '';
                ?>
              <li class="{{ $class }}"><a href="#/interactions/{{ $date_start }}/{{ $date_end }}/{{ $sl_location }}" tabindex="-1">{{ $location_select->name }}</a></li>
              <?php
                }
                ?>
            </ul>
          </div>
        </div>
        <?php
          }
          ?>
        <?php
          if($first_created !== false && $stats_found !== false)
          {
          ?>
        <div class="pull-right col-xs-12 col-sm-auto">
          <div id="stats-range" class="pull-right daterange-selector btn btn-default"> 
            <i class="fa fa-calendar" style="margin:-1px 2px 0 0"></i> <span></span> <b class="caret" style="margin-left:5px"></b> 
          </div>
        </div>
        <?php
          }
          ?>
      </div>
    </div>
  </div>
</div>
<?php
  if($stats_found)
  {
  ?>
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
        <div class="stat-cell col-sm-3 padding-sm-hr bordered no-border-r valign-top">
          <h4 class="padding-sm no-padding-t padding-xs-hr"><i class="fa fa-bar-chart-o text-primary fa-border"></i> {{ trans('global.interactions') }}</h4>
          <ul class="list-group no-margin">
<?php /*
            <li class="list-group-item no-border-hr padding-xs-hr no-bg no-border-radius">
              {{ trans('global.interactions_this_month') }} <span class="label label-primary pull-right">{{ number_format($interactions_this_month->count(),	0, trans('i18n.dec_point'), trans('i18n.thousands_sep')) }}</span>
            </li>
            <li class="list-group-item no-border-hr padding-xs-hr no-bg no-border-radius">
              <span class="interaction-range"></span> <span class="label label-success pull-right">
              {{ number_format($interactions_total,	0, trans('i18n.dec_point'), trans('i18n.thousands_sep')) }}</span>
            </li>
*/ ?>
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
        <div class="stat-cell col-sm-9 bg-primary padding-sm valign-middle">
          <div id="hero-graph" class="graph" style="height: 200px;"></div>
        </div>
      </div>
    </div>
  </div>
</div>
<hr class="no-grid-gutter-h grid-gutter-margin-b no-margin-t">
<div class="row">
  <div class="col-md-3">
    <div class="stat-panel">
      <div class="stat-cell bg-primary valign-middle" style="border-radius:2px">
        <i class="ion-ios-analytics bg-icon"></i>
        <span class="text-xlg"><strong>{{ number_format($interactions_this_month->count(),	0, trans('i18n.dec_point'), trans('i18n.thousands_sep')) }}</strong></span><br>
        <span class="text-bg">{{ trans('global.this_month') }}</span>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-panel">
      <div class="stat-cell bg-success valign-middle" style="border-radius:2px">
        <i class="fa fa-calendar bg-icon" style="color: rgba(0,0,0,.08) !important; font-size:80px"></i>
        <span class="text-xlg"><strong>{{ number_format($interactions_total,0, trans('i18n.dec_point'), trans('i18n.thousands_sep')) }}</strong></span><br>
        <span class="text-bg"><span class="interaction-range"></span></span>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-panel">
      <div class="stat-cell bg-info valign-middle" style="border-radius:2px">
        <i class="fa fa-th bg-icon"></i>
        <span class="text-xlg"><strong>{{ number_format($interactions_app->count(),	0, trans('i18n.dec_point'), trans('i18n.thousands_sep')) }}</strong></span><br>
        <span class="text-bg">{{ trans('global.apps') }}</span><br>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-panel">
      <div class="stat-cell bg-warning valign-middle" style="border-radius:2px">
        <i class="fa fa-laptop bg-icon"></i>
        <span class="text-xlg"><strong>{{ number_format($interactions_site->count(),0, trans('i18n.dec_point'), trans('i18n.thousands_sep')) }}</strong></span><br>
        <span class="text-bg">{{ trans('global.one_pages') }}</span><br>
      </div>
    </div>
  </div>
</div>
<hr class="no-grid-gutter-h grid-gutter-margin-b no-margin-t">
<div class="row">
  <div class="col-md-12">
    <?php 
      $heatmap = '';
      if(count($interactions_heatmap) > 0) { 
      	foreach($interactions_heatmap as $interaction)
      	{
      		$latlng = $interaction->lat . ',' . $interaction->lng;
      		$count = $interaction->total;
      		$heatmap .= '[' . $latlng . ', "' . $count . '"],';
      	}
      	$heatmap = trim($heatmap, ',');
      } 
      ?>
    <script>
      var map = L.map('heat_map');
      
      L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="http://openstreetmap.org" target="_blank">OpenStreetMap</a>',
          maxZoom: 19
      }).addTo(map);
      
      <?php if($heatmap != '') {  ?>
      var heatPoints = [{{ $heatmap }}];
      var heat = L.heatLayer(heatPoints, {
      	maxZoom: 6
      }).addTo(map);
      
      var bounds = new L.LatLngBounds(heatPoints);
      map.fitBounds(bounds);
      <?php } else { ?>
      map.setView([51.505, -0.09], 2);
      <?php } ?>
      if(map.getZoom() > 14)
      {
      	map.setZoom(12);
      }
    </script>
    <div class="stat-panel">
      <div class="stat-row">
        <div>
          <div id="heat_map" style="height:576px; border:1px solid #cfcfcf; margin:0"></div>
        </div>
      </div>
    </div>
  </div>
</div>
<hr class="no-grid-gutter-h grid-gutter-margin-b no-margin-t">
<div class="row">
  <div class="col-md-3">
    <div class="panel">
      <div class="panel-heading">
        <span class="panel-title">{{ trans('global.platform') }}</span>
      </div>
      <div class="panel-body no-padding no-margin">
        <?php
          $data = '';
          foreach($interactions_platform as $interaction)
          {
          	$data .= "{label: \"" . $interaction->platform . "\", value: " . $interaction->total . "},";
          }
          $data = trim($data, ',');
          ?>
        <script>
          Morris.Donut({
          	element: 'segment_platform',
          	resize: true,
          	colors: ['#a4c639', '#8e8e93'],
          	data: [{{ $data }}]
          });
          
          $('#table-platform').dataTable({
          	dom: "t"+
          		  "<'table-footer clearfix'<'DT-label'i><'DT-pagination'p>>",
          	order: [
          		[1, "desc"]
          	],
          	language: {
          		emptyTable: "{{ trans('global.empty_table') }}",
          		info: "{{ trans('global.dt_info') }}",
          		infoEmpty: "",
          		infoFiltered: "(filtered from _MAX_ total entries)",
          		thousands: "{{ trans('i18n.thousands_sep') }}",
          		lengthMenu: "{{ trans('global.show_records') }}",
          		processing: '<i class="fa fa-circle-o-notch fa-spin"></i>',
          		paginate: {
          			first: '<i class="fa fa-fast-backward"></i>',
          			last: '<i class="fa fa-fast-forward"></i>',
          			next: '<i class="fa fa-caret-right"></i>',
          			previous: '<i class="fa fa-caret-left"></i>'
          		}
          	}
          });
        </script>
        <div id="segment_platform" style="margin:10px 20px"></div>
        <div class="table-primary no-margin">
          <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" id="table-platform" style="margin:0">
            <thead>
              <tr>
                <th>{{ trans('global.platform') }}</th>
                <th class="text-right">{{ trans('global.interactions') }} &nbsp;</th>
              </tr>
            </thead>
            <tbody>
              <?php
                foreach($interactions_platform as $row)
                {
                ?>
              <tr>
                <td>{{ $row->platform }}</td>
                <td class="text-right">{{ $row->total }}</td>
              </tr>
              <?php
                }
                ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="panel">
      <div class="panel-heading">
        <span class="panel-title">{{ trans('global.phone_models') }}</span>
      </div>
      <div class="panel-body no-padding no-margin">
        <?php
          $data = '';
          foreach($interactions_model as $interaction)
          {
          	$data .= "{label: \"" . $interaction->model . "\", value: " . $interaction->total . "},";
          }
          $data = trim($data, ',');
          ?>
        <script>
          Morris.Donut({
          	element: 'segment_model',
          	resize: true,
          	colors: CmsAdmin.settings.consts.COLORS,
          	data: [{{ $data }}]
          });
          
          $('#table-model').dataTable({
          	dom: "t"+
          		  "<'table-footer clearfix'<'DT-label'i><'DT-pagination'p>>",
          	order: [
          		[1, "desc"]
          	],
          	language: {
          		emptyTable: "{{ trans('global.empty_table') }}",
          		info: "{{ trans('global.dt_info') }}",
          		infoEmpty: "",
          		infoFiltered: "(filtered from _MAX_ total entries)",
          		thousands: "{{ trans('i18n.thousands_sep') }}",
          		lengthMenu: "{{ trans('global.show_records') }}",
          		processing: '<i class="fa fa-circle-o-notch fa-spin"></i>',
          		paginate: {
          			first: '<i class="fa fa-fast-backward"></i>',
          			last: '<i class="fa fa-fast-forward"></i>',
          			next: '<i class="fa fa-caret-right"></i>',
          			previous: '<i class="fa fa-caret-left"></i>'
          		}
          	}
          });
        </script>
        <div id="segment_model" style="margin:10px 20px"></div>
        <div class="table-primary no-margin">
          <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" id="table-model" style="margin:0">
            <thead>
              <tr>
                <th>{{ trans('global.model') }}</th>
                <th class="text-right">{{ trans('global.interactions') }} &nbsp;</th>
              </tr>
            </thead>
            <tbody>
              <?php
                foreach($interactions_model as $row)
                {
                ?>
              <tr>
                <td>{{ $row->model }}</td>
                <td class="text-right">{{ $row->total }}</td>
              </tr>
              <?php
                }
                ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="panel">
      <div class="panel-heading">
        <span class="panel-title">{{ trans('global.apps') }}</span>
      </div>
      <div class="panel-body no-padding no-margin">
        <?php
          $data = '';
          foreach($interactions_app as $interaction)
          {
          	$data .= "{label: \"" . $interaction->name . "\", value: " . $interaction->total . "},";
          }
          $data = trim($data, ',');
          ?>
        <script>
          Morris.Donut({
          	element: 'segment_app',
          	resize: true,
          	colors: CmsAdmin.settings.consts.COLORS,
          	data: [{{ $data }}]
          });
          
          $('#table-app').dataTable({
          	dom: "t"+
          		  "<'table-footer clearfix'<'DT-label'i><'DT-pagination'p>>",
          	order: [
          		[1, "desc"]
          	],
          	language: {
          		emptyTable: "{{ trans('global.empty_table') }}",
          		info: "{{ trans('global.dt_info') }}",
          		infoEmpty: "",
          		infoFiltered: "(filtered from _MAX_ total entries)",
          		thousands: "{{ trans('i18n.thousands_sep') }}",
          		lengthMenu: "{{ trans('global.show_records') }}",
          		processing: '<i class="fa fa-circle-o-notch fa-spin"></i>',
          		paginate: {
          			first: '<i class="fa fa-fast-backward"></i>',
          			last: '<i class="fa fa-fast-forward"></i>',
          			next: '<i class="fa fa-caret-right"></i>',
          			previous: '<i class="fa fa-caret-left"></i>'
          		}
          	}
          });
        </script>
        <div id="segment_app" style="margin:10px 20px"></div>
        <div class="table-primary no-margin">
          <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" id="table-app" style="margin:0">
            <thead>
              <tr>
                <th>{{ trans('global.app') }}</th>
                <th class="text-right">{{ trans('global.interactions') }} &nbsp;</th>
              </tr>
            </thead>
            <tbody>
              <?php
                foreach($interactions_app as $row)
                {
                ?>
              <tr>
                <td>{{ $row->name }}</td>
                <td class="text-right">{{ $row->total }}</td>
              </tr>
              <?php
                }
                ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="panel">
      <div class="panel-heading">
        <span class="panel-title">{{ trans('global.one_pages') }}</span>
      </div>
      <div class="panel-body no-padding no-margin">
        <?php
          $data = '';
          foreach($interactions_site as $interaction)
          {
          	$data .= "{label: \"" . $interaction->name . "\", value: " . $interaction->total . "},";
          }
          $data = trim($data, ',');
          ?>
        <script>
          Morris.Donut({
          	element: 'segment_site',
          	resize: true,
          	colors: CmsAdmin.settings.consts.COLORS,
          	data: [{{ $data }}]
          });
          
          $('#table-site').dataTable({
          	dom: "t"+
          		  "<'table-footer clearfix'<'DT-label'i><'DT-pagination'p>>",
          	order: [
          		[1, "desc"]
          	],
          	language: {
          		emptyTable: "{{ trans('global.empty_table') }}",
          		info: "{{ trans('global.dt_info') }}",
          		infoEmpty: "",
          		infoFiltered: "(filtered from _MAX_ total entries)",
          		thousands: "{{ trans('i18n.thousands_sep') }}",
          		lengthMenu: "{{ trans('global.show_records') }}",
          		processing: '<i class="fa fa-circle-o-notch fa-spin"></i>',
          		paginate: {
          			first: '<i class="fa fa-fast-backward"></i>',
          			last: '<i class="fa fa-fast-forward"></i>',
          			next: '<i class="fa fa-caret-right"></i>',
          			previous: '<i class="fa fa-caret-left"></i>'
          		}
          	}
          });
        </script>
        <div id="segment_site" style="margin:10px 20px"></div>
        <div class="table-primary no-margin">
          <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" id="table-site" style="margin:0">
            <thead>
              <tr>
                <th>{{ trans('global.one_page') }}</th>
                <th class="text-right">{{ trans('global.interactions') }} &nbsp;</th>
              </tr>
            </thead>
            <tbody>
              <?php
                foreach($interactions_site as $row)
                {
                ?>
              <tr>
                <td>{{ $row->name }}</td>
                <td class="text-right">{{ $row->total }}</td>
              </tr>
              <?php
                }
                ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
  }
  elseif($stats_found === false)
  {
      // No stats found for period
  ?>
<div class="callout pull-left">{{ Lang::get('global.no_interactions_found') }}</div>
<?php
  }
  else
  {
  }
  ?>
<script>
  <?php
    if($first_created !== false)
    {
    ?>
  
  $('#stats-range').daterangepicker({
  	ranges: {
  		 '<?php echo trans('global.today') ?>': [ Date.today(), Date.today() ],
  		 '<?php echo trans('global.yesterday') ?>': [ Date.today().add({ days: -1 }), Date.today().add({ days: -1 }) ],
  		 '<?php echo trans('global.last_7_days') ?>': [ Date.today().add({ days: -6 }), Date.today() ],
  		 '<?php echo trans('global.last_30_days') ?>': [ Date.today().add({ days: -29 }), Date.today() ],
  		 '<?php echo trans('global.this_month') ?>': [ Date.today().moveToFirstDayOfMonth(), Date.today().moveToLastDayOfMonth() ],
  		 '<?php echo trans('global.last_month') ?>': [ Date.today().moveToFirstDayOfMonth().add({ months: -1 }), Date.today().moveToFirstDayOfMonth().add({ days: -1 }) ]
  	},
  	opens: 'left',
  	format: 'MM-DD-YYYY',
  	separator: ' <?php echo trans('global.date_to') ?> ',
  	startDate: Date.parse('<?php echo $date_start ?>').toString('MM-d-yyyy'),
  	endDate: Date.parse('<?php echo $date_end ?>').toString('MM-d-yyyy'),
  	minDate: Date.parse('<?php echo $first_created ?>').toString('MM-d-yyyy'),
  	maxDate: '<?php echo date('m/d/Y') ?>',
  	locale: {
  		applyLabel: '<?php echo trans('global.submit') ?>',
  		cancelLabel: '<?php echo trans('global.reset') ?>',
  		fromLabel: '<?php echo trans('global.date_from') ?>',
  		toLabel: '<?php echo trans('global.date_to') ?>',
  		customRangeLabel: '<?php echo trans('global.custom_range') ?>',
  		daysOfWeek: ['<?php echo trans('global.su') ?>', '<?php echo trans('global.mo') ?>', '<?php echo trans('global.tu') ?>', '<?php echo trans('global.we') ?>', '<?php echo trans('global.th') ?>', '<?php echo trans('global.fr') ?>','<?php echo trans('global.sa') ?>'],
  		monthNames: monthNames,
  		firstDay: 1
  	},
  	showWeekNumbers: true,
  	buttonClasses: ['btn']
  });
  
  $('#stats-range').on('apply.daterangepicker', function(ev, picker) {
      var start = picker.startDate.format('YYYY-MM-DD');
      var end = picker.endDate.format('YYYY-MM-DD');
      document.location = '#/interactions/' + start + '/' + end + '/{{ $sl }}';
  });
  
  /* Set the initial state of the picker label */
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
  <?php
    }
    ?>
</script>
@stop