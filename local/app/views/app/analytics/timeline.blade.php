@extends('../app.layouts.partial')
@section('content')
<ul class="breadcrumb breadcrumb-page">
  <div class="breadcrumb-label text-light-gray">{{ trans('global.you_are_here') }} </div>
  <li><a href="{{ trans('global.home_crumb_url') }}">{{ trans('global.home_crumb_text') }}</a></li>
  <li><a href="#/boards">{{ trans('global.scenario_boards') }}</a></li>
  <li>{{ trans('global.analytics') }}</li>
  <li class="active">{{ trans('global.timeline') }}</li>
</ul>
<?php
  $title = trans('global.timeline');
  ?>
<div class="page-header">
  <div class="row">
    <h1 class="col-xs-12 col-sm-4 text-center text-left-sm" style="height:32px"><i class="ion-ios-timer-outline page-header-icon"></i> {{ $title }}</h1>
    <div class="col-xs-12 col-sm-8">
      <div class="row">
        <hr class="visible-xs no-grid-gutter-h">
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
<div id="visualization"></div>

<script>
blockUI();

/* create a dataset with items */
var items = new vis.DataSet();
var groups = new vis.DataSet();

<?php
$groups = [];
if (count($interactions) > 0)
{
  foreach ($interactions as $interaction)
  {
    if (! in_array($interaction->device_uuid, $groups))
    {
      array_push($groups, $interaction->device_uuid);
  
      $platform = ($interaction->platform == 'iOS') ? 'ion-social-apple' : 'ion-social-android';
      $platform_color = ($interaction->platform == 'iOS') ? '8e8e93' : 'a4c639';
      $platform = '<i class="' . $platform . '" style="font-size:34px; color: #' . $platform_color . '; position: relative; top:-5px"></i>';
?>
    groups.add({id: '{{ $interaction->device_uuid }}', content: '{{ $platform }} <svg title="{{ $interaction->device_uuid }}" data-toggle="tooltip" width="34" height="34" data-jdenticon-hash="{{ md5($interaction->device_uuid) }}" style="height:32px;"></svg>'});
<?php
    }
?>

    items.add({
      id: <?php echo $interaction->id ?>,
      group: '<?php echo $interaction->device_uuid ?>',
      content: '{{ trans('global.' . $interaction->state) }} {{ $interaction->beacon }}',
      start: '<?php echo $interaction->created_at ?>',
      type: 'box'
    });
<?php
  }
}

$groups = array_unique($groups);
?>
var container = document.getElementById('visualization');
var options = {
  groupOrder: 'content'  /* groupOrder can be a property name or a sorting function */,
  zoomMax: 1000*60*60*24*30,
  zoomMin: 1000,
  stack: false,
  start: '<?php echo (count($interactions) > 0) ? $interactions{0}->created_at->format('Y-m-d') : $date_start; ?>',
  orientation: 'both'
};

var timeline = new vis.Timeline(container);
timeline.setOptions(options);
timeline.setGroups(groups);
timeline.setItems(items);
jdenticon();

timeline.on('rangechanged', function () {
  unblockUI();
});

<?php
if (count($interactions) == 0)
{
?>
unblockUI();
<?php
}
?>
</script>

<?php
  }
  elseif($stats_found === false)
  {
      // No stats found for period
  ?>
<div class="callout pull-left">{{ Lang::get('global.no_analytics_found') }}</div>
<?php
  }
  ?>
<script>
  var monthNames = ['<?php echo trans('global.january') ?>', '<?php echo trans('global.february') ?>', '<?php echo trans('global.march') ?>', '<?php echo trans('global.april') ?>', '<?php echo trans('global.may') ?>', '<?php echo trans('global.june') ?>', '<?php echo trans('global.july') ?>', '<?php echo trans('global.august') ?>', '<?php echo trans('global.september') ?>', '<?php echo trans('global.october') ?>', '<?php echo trans('global.november') ?>', '<?php echo trans('global.december') ?>'];
      var monthNamesAbbr = ['<?php echo trans('global.january_abbr') ?>', '<?php echo trans('global.february_abbr') ?>', '<?php echo trans('global.march_abbr') ?>', '<?php echo trans('global.april_abbr') ?>', '<?php echo trans('global.may_abbr') ?>', '<?php echo trans('global.june_abbr') ?>', '<?php echo trans('global.july_abbr') ?>', '<?php echo trans('global.august_abbr') ?>', '<?php echo trans('global.september_abbr') ?>', '<?php echo trans('global.october_abbr') ?>', '<?php echo trans('global.november_abbr') ?>', '<?php echo trans('global.december_abbr') ?>'];

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
      document.location = '#/timeline/' + start + '/' + end + '/{{ $sl }}';
  });
  
  /* Set the initial state of the picker label */
  var _d_start = Date.parse('<?php echo $date_start ?>');
  var _d_end = Date.parse('<?php echo $date_end ?>');
  
  var d_start = monthNames[_d_start.getMonth()] + ' ' + _d_start.toString('d, yyyy');
  var d_end = monthNames[_d_end.getMonth()] + ' ' + _d_end.toString('d, yyyy');
  
  var d_string = (d_start == d_end) ? d_start : d_start + ' - ' + d_end;
  
  $('#stats-range span').html(d_string);
  <?php
    }
    ?>
</script>
@stop