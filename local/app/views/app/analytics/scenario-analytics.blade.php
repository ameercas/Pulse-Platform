@extends('../app.layouts.partial')
@section('content')
<ul class="breadcrumb breadcrumb-page">
  <div class="breadcrumb-label text-light-gray">{{ trans('global.you_are_here') }} </div>
  <li><a href="{{ trans('global.home_crumb_url') }}">{{ trans('global.home_crumb_text') }}</a></li>
  <li><a href="#/boards">{{ trans('global.scenario_boards') }}</a></li>
  <li>{{ trans('global.analytics') }}</li>
  <li class="active">{{ trans('global.scenarios') }}</li>
</ul>
<?php
  $title = trans('global.scenarios');
  ?>
<div class="page-header">
  <div class="row">
    <h1 class="col-xs-12 text-center text-left-sm" style="height:32px"><i class="ion-radio-waves page-header-icon"></i> {{ $title }}</h1>
  </div>
</div>
<?php
  if($stats_found)
  {
  ?>
<script>
var scenarios_table = $('#dt-table-scenarios').DataTable({
    ajax: "{{ url('/api/v1/interaction-analytics/scenario-data') }}",
    order: [
        [6, "desc"]
    ],
    dom: "<'row'<'col-sm-12 dt-header'<'pull-left'lr><'pull-right'f><'pull-right hidden-sm hidden-xs'T><'clearfix'>>>t<'row'<'col-sm-12 dt-footer'<'pull-left'i><'pull-right'p><'clearfix'>>>",
    sPaginationType: 'bootstrap',
    processing: true,
    serverSide: true,
    stateSave: true,
    stripeClasses: [],
    lengthMenu: [
        [10, 25, 50, 75, 100, 200, 500, 1000, 1000000],
        [10, 25, 50, 75, 100, 200, 500, 1000, "{{ trans('global.all') }}"]
    ],
    rowCallback: function(row, data) {
		if($.inArray(data.DT_RowId.replace('row_', ''), selected_scenarios) !== -1)
		{
			$(row).addClass('success');
		}
	},
	fnDrawCallback: function() {
		onDataTableLoad();
    jdenticon();
	},
	columns: [ {
		data: "platform"
	},{
		data: "device_uuid"
	},{
		data: "model"
	}, {
    data: "app_site",
    sortable: false
	}, {
		data: "state"
	}, {
		data: "encounter",
    sortable: false
	}, {
		data: "created_at"
	}<?php /*,
  {
      data: "sl",
      sortable: false
  }*/ ?>],
	columnDefs: [
		{
			render: function (data, type, row) {
				if(data == 'iOS')
				{
					return '<div class="text-center"><i class="ion-social-apple" style="font-size:18px; color: #8e8e93; position: absolute; margin-top:-3px"></i></div>';
				}
				else
				{
					return '<div class="text-center"><i class="ion-social-android" style="font-size:18px; color: #a4c639; position: absolute; margin-top:-3px"></i></div>';
				}
			},
			targets: 0 /* Column to re-render */
		},
		{
			render: function (data, type, row) {
        if(row.site_name != null)
        {
          return '<i class=""></i> ' + row.site_name;
        }
        else
        {
          return '<i class=""></i> ' + row.app_name;
        }
			},
			targets: 3 /* Column to re-render */
		},
		{
			render: function (data, type, row) {
        if(row.beacon != null)
        {
          return '<i class=""></i> ' + row.beacon;
        }
        else
        {
          return '<i class=""></i> ' + row.geofence;
        }
			},
			targets: 5 /* Column to re-render */
		},
		{
			render: function (data, type, row) {
				return '<svg title="' + data + '" data-toggle="tooltip" width="34" height="34" data-jdenticon-hash="' + row.device_uuid_hash + '" style="height:25px;position:absolute; margin:-4px 0 0 0"></svg>';
			},
			targets: 1 /* Column to re-render */
		},
		{
			render: function (data, type, row) {
				return '<img src="/assets/images/state-icons/' + row.state_raw + '.svg" style="height:18px;position: relative; margin:-3px 3px 0 0"> ' + data;
			},
			targets: 4 /* Column to re-render */
		}<?php /*,
    {
			render: function (data, type, row) {
				return '<div class="row-actions-wrap"><div class="row-actions" data-sl="' + data + '">' + 
					'<a href="#/beacon/edit/' + data + '" class="btn btn-xs btn-primary row-btn-details" data-toggle="tooltip" title="{{ trans('global.view') }}"><i class="fa fa-search"></i></a> ' + 
					'</div></div>';
			},
			targets: 5
		} */ ?>
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
    },
    oTableTools: {
        sSwfPath: "{{ url('/assets/swf/tabletools/copy_csv_xls_pdf.swf') }}",
        sRowSelect: "os",
        aButtons: [{
            "sExtends": "copy",
            "sButtonText": '<i class="fa fa-files-o"></i>'
        }, {
            "sExtends": "xls",
            "sFileName": "*.xls",
            "sButtonText": '<i class="fa fa-file-excel-o"></i>'
        }, {
            "sExtends": "pdf",
            "sButtonText": '<i class="fa fa-file-pdf-o"></i>'
        }]
    }
});
$('#dt-table-scenarios_wrapper .dataTables_filter input').attr('placeholder', "{{ trans('global.search_') }}");

$('#dt-table-scenarios tbody').on('click dblclick', 'tr', function(e) {
    if(e.target.nodeName == 'TD')
    {
        var td_index = $(e.target).index();
    }
    else
    {
        var td_index = $(e.target).parents('td').index();
    }
    if(td_index == 8) return;

    var id = this.id.replace('row_', '');
    var index = $.inArray(id, selected_scenarios);

    if (index === -1) {
        selected_scenarios.push(id);
    } else {
        selected_scenarios.splice(index, 1);
    }

    $(this).toggleClass('success');
});

</script>
	<div class="table-primary">
		<table class="table table-striped table-bordered table-hover" id="dt-table-scenarios">
			<thead>
				<tr>
					<th class="text-center" style="width:90px">{{ Lang::get('global.platform') }}</th>
					<th>{{ Lang::get('global.device') }}</th>
					<th>{{ Lang::get('global.model') }}</th>
					<th>{{ Lang::get('global.app') . ' / ' . Lang::get('global.one_page') }}</th>
					<th>{{ Lang::get('global.trigger') }}</th>
					<th>{{ Lang::get('global.beacon') . ' / ' . Lang::get('global.geofence') }}</th>
					<th>{{ Lang::get('global.date') }}</th>
		      <!-- <th class="text-center" style="width:52px">{{ Lang::get('global.actions') }}</th> -->
				</tr>
			</thead>
		</table>
	</div>
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
@stop