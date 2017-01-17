@extends('../app.layouts.partial')

@section('content')
<ul class="breadcrumb breadcrumb-page">
  <div class="breadcrumb-label text-light-gray">{{ trans('global.you_are_here') }} </div>
  <li><a href="{{ trans('global.home_crumb_url') }}">{{ trans('global.home_crumb_text') }}</a></li>
  <li><a href="#/boards">{{ trans('global.scenario_boards') }}</a></li>
  <li class="active">{{ trans('global.scenarios') }}</li>
</ul>

<div class="page-header">
  <div class="row">
    <h1 class="col-xs-12 col-sm-7 text-center text-left-sm" style="height:32px"><i class="ion-android-notifications page-header-icon"></i> <span id="board_name" class="bs-x-text" data-type="text" data-clear="false" data-mode="inline" data-pk="{{ $sl }}">{{ $scenario_board->name }}</span></h1>

    <div class="col-xs-12 col-sm-5">
      <div class="row">
        <hr class="visible-xs no-grid-gutter-h">

        <div class="pull-right col-xs-12 col-sm-auto">
          <div class="btn-group" style="width:100%;" id="tour-options">
            <button class="btn btn-default dropdown-toggle" style="width:100%;" type="button" data-toggle="dropdown" tooltip="{{ trans('global.options') }}"><i class="icon fa fa-bars"></i> &nbsp; <i class="fa fa-caret-down"></i></button>
            <ul class="dropdown-menu dropdown-menu-right" role="menu">
              <li><a href="javascript:void(0);" data-modal="{{ url('/app/modal/beacon/board-settings?sl=' . $sl) }}" tabindex="-1"><i class="fa fa-cogs"></i> &nbsp; {{ trans('global.board_settings') }}</a></li>
<?php
if(\Auth::user()->can('system_management'))
{
?>
              <li><a href="{{ url('/api/v1/remote/handshake?sl=' . $sl) }}" tabindex="-1" target="_blank"><i class="fa fa-code"></i> &nbsp; View JSON</a></li>
<?php
}
?>
              <li class="divider"></li>
              <li><a href="javascript:void(0);" tabindex="-1" id="btnDeleteBoard"><i class="fa fa-trash-o"></i> &nbsp; {{ trans('global.delete_board') }}</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="panel panel-default">
  <div class="panel-heading">
    <span class="panel-title">{{ trans('global.scenarios') }}</span>
  </div>
<?php
$i = 0;
$js = '';

foreach($scenario_board->scenarios as $scenario)
{
  // Attached geofences to id array
  $scenario_geofences = $scenario->geofences()->get(['geofences.id']);
  $geofence_array = array();
  foreach($scenario_geofences as $geofence)
  {
    array_push($geofence_array, (int) $geofence->id);
  }
  $scenario->geofences = $geofence_array;

  // Attached beacons to id array
  $scenario_beacons = $scenario->beacons()->get(['beacons.id']);
  $beacon_array = array();
  foreach($scenario_beacons as $beacon)
  {
    array_push($beacon_array, (int) $beacon->id);
  }
  $scenario->beacons = $beacon_array;

  $sl_scenario = \App\Core\Secure::array2string(array('scenario_board_id' => $scenario_board->id, 'scenario_id' => $scenario->id));

  $json_string = str_replace("'", "\'", str_replace('\\', '\\\\', json_encode($scenario)));
  //dd($json_string);
  $js .= "var data = JSON.parse('" . $json_string . "');
data.sl = '" . $sl_scenario . "';
data.i = '" . $i . "';";

  $js .= "addRepeaterRow('insert', data);";
  $i++;
}
?>
  <table width="100%" border="0" cellspacing="0" cellpadding="10" class="table" id="tbl-scenarios">
    <thead>
      <tr>
        <th>{{ trans('global.if_someone') }}</th>
        <th style="width:50px">&nbsp;</th>
        <th>{{ trans('global.place') }}</th>
        <th>{{ trans('global.then') }}</th>
        <th style="width:50px">&nbsp;</th>
        <th>{{ trans('global.when') }}</th>
        <th style="width:48px">&nbsp;</th>
        <th>&nbsp;</th>
        <th style="width:48px">&nbsp;</th>
        <th style="width:48px">&nbsp;</th>
        <th style="width:47px">&nbsp;</th>
      </tr>
    </thead>
    <tbody>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="11" class="text-center"><button class="btn btn-success btn-lg btn-flat btn-block" id="add_scenario"><i class="fa fa-plus"></i> {{ trans('global.add_scenario') }}</button></td>
      </tr>
    </tfoot>
  </table>
</div>
<style type="text/css">
#tbl-scenarios td {
  padding:5px !important;
}
.select2-primary .form-group {
  margin-bottom:0;
}
.select2-primary label {
  padding-top:6px;
}
.table > tbody > tr > td, .table > tbody > tr > th, .table > tfoot > tr > td, .table > tfoot > tr > th, .table > thead > tr > td, .table > thead > tr > th {
  vertical-align:middle;
}
.time .input-group-addon,
.date-start-end .input-group-addon,
.popover-content .input-group-addon {
  padding:8px 0;
  float: left;
  width: 29px;
  height: 30px;

}
.timepicker-holder .form-control {
  width:60px;
}
.time, 
.date-start-end, 
.date-single, 
.btn-settings
.btn-settings,
.btn-date,
.btn-time,
.btn-app,
.btn-site,
.settings-content {
  display:none;
}
.timepicker-component {
  text-align:center;
}
</style>

<script id="scenario-row" type="text/x-handlebars-template">
    <tr data-i="@{{i}}" data-sl="@{{sl}}">
      <td>
<?php
echo '<select class="scenario-if">';
echo '@{{#select scenario_if}}';
foreach ($scenario_if as $statement) 
{
  if ($statement->id == 1) echo '<optgroup label="' . trans('global.available_app_close') . '">';
  if ($statement->id == 3) echo '<optgroup label="' . trans('global.available_app_open') . '">';

  echo '<option value="' . $statement->id . '">' . trans('global.' . $statement->name) . '</option>';

  if ($statement->id == 2 || $statement->id == 5) echo '</optgroup>';
}
echo '@{{/select}}';
echo '</select>';
?>
      <td>

        <button class="btn btn-primary btn-notification btn-popover" data-toggle="tooltip" title="{{ trans('global.notification') }}"><i class="ion-android-notifications"></i></button>

        <div class="settings-content notification-content">
          <div class="form-group">
            <label>{{ trans('global.push_notification_text') }}</label>
            <textarea class="form-control notification" style="width:220px;height:68px;">@{{notification}}</textarea>
          </div>
          <div class="form-group pull-right">
            <button type="button" class="btn btn-primary btn-sm btn-save"><i class="fa fa-check"></i></button>
            <button type="button" class="btn btn-default btn-sm close-popover"><i class="fa fa-times"></i></button>
          </div>
        </div>

      </td>
      <td>
        <div class="select2-primary"> 
         <select multiple="multiple" class="scenario-places">
<?php
// Beacons in a group
foreach($location_groups as $location_group)
{
  $geofences_in_group = $location_group->geofences()->where('active', '=', 1)->orderBy('name', 'asc')->get();
  $beacons_in_group = $location_group->beacons()->where('active', '=', 1)->orderBy('name', 'asc')->get();

  echo '<optgroup label="' . $location_group->name . '">';

  foreach($geofences_in_group as $geofence)
  {
    echo '<option value="geofence' . $geofence->id . '" {{#ifIn ' . $geofence->id . ' geofences }}selected="selected"{{/ifIn}} data-type="geofence">' . $geofence->name . '</option>';
  }

  foreach($beacons_in_group as $beacon)
  {
    echo '<option value="beacon' . $beacon->id . '" {{#ifIn ' . $beacon->id . ' beacons }}selected="selected"{{/ifIn}} data-type="beacon">' . $beacon->name . '</option>';
  }

  echo '</optgroup>';
}
// Beacons and geofences without a group
foreach($geofences as $geofence)
{
  echo '<option value="geofence' . $geofence->id . '" {{#ifIn ' . $geofence->id . ' geofences }}selected="selected"{{/ifIn}} data-type="geofence">' . $geofence->name . '</option>';
}

foreach($beacons as $beacon)
{
  echo '<option value="beacon' . $beacon->id . '" {{#ifIn ' . $beacon->id . ' beacons }}selected="selected"{{/ifIn}} data-type="beacon">' . $beacon->name . '</option>';
}
?>
         </select> 
        </div>
      </td>
      <td>
<?php
echo '<select class="scenario-then">';
echo '@{{#select scenario_then}}';

echo '<option value="">' . trans('global.do_nothing') . '</option>';
foreach ($scenario_then as $statement) 
{
  echo '<option value="' . $statement->id . '">' . trans('global.' . $statement->name) . '</option>';
}
echo '@{{/select}}';
echo '</select>';
?>
      </td>
      <td>

        <button class="btn btn-primary btn-settings btn-popover btn-img" style="width:40px"data-toggle="tooltip" title="{{ trans('global.image') }}"><i class="fa fa-picture-o"></i></button>

        <div class="settings-content img-content">
          <div class="form-group">
            <input type="hidden" class="show-img" id="show_image@{{i}}" value="@{{show_image}}">

            <div class="btn-group" role="group">
              <button type="button" class="btn btn-primary img-browse" data-id="show_image@{{i}}"><i class="fa fa-picture-o"></i> {{ trans('global.select_image') }}</button>
              <button type="button" class="btn btn-danger img-remove" data-id="show_image@{{i}}" title="{{ trans('global.remove_image') }}"><i class="fa fa-ban"></i></button>
            </div>

            <div id="show_image@{{i}}-image" class="show-image-container">
              @{{#if show_image_thumb}}
              <img src="@{{ show_image_thumb }}" class="thumbnail" style="max-width:100%;margin:10px 0 0 0;">
              @{{/if}}
            </div>

          </div>
          <div class="form-group pull-right">
            <button type="button" class="btn btn-primary btn-sm btn-save"><i class="fa fa-check"></i></button>
            <button type="button" class="btn btn-default btn-sm close-popover"><i class="fa fa-times"></i></button>
          </div>
        </div>

        <button class="btn btn-primary btn-settings btn-tpl" style="width:40px"data-toggle="tooltip" title="{{ trans('global.template') }}"><i class="fa fa-file-text-o"></i></button>

        <div class="settings-content tpl-content">
          <div class="show-template">@{{template}}</div>
<?php /*
          <div class="form-group">
            <label>{{ trans('global.template') }}</label>
            <textarea class="form-control show-template">@{{template}}</textarea>
          </div>
          <div class="form-group pull-right">
            <button type="button" class="btn btn-primary btn-sm btn-save"><i class="fa fa-check"></i></button>
            <button type="button" class="btn btn-default btn-sm close-popover"><i class="fa fa-times"></i></button>
          </div>
*/ ?>
        </div>

        <button class="btn btn-primary btn-settings btn-popover btn-url" style="width:40px" data-toggle="tooltip" title="{{ trans('global.url') }}"><i class="fa fa-link"></i></button>

        <div class="settings-content url-content">
          <div class="form-group">
            <textarea class="form-control open-url" style="width:320px;height:52px;" placeholder="http://">@{{open_url}}</textarea>
          </div>
          <div class="form-group pull-right">
            <button type="button" class="btn btn-primary btn-sm btn-save"><i class="fa fa-check"></i></button>
            <button type="button" class="btn btn-default btn-sm close-popover"><i class="fa fa-times"></i></button>
          </div>
        </div>

        <button class="btn btn-primary btn-settings btn-popover btn-app" style="width:40px" data-toggle="tooltip" title="{{ trans('global.app') }}"><i class="fa fa-mobile"></i></button>

        <div class="settings-content app-content">
<?php
echo '<div class="form-group">';
echo '<select class="form-control show-app" style="width:100%;" onchange="if (this.value != \'\') { $(\'#group_app{{i}}-holder\').hide();$(\'.page-select-group\').hide();$(\'#group_app{{i}}-\' + this.value).show();} else {$(\'#group_app{{i}}-holder\').show();$(\'.page-select-group\').hide();} ">';
echo '@{{#select show_app}}';
echo '<option value="">' . trans('global.select_app') . '...</option>';
foreach ($apps_select as $group => $apps)
{
  echo '<optgroup label="' . $group . '">';

  foreach ($apps as $app_id => $app_name)
  {
    echo '<option value="' . $app_id . '">' . $app_name . '</option>';
  }

  echo '</optgroup>';
}

echo '@{{/select}}';
echo '</select>';
echo '</div>';

echo '<div class="form-group" id="group_app{{i}}-holder">';
echo '<select class="form-control" style="width:100%;" disabled>';
echo '<option value=""></option>';
echo '</select>';
echo '</div>';

foreach ($app_pages as $app_id => $pages)
{
  echo '<div class="form-group page-select-group app' . $app_id . '" id="group_app{{i}}-' . $app_id . '" style="display:none">';
  echo '<select class="form-control show-app-page" style="width:100%;">';
  echo '@{{#select show_app_page}}';

  foreach ($pages as $page)
  {
    echo '<option value="' . $page['id'] . '">' . $page['name'] . '</option>';
  }

  echo '@{{/select}}';
  echo '</select>';
  echo '</div>';
}
?>

          <div class="form-group pull-right">
            <button type="button" class="btn btn-primary btn-sm btn-save"><i class="fa fa-check"></i></button>
            <button type="button" class="btn btn-default btn-sm close-popover"><i class="fa fa-times"></i></button>
          </div>
        </div>

        <button class="btn btn-primary btn-settings btn-popover btn-site" style="width:40px" data-toggle="tooltip" title="{{ trans('global.one_page') }}"><i class="fa fa-laptop"></i></button>

        <div class="settings-content site-content">
<?php
echo '<div class="form-group">';
echo '<select class="form-control show-site" style="width:100%;">';
echo '@{{#select show_site}}';
echo '<option value="">' . trans('global.select_one_page') . '...</option>';
foreach ($sites_select as $group => $sites)
{
  echo '<optgroup label="' . $group . '">';

  foreach ($sites as $site_id => $site_name)
  {
    echo '<option value="' . $site_id . '">' . $site_name . '</option>';
  }

  echo '</optgroup>';
}

echo '@{{/select}}';
echo '</select>';
echo '</div>';
?>

          <div class="form-group pull-right">
            <button type="button" class="btn btn-primary btn-sm btn-save"><i class="fa fa-check"></i></button>
            <button type="button" class="btn btn-default btn-sm close-popover"><i class="fa fa-times"></i></button>
          </div>
        </div>

      </td>
      <td>
<?php
echo '<select class="scenario-date">';
echo '@{{#select scenario_day}}';
foreach ($scenario_day as $statement) 
{
  if ($statement->id == 2) echo '<optgroup label="' . trans('global.range') . '">';
  if ($statement->id == 7) echo '<optgroup label="' . trans('global.day') . '">';

  echo '<option value="' . $statement->id . '">' . trans('global.' . $statement->name) . '</option>';

  if ($statement->id == 6 || $statement->id == 13) echo '</optgroup>';
}
echo '@{{/select}}';
echo '</select>';
?>
      </td>
      <td>
        <button class="btn btn-primary btn-popover btn-date" data-toggle="tooltip" title="{{ trans('global.date_range') }}"><i class="fa fa-calendar-o"></i></button>
        <div class="date-start-end">
          <div class="form-group input-daterange input-group datepicker-component" style="width:232px">
            <input type="text" class="input-sm form-control scenario-date-start" value="@{{date_start}}" placeholder="{{ trans('global.start_date') }}">
            <span class="input-group-addon text-lowercase">-</span>
            <input type="text" class="input-sm form-control scenario-date-end" value="@{{date_end}}" placeholder="{{ trans('global.end_date') }}">
          </div>
          <div class="form-group pull-right">
            <button type="button" class="btn btn-primary btn-sm btn-save"><i class="fa fa-check"></i></button>
            <button type="button" class="btn btn-default btn-sm close-popover"><i class="fa fa-times"></i></button>
          </div>
        </div>
      </td>
      <td>
<?php
echo '<select class="scenario-time">';
echo '@{{#select scenario_time}}';
foreach ($scenario_time as $statement) 
{
  echo '<option value="' . $statement->id . '">' . trans('global.' . $statement->name) . '</option>';
}
echo '@{{/select}}';
echo '</select>';
?>
      </td>
      <td>
        <button class="btn btn-primary btn-popover btn-time" data-toggle="tooltip" title="{{ trans('global.time_range') }}"><i class="fa fa-clock-o"></i></button>
        <div class="time">
          <div class="form-group input-group timepicker-holder" style="width:149px">
            <input type="text" class="input-sm form-control timepicker-component scenario-time-start" value="@{{time_start}}" placeholder="00:00">
            <span class="input-group-addon text-lowercase">-</span>
            <input type="text" class="input-sm form-control timepicker-component scenario-time-end" value="@{{time_end}}" placeholder="00:00">
          </div>
          <div class="form-group pull-right">
            <button type="button" class="btn btn-primary btn-sm btn-save"><i class="fa fa-check"></i></button>
            <button type="button" class="btn btn-default btn-sm close-popover"><i class="fa fa-times"></i></button>
          </div>
        </div>
      </td>
      <td>
        <button class="btn btn-primary btn-popover btn-config" data-toggle="tooltip" title="{{ trans('global.timing') }}"><i class="fa fa-hourglass-start"></i></button>
        <div class="settings-content config-content">
          <div style="width:260px">
            <div class="form-group">
              <label>{{ trans('global.frequency') }}</label>
              <input type="number" class="form-control frequency" value="@{{frequency}}" min="0" placeholder="0">
              <span class="help-block">{{ trans('global.frequency_info') }}</span>
            </div>

            <div class="form-group">
              <label>{{ trans('global.delay') }}</label>
              <input type="number" class="form-control delay" value="@{{delay}}" min="0" placeholder="0">
              <span class="help-block">{{ trans('global.delay_info') }}</span>
            </div>

            <div class="form-group pull-right">
              <button type="button" class="btn btn-primary btn-sm btn-save"><i class="fa fa-check"></i></button>
              <button type="button" class="btn btn-default btn-sm close-popover"><i class="fa fa-times"></i></button>
            </div>
           </div>
        </div>
      </td>
      <td align="right">
        <button type="button" class="btn btn-danger btn-delete" title="{{ trans('global.delete') }}" data-toggle="tooltip" title="{{ trans('global.delete') }}"><i class="fa fa-times"></i></button>
      </td>
    </tr>
</script> 
<script>
<?php /* <i class="fa fa-map-marker"></i> */ ?>

var sl = "{{ $sl }}";
var i = 0;

var thumb_width = 146;
var thumb_height = 0;
var thumb_type = 'resize';
<?php
echo $js;
?>

$('#add_scenario').on('click', function() {
  addRepeaterRow('new', null);
});

function addRepeaterRow(action, data)
{
  var source = $('#scenario-row').html();
  var template = Handlebars.compile(source);

  if(action == 'update')
  {
    var show_image_thumb = (typeof data.show_image !== 'undefined' && data.show_image != '' && data.show_image != null) ? app_root + '/api/v1/thumb/nail?w=' + thumb_width + '&h=' + thumb_height + '&t=' + thumb_type + '&img=' + data.show_image : '';

    var html = template({
      sl: data.sl,
      geofences: data.geofences,
      beacons: data.beacons,
      scenario_if: data.scenario_if_id,
      scenario_then: data.scenario_then_id,
      scenario_day: data.scenario_day_id,
      date_start: data.date_start,
      date_end: data.date_end,
      scenario_time: data.scenario_time_id,
      time_start: data.time_start,
      time_end: data.time_end,
      notification: data.notification,
      frequency: data.frequency,
      delay: data.delay,
      show_image: data.show_image,
      show_image_thumb: show_image_thumb,
      open_url: data.open_url,
      template: data.template
    });

    $('tbl-scenarios #row' + data.i).replaceWith(html);
  }
  else if(action == 'new')
  {
    var request = $.ajax({
      url: "{{ url('/api/v1/scenario/scenario') }}",
      type: 'POST',
      data: {sl : sl},
      dataType: 'json'
    });

    request.done(function(json) {

    if (json.result == 'error')
    {
    swal({
      title: "{{ trans('admin.upgrade_title') }}",
      text: json.result_msg,
      type: "warning",
      confirmButtonClass: "btn-primary",
      confirmButtonText: "{{ trans('global.ok') }}",
      closeOnConfirm: true
    });
     return;
    }

      var html = template({
        i: i++,
        sl: json.sl,
        geofences: [0],
        beacons: [0],
        scenario_if: 1,
        scenario_then: 1,
        scenario_day: 1,
        date_start: null,
        date_end: null,
        scenario_time: 1,
        time_start: null,
        time_end: null,
        notification: '',
        frequency: 0,
        delay: 0,
        show_image: '',
        show_image_thumb: '',
        open_url: '',
        template: '',
        show_app: null,
        show_app_page: null,
        show_site: null
      });

      $('#tbl-scenarios tbody').append(html);
      rowBindings();
      bsTooltipsPopovers();
      showSaved();
    });

    request.fail(function(jqXHR, textStatus) {
      alert('Request failed, please try again (' + textStatus + ')');
    });
  }
  else
  {
    var show_image_thumb = (typeof data.show_image !== 'undefined' && data.show_image != '' && data.show_image != null) ? app_root + '/api/v1/thumb/nail?w=' + thumb_width + '&h=' + thumb_height + '&t=' + thumb_type + '&img=' + data.show_image : '';

    var html = template({
      i: i++,
      sl: data.sl,
      scenario_if: data.scenario_if_id,
      geofences: data.geofences,
      beacons: data.beacons,
      scenario_then: data.scenario_then_id,
      scenario_day: data.scenario_day_id,
      date_start: data.date_start,
      date_end: data.date_end,
      scenario_time: data.scenario_time_id,
      time_start: data.time_start,
      time_end: data.time_end,
      notification: data.notification,
      frequency: data.frequency,
      delay: data.delay,
      show_image: data.show_image,
      show_image_thumb: show_image_thumb,
      open_url: data.open_url,
      template: data.template,
      show_app: data.show_app,
      show_app_page: data.show_app_page,
      show_site: data.show_site
    });

    $('#tbl-scenarios tbody').append(html);
    rowBindings();
  }
}

/* Close all other popovers */
/*
$('.btn-popover').on('click', function() {
  var this_btn = $(this);
  $('.btn-popover').each(function () {
    if ($(this).next('div.popover:visible').length) {
      $(this).not(this_btn).popover('hide');
      $(this).not(this_btn).next('.popover').remove();
    }
  });
});
*/
$('html').on('click', function (e) {
  /*console.log(e.target);*/
  $('.btn-popover').each(function () {
    if (! $(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0 && 
      $('.datepicker').has(e.target).length === 0 && 
      ! $(e.target).hasClass('day') && 
      ! $(e.target).hasClass('popover') && 
      ! $(e.target).is('#cboxClose') && 
      ! $(e.target).is('#cboxOverlay')) {
 
      if (typeof $(this).popover !== 'undefined')
      {
        if ($(this).next('.popover').is(':visible'))
        {
          $(this).popover('hide');
          $(this).next('.popover').remove();
        }
      }
    }
  });
});

$('#tbl-scenarios').on('click', '.close-popover', function (e) {
  var popover = $(this).closest('.popover').prev('.btn-popover');
  $(popover).popover('hide');
  $(popover).next('.popover').remove();
});

function rowBindings()
{
  $('table#tbl-scenarios > tbody > tr').not('.binded').each(function() {
    var tr = $(this);
    var sl_scenario = $(this).attr('data-sl');
    tr.addClass('binded');

    /* Check options */
    checkScenarioIf(tr);
    checkScenarioThen(tr);
    checkScenarioDate(tr);
    checkScenarioTime(tr);
<?php
/*
 -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 */
?>
    /* Scenario if */
    $(this).find('.scenario-if').select2(
    {
      allowClear: false,
      minimumResultsForSearch: -1
    })
    .on("change", function(e) {
      var value = e.val;

      checkScenarioIf(tr);

      var request = $.ajax({
        url: "{{ url('/api/v1/scenario/update-scenario') }}",
        type: 'POST',
        data: {
          sl: sl_scenario,
          name: 'scenario-if',
          value: value
        },
        dataType: 'json'
      });

      request.done(function(json) { showSaved(); });
      request.fail(function(jqXHR, textStatus) { alert('Request failed, please try again (' + textStatus + ')');});
    });
<?php
/*
 -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 */
?>
    /* Geofences & beacons */
    $(this).find('.scenario-places').select2({
      formatResult: function(result) {
        if (typeof result.children !== 'undefined') /* Group title */
        {
          return result.text;
        }

        var type = result.element[0].getAttribute('data-type');

        if (type == 'geofence') return '<i class="fa fa-map-marker"></i> ' + result.text;
        if (type == 'beacon') return '<i class="fa fa-dot-circle-o"></i> ' + result.text;
      },
      formatSelection: function(result) {
        if (typeof result.children !== 'undefined') /* Group title */
        {
          return result.text;
        }

        var type = result.element[0].getAttribute('data-type');

        if (type == 'geofence') return '<i class="fa fa-map-marker"></i> ' + result.text;
        if (type == 'beacon') return '<i class="fa fa-dot-circle-o"></i> ' + result.text;
      }
    })
    .on("change", function(e) {
      closeAllPopovers();
      var value = e.val;

      var request = $.ajax({
        url: "{{ url('/api/v1/scenario/update-scenario-places') }}",
        type: 'POST',
        data: {
          sl: sl_scenario,
          places: value
        },
        dataType: 'json'
      });

      request.done(function(json) { showSaved(); });
      request.fail(function(jqXHR, textStatus) { alert('Request failed, please try again (' + textStatus + ')'); });
    });

    /* Scenario then */
    $(this).find('.scenario-then').select2(
    {
      allowClear: false,
      minimumResultsForSearch: -1
    })
    .on("change", function(e) {
      var value = e.val;

      checkScenarioThen(tr);

      var request = $.ajax({
        url: "{{ url('/api/v1/scenario/update-scenario') }}",
        type: 'POST',
        data: {
          sl: sl_scenario,
          name: 'scenario-then',
          value: value
        },
        dataType: 'json'
      });
  
      request.done(function(json) { showSaved(); });
      request.fail(function(jqXHR, textStatus) { alert('Request failed, please try again (' + textStatus + ')'); });
    });
<?php
/*
 -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 */
?>
    /* Scenario when day */
    $(this).find('.scenario-date').select2(
    {
      allowClear: false,
      minimumResultsForSearch: -1
    })
    .on("change", function(e) {
      var value = e.val;

      checkScenarioDate(tr);

      var request = $.ajax({
        url: "{{ url('/api/v1/scenario/update-scenario') }}",
        type: 'POST',
        data: {
          sl: sl_scenario,
          name: 'scenario-when-date',
          value: value
        },
        dataType: 'json'
      });
  
      request.done(function(json) { showSaved(); });
      request.fail(function(jqXHR, textStatus) { alert('Request failed, please try again (' + textStatus + ')'); });
    });

    /* Date range picker popover */
    var date_range_picker = $(this).find('.date-start-end');
    var btn_date = $(this).find('.btn-date');

    $(btn_date).popover({
      placement:'top', 
      html : true, 
      content: function() { 
        /*if (this.cache) return this.cache;
        return this.cache = $(date_range_picker).html();*/
        return $(date_range_picker).html();
      },
      showCallback: function() {

      }
    }).on('shown.bs.popover', function (e) {
      /* Date range picker */
      $(tr).find('.popover-content .datepicker-component').datepicker({
        format: 'yyyy-mm-dd'
      });

      var popover = $(this);

      /* Bind save */
      $(this).next().find('.popover-content .btn-save').on('click', function() {
        var date_start = tr.find('.scenario-date-start').data('datepicker').getDate();
        var date_end = tr.find('.scenario-date-end').data('datepicker').getDate();

        date_start = (isNaN(date_start.getFullYear())) ? '' : date_start.getFullYear() + '-' + (date_start.getMonth() + 1) + '-' + date_start.getDate();
        date_end = (isNaN(date_end.getFullYear())) ? '' : date_end.getFullYear() + '-' + (date_end.getMonth() + 1) + '-' + date_end.getDate();

        /* Set dates to hidden form */
        tr.find('.date-start-end .scenario-date-start').attr('value', date_start);
        tr.find('.date-start-end .scenario-date-end').attr('value', date_end);

        /* Close popover */
        $(popover).popover('hide'); $(popover).next('.popover').remove();

        var request = $.ajax({
          url: "{{ url('/api/v1/scenario/update-scenario') }}",
          type: 'POST',
          data: {sl : sl_scenario, name : 'datepicker-range', date_start: date_start, date_end: date_end},
          dataType: 'json'
        });

        request.done(function(json) { showSaved(); });
        request.fail(function(jqXHR, textStatus) { alert('Request failed, please try again (' + textStatus + ')'); });
      });

    });
<?php
/*
 -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 */
?>
    /* Scenario when time */
    $(this).find('.scenario-time').select2(
    {
      allowClear: false,
      minimumResultsForSearch: -1
    })
    .on("change", function(e) {
      var value = e.val;

      checkScenarioTime(tr);

      var request = $.ajax({
        url: "{{ url('/api/v1/scenario/update-scenario') }}",
        type: 'POST',
        data: {
          sl: sl_scenario,
          name: 'scenario-when-time',
          value: value
        },
        dataType: 'json'
      });
  
      request.done(function(json) { showSaved(); });
      request.fail(function(jqXHR, textStatus) { alert('Request failed, please try again (' + textStatus + ')'); });
    });

    /* Time range picker popover */
    var time_picker = $(this).find('.time');

    $(this).find('.btn-time').popover({
      placement:'left', 
      html : true, 
      content: function() { 
        return $(time_picker).html();
      },
      showCallback: function() { }
    })
    .on('shown.bs.popover', function (e) {

      /* Set dates */
      var time_start = tr.find('.time .scenario-time-start').val();
      var time_end = tr.find('.time .scenario-time-end').val();

      /* Time picker */
      var timepicker_opts = {
        minuteStep: 5,
        appendWidgetTo: 'body',
        showSeconds: false,
        showMeridian: false,
        showInputs: false,
        defaultTime: false,
        orientation: $('body').hasClass('right-to-left') ? { x: 'right', y: 'auto'} : { x: 'auto', y: 'auto'}
      };

      $(tr).find('.popover-content .timepicker-component').timepicker(timepicker_opts);

      var popover = $(this);

      /* Bind save */
      $(this).next().find('.popover-content .btn-save').on('click', function() {
        var time_start = tr.find('.popover-content .scenario-time-start').data('timepicker');
        var time_end = tr.find('.popover-content .scenario-time-end').data('timepicker');
      
        time_start = (typeof time_start === 'undefined' || isNaN(time_start.hour) || isNaN(time_start.minute)) ? '' : time_start.hour + ':' + time_start.minute + ':00';
        time_end = (typeof time_end === 'undefined' || isNaN(time_end.hour) || isNaN(time_end.minute)) ? '' : time_end.hour + ':' + time_end.minute + ':00';

        /* Set dates to hidden form */
        tr.find('.time .scenario-time-start').attr('value', time_start);
        tr.find('.time .scenario-time-end').attr('value', time_end);

        /* Close popover */
        $(popover).popover('hide'); $(popover).next('.popover').remove();

        var request = $.ajax({
          url: "{{ url('/api/v1/scenario/update-scenario') }}",
          type: 'POST',
          data: {sl : sl_scenario, name : 'time-range', time_start: time_start, time_end: time_end},
          dataType: 'json'
        });

        request.done(function(json) { showSaved(); });
        request.fail(function(jqXHR, textStatus) { alert('Request failed, please try again (' + textStatus + ')'); });
      });

    });
<?php
/*
 -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 */
?>
    /* Notification */
    var notification_content = $(this).find('.notification-content');
    var btn_notification = $(this).find('.btn-notification');

    $(btn_notification).popover({
      placement:'top', 
      html : true, 
      content: function() { 
        return $(notification_content).html();
      }
    }).on('shown.bs.popover', function (e) {

      var popover = $(this);

      /* Bind save */
      $(this).next().find('.popover-content .btn-save').on('click', function() {

        /* Get values(s) from popover */
        var notification = tr.find('.popover-content .notification').val();

        /* Set value(s) to hidden form */
        tr.find('.settings-content .notification').text(notification);

        /* Close popover */
        $(popover).popover('hide'); $(popover).next('.popover').remove();

        var request = $.ajax({
          url: "{{ url('/api/v1/scenario/update-scenario') }}",
          type: 'POST',
          data: {sl : sl_scenario, name : 'notification', value: notification},
          dataType: 'json'
        });

        request.done(function(json) { showSaved(); });
        request.fail(function(jqXHR, textStatus) { alert('Request failed, please try again (' + textStatus + ')'); });
      });
    });

    /* Image */
    var img_content = $(this).find('.img-content');
    var btn_img = $(this).find('.btn-img');

    $(btn_img).popover({
      placement:'right', 
      html : true, 
      content: function() { 
        return $(img_content).html();
      }
    }).on('shown.bs.popover', function (e) {

      var popover = $(this);

      /* Bind save */
      $(this).next().find('.popover-content .btn-save').on('click', function() {

        /* Get values(s) from popover */
        var show_image = tr.find('.popover-content .show-img').val();
        var show_image_thumb = tr.find('.popover-content .thumbnail').attr('src');

        /* Set value(s) to hidden form */
        tr.find('.settings-content .show-img').attr('value', show_image);
        if (typeof show_image_thumb === 'undefined')
        {
          tr.find('.settings-content .show-image-container').html('');
        }
        else
        {
          tr.find('.settings-content .show-image-container').html('<img src="' + show_image_thumb + '" class="thumbnail" style="max-width:100%;margin:10px 0 0 0;">');
        }

        /* Close popover */
        $(popover).popover('hide'); $(popover).next('.popover').remove();

        var request = $.ajax({
          url: "{{ url('/api/v1/scenario/update-scenario') }}",
          type: 'POST',
          data: {sl : sl_scenario, name : 'show_image', value: show_image},
          dataType: 'json'
        });

        request.done(function(json) { showSaved(); });
        request.fail(function(jqXHR, textStatus) { alert('Request failed, please try again (' + textStatus + ')'); });
      });
      
    });

    /* Template */
    var tpl_content = $(this).find('.tpl-content');
    var btn_tpl = $(this).find('.btn-tpl');

    $(btn_tpl).on('click', function() {

      $.colorbox(
      {
        href: app_root + '/app/editor?i=' + tr.attr('data-i'),
        fastIframe: true,
        iframe: true,
        scrolling: false,
        width: '60%',
        height: '95%',
        escKey: false,
        overlayClose: false,
        onClosed: function (e) {
           return;
        }
      });

    });

    /* Url */
    var url_content = $(this).find('.url-content');
    var btn_url = $(this).find('.btn-url');

    $(btn_url).popover({
      placement:'right', 
      html : true, 
      content: function() { 
        return $(url_content).html();
      }
    }).on('shown.bs.popover', function (e) {

      var popover = $(this);

      /* Bind save */
      $(this).next().find('.popover-content .btn-save').on('click', function() {

        /* Get values(s) from popover */
        var open_url = tr.find('.popover-content .open-url').val();

        /* Set value(s) to hidden form */
        tr.find('.settings-content .open-url').html(open_url);

        /* Close popover */
        $(popover).popover('hide'); $(popover).next('.popover').remove();

        var request = $.ajax({
          url: "{{ url('/api/v1/scenario/update-scenario') }}",
          type: 'POST',
          data: {sl : sl_scenario, name : 'open_url', value: open_url},
          dataType: 'json'
        });

        request.done(function(json) { showSaved(); });
        request.fail(function(jqXHR, textStatus) { alert('Request failed, please try again (' + textStatus + ')'); });
      });

    });

    /* App */
    var app_content = $(this).find('.app-content');
    var btn_app = $(this).find('.btn-app');

    $(btn_app).popover({
      placement:'right', 
      html : true, 
      content: function() { 
        return $(app_content).html();
      }
    }).on('shown.bs.popover', function (e) {

      var popover = $(this);

      /* Set values */
      var show_app = tr.find('.settings-content .show-app').val();
      var show_app_page = tr.find('.settings-content .app' + show_app + ' .show-app-page').val();
      tr.find('.popover-content .show-app').val(show_app).change();
      tr.find('.popover-content .app' + show_app + ' .show-app-page').val(show_app_page);

      /* Bind save */
      $(this).next().find('.popover-content .btn-save').on('click', function() {

        /* Get values(s) from popover */
        var show_app = tr.find('.popover-content .show-app').val();
        var show_app_page = tr.find('.popover-content .app' + show_app + ' .show-app-page').val();

        /* Set value(s) to hidden form */
        tr.find('.settings-content .show-app').val(show_app);
        tr.find('.settings-content .app' + show_app + ' .show-app-page').val(show_app_page);

        /* Close popover */
        $(popover).popover('hide'); $(popover).next('.popover').remove();

        var request = $.ajax({
          url: "{{ url('/api/v1/scenario/update-scenario') }}",
          type: 'POST',
          data: {sl : sl_scenario, name : 'show_app', value: show_app + ',' + show_app_page},
          dataType: 'json'
        });

        request.done(function(json) { showSaved(); });
        request.fail(function(jqXHR, textStatus) { alert('Request failed, please try again (' + textStatus + ')'); });
      });

    });

    /* Landing Page */
    var site_content = $(this).find('.site-content');
    var btn_site = $(this).find('.btn-site');

    $(btn_site).popover({
      placement:'right', 
      html : true, 
      content: function() { 
        return $(site_content).html();
      }
    }).on('shown.bs.popover', function (e) {

      var popover = $(this);

      /* Set value */
      var show_site = tr.find('.settings-content .show-site').val();
      tr.find('.popover-content .show-site').val(show_site).change();

      /* Bind save */
      $(this).next().find('.popover-content .btn-save').on('click', function() {

        /* Get values(s) from popover */
        var show_site = tr.find('.popover-content .show-site').val();

        /* Set value(s) to hidden form */
        tr.find('.settings-content .show-site').val(show_site);

        /* Close popover */
        $(popover).popover('hide'); $(popover).next('.popover').remove();

        var request = $.ajax({
          url: "{{ url('/api/v1/scenario/update-scenario') }}",
          type: 'POST',
          data: {sl : sl_scenario, name : 'show_site', value: show_site},
          dataType: 'json'
        });

        request.done(function(json) { showSaved(); });
        request.fail(function(jqXHR, textStatus) { alert('Request failed, please try again (' + textStatus + ')'); });
      });

    });

    /* General scenario settings */
    var config_content = $(this).find('.config-content');
    var btn_config = $(this).find('.btn-config');

    $(btn_config).popover({
      placement:'left', 
      html : true, 
      content: function() { 
        return $(config_content).html();
      }
    }).on('shown.bs.popover', function (e) {

      var popover = $(this);

      /* Bind save */
      $(this).next().find('.popover-content .btn-save').on('click', function() {

        /* Get values(s) from popover */
        var frequency = tr.find('.popover-content .frequency').val();
        var delay = tr.find('.popover-content .delay').val();

        /* Set value(s) to hidden form */
        tr.find('.config-content .frequency').attr('value', frequency);
        tr.find('.config-content .delay').attr('value', delay);

        /* Close popover */
        $(popover).popover('hide'); $(popover).next('.popover').remove();

        var request = $.ajax({
          url: "{{ url('/api/v1/scenario/update-scenario') }}",
          type: 'POST',
          data: {sl : sl_scenario, name : 'config', frequency: frequency, delay: delay},
          dataType: 'json'
        });

        request.done(function(json) { showSaved(); });
        request.fail(function(jqXHR, textStatus) { alert('Request failed, please try again (' + textStatus + ')'); });
      });

    });

  });
}

function escapeHtml(str) {
  var div = document.createElement('div');
  div.appendChild(document.createTextNode(str));
  return div.innerHTML;
};

/* UNSAFE with unsafe strings; only use on previously-escaped ones! */
function unescapeHtml(escapedStr) {
  var div = document.createElement('div');
  div.innerHTML = escapedStr;
  var child = div.childNodes[0];
  return child ? child.nodeValue : '';
};

function saveTemplateEditor(i, content)
{
  $.colorbox.close();

  var tr = $('#tbl-scenarios tr[data-i=' + i + ']');
     var sl_scenario = tr.attr('data-sl');

  tr.find('.settings-content .show-template').html(escapeHtml(content));

  var request = $.ajax({
    url: "{{ url('/api/v1/scenario/update-scenario') }}",
    type: 'POST',
    data: {sl : sl_scenario, name : 'template', value: content},
    dataType: 'json'
  });

  request.done(function(json) { showSaved(); });
  request.fail(function(jqXHR, textStatus) { alert('Request failed, please try again (' + textStatus + ')'); });
}

function getTemplateContent(i)
{
  var tr = $('#tbl-scenarios tr[data-i=' + i + ']');
  var content = tr.find('.settings-content .show-template').html();
  content = unescapeHtml(content);

  return content;
}

function checkScenarioIf(tr)
{
  closeAllPopovers();
   var scenario = $(tr).find('.scenario-if'); 
  scenario = (scenario.hasClass('select2-container')) ? scenario.select2('val') : scenario.val();

  $(tr).find('.btn-notification').hide();

  switch(parseInt(scenario))
  {
    case 1:
    case 2:
      $(tr).find('.btn-notification').show();
      break;
  }
}

function checkScenarioThen(tr)
{
  closeAllPopovers();
   var scenario = $(tr).find('.scenario-then'); 
  scenario = (scenario.hasClass('select2-container')) ? scenario.select2('val') : scenario.val();

  $(tr).find('.btn-settings').hide();

  switch(parseInt(scenario))
  {
    case 2:
      $(tr).find('.btn-img').show();
      break;
    case 3:
      $(tr).find('.btn-tpl').show();
      break;
    case 4:
      $(tr).find('.btn-url').show();
      break;
    case 12:
      $(tr).find('.btn-app').show();
      break;
    case 13:
      $(tr).find('.btn-site').show();
      break;
  }
}

function checkScenarioDate(tr)
{
  closeAllPopovers();
   var scenario = $(tr).find('.scenario-date'); 

  scenario = (scenario.hasClass('select2-container')) ? scenario.select2('val') : scenario.val();

  $(tr).find('.btn-date').hide();

  switch(parseInt(scenario))
  {
    case 2:
      $(tr).find('.btn-date').show();
      break;
  }
}

function checkScenarioTime(tr)
{
  closeAllPopovers();
   var scenario = $(tr).find('.scenario-time'); 
  scenario = (scenario.hasClass('select2-container')) ? scenario.select2('val') : scenario.val();

  $(tr).find('.btn-time').hide();

  switch(parseInt(scenario))
  {
    case 2:
      $(tr).find('.btn-time').show();
      break;
  }
}

function closeAllPopovers()
{
  $('.btn-popover').each(function () {
    if ($(this).next('div.popover:visible').length) {
      $(this).popover('hide');
      $(this).next('.popover').remove();
    }
  });
}

$('#tbl-scenarios').on('click', '.btn-delete', function() {
  var row = $(this).parents('tr');
  var sl_scenario = row.attr('data-sl');
  var request = $.ajax({
    url: "{{ url('/api/v1/scenario/delete-scenario') }}",
    type: 'POST',
     data: {sl : sl_scenario},
    dataType: 'json'
  });

  request.done(function(json) {
    row.remove();
    showSaved();
  });

  request.fail(function(jqXHR, textStatus) {
    alert('Request failed, please try again (' + textStatus + ')');
  });
});

var elfinderUrl = 'elfinder/standalonepopup/';

$('#tbl-scenarios').on('click', '.img-browse', function(event)
{
  if(event.handled !== true)
  {
    $.colorbox(
    {
      href: elfinderUrl + $(this).attr('data-id') + '/processBoardFile',
      fastIframe: true,
      iframe: true,
      width: '70%',
      height: '80%'
    });

    event.handled = true;
  }

  return false;
});

$('#tbl-scenarios').on('click', '.img-remove', function(event)
{
  if(event.handled !== true)
  {
    $('#' + $(this).attr('data-id') + '-image').html('');
    $('#' + $(this).attr('data-id')).val('');
    event.handled = true;
  }

  return false;
});

/* Callback after elfinder selection */
window.processBoardFile = function(filePath, requestingField)
{
  if($('#' + requestingField).attr('type') == 'text')
  {
    $('#' + requestingField).val(decodeURI(filePath));
  }

  if($('#' + requestingField + '-image').length)
  {
    var img = decodeURI(filePath);
    var thumb = '{{ url('/api/v1/thumb/nail?') }}w=' + thumb_width + '&h=' + thumb_height + '&t=' + thumb_type + '&img=' + filePath;

    $('#' + requestingField + '-image').addClass('bg-loading');

    $('<img/>').attr('src', decodeURI(thumb)).load(function() {
      $(this).remove();
      $('#' + requestingField + '-image').html('<img src="' + thumb + '" class="thumbnail" style="max-width:100%; margin:10px 0 0 0;">');
      $('#' + requestingField + '-image').removeClass('bg-loading');
    });

    $('#' + requestingField).val(img);

    /* Reposition popover */
    $('#' + requestingField).closest('.popover').css('top', '-150px');
  }
};

$('#btnDeleteBoard').on('click', function() {
  swal({
    title: "{{ trans('global.are_you_sure') }}",
    text: "{{ trans('global.confirm_delete_scenario_board') }}",
    type: "warning",
    showCancelButton: true,
    confirmButtonClass: "btn-danger",
    confirmButtonText: "{{ trans('global.delete_scenario_board') }}",
    cancelButtonText: "{{ trans('global.cancel') }}",
    closeOnConfirm: true,
    closeOnCancel: true
  },
  function(isConfirm)
  {
    if(isConfirm)
    {
    blockUI();
    var request = $.ajax({
      url: "{{ url('/api/v1/scenario/delete-board') }}",
      type: 'GET',
      data: {data : "{{ $sl }}"},
      dataType: 'json'
    });

    request.done(function(json) {

      /* Decrement count */
      var count = parseInt($('#count_boards').text());
      $('#count_boards').text(count-1);

      /* Open overview */
      document.location = '#/boards?' + new Date().getTime();
      unblockUI();
    });

    request.fail(function(jqXHR, textStatus) {
      alert('Request failed, please try again (' + textStatus, ')');
      unblockUI();
    });
    }
  });
});

$('#board_name').editable({
  url: '{{ url('/api/v1/scenario/board-title') }}',
  ajaxOptions: {
    type: 'post'
  },
  success: function(response, newValue) {
    if(response.status == 'error') return response.msg;
    showSaved();
  }
});

/* After board update callback */
function formSubmittedSuccess()
{
  /* Leave empty to prevent this function being called from other page */
}
</script> 
@stop