@extends('../app.layouts.partial')

@section('content')
	<ul class="breadcrumb breadcrumb-page">
		<div class="breadcrumb-label text-light-gray">{{ trans('global.you_are_here') }} </div>
		<li><a href="{{ trans('global.home_crumb_url') }}">{{ trans('global.home_crumb_text') }}</a></li>
		<li class="active">{{ trans('global.beacons') }}</li>
	</ul>

	<div class="page-header">
		<div class="row">
			<h1 class="col-xs-12 col-sm-4 text-center text-left-sm"><i class="fa fa-dot-circle-o page-header-icon"></i> {{ trans('global.beacons') }}</h1>

<?php
if ($beacons->count() > 0)
{
?>
				<div class="pull-right col-xs-12 col-sm-auto">
					<div class="btn-group" style="width: 100%;">
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" tooltip="{{ trans('global.options') }}" aria-expanded="false" style="width: 100%;"<?php
if ($beacons->count() == 0) echo ' disabled';
?>>
						<i class="icon fa fa-bars"></i>
						&nbsp;
						<i class="fa fa-caret-down"></i>
						</button>

						<ul class="dropdown-menu" role="menu">
<?php /*
							<li><a href="javascript:void(0);" data-modal="{{ url('/app/modal/beacon-import') }}"><i class="fa fa-plus"></i> {{ Lang::get('global.import_beacons') }}</a></li>
							<li class="divider"></li>
*/ ?>
							<li><a href="javascript:void(0);" id="select-all"><i class="fa fa-check-square-o"></i> {{ Lang::get('global.select_all') }}</a></li>
							<li><a href="javascript:void(0);" id="deselect-all"><i class="fa fa-square-o"></i> {{ Lang::get('global.deselect_all') }}</a></li>
							<li class="divider"></li>
							<li class="nav-header nav-link disabled"><a href="javascript:void(0);">{{ Lang::get('global.with_selected') }}</a></li>
							<li class="must-have-selection"><a href="javascript:void(0);" id="selected-switch"><i class="fa fa-toggle-off"></i> {{ Lang::get('global.toggle_active') }}</a></li>
							<li class="must-have-selection"><a href="javascript:void(0);" id="selected-delete"><i class="fa fa-trash"></i> {{ Lang::get('global.delete_selected') }}</a></li>
						</ul>
					</div>
				</div>
<?php
}
?>
				<div class="pull-right col-xs-12 col-sm-auto">
					<a href="#/beacon/new" class="btn btn-primary btn-labeled" style="width: 100%;"><span class="btn-label icon fa fa-plus"></span> {{ trans('global.new_beacon') }}</a>
				</div>
		</div>
	</div>

<?php
if ($beacons->count() > 0)
{
?>

<script>
var beacons_table = $('#dt-table-beacons').DataTable({
    ajax: "{{ url('/api/v1/beacon/data') }}",
    order: [
        [0, "asc"],
        [1, "asc"]
    ],
    dom: "<'row'<'col-sm-12 dt-header'<'pull-left'lr><'pull-right'f><'pull-right hidden-sm hidden-xs'T><'clearfix'>>>t<'row'<'col-sm-12 dt-footer'<'pull-left'i><'pull-right'p><'clearfix'>>>",
    processing: true,
    serverSide: true,
    stateSave: true,
    stripeClasses: [],
    lengthMenu: [
        [10, 25, 50, 75, 100, 1000000],
        [10, 25, 50, 75, 100, "{{ trans('global.all') }}"]
    ],
    rowCallback: function(row, data) {
		if($.inArray(data.DT_RowId.replace('row_', ''), selected_beacons) !== -1)
		{
			$(row).addClass('success');
		}
	},
	fnDrawCallback: function() {
		onDataTableLoad();
	},
	columns: [{
		data: "location_group_id"
	},{
		data: "name"
	}, {
		data: "uuid"
	}, {
		data: "major"
	}, {
		data: "minor"
	}, {
		data: "active"
	},
    {
        data: "sl",
        sortable: false
    }],
	columnDefs: [
		{
			render: function (data, type, row) {
				if(data == 1)
				{
					return '<div class="text-center"><i class="fa fa-check icon-active"></i></div>';
				}
				else
				{
					return '<div class="text-center"><i class="fa fa-times icon-nonactive"></i></div>';
				}
			},
			targets: 5 /* Column to re-render */
		},
		{
			render: function (data, type, row) {
				return '<div class="text-center">' + data + '</div>';
			},
			targets: [3, 4] // Column to re-render
		},
		{
			render: function (data, type, row) {
				return '<div class="row-actions-wrap"><div class="row-actions" data-sl="' + data + '">' + 
					'<a href="#/beacon/edit/' + data + '" class="btn btn-xs btn-success row-btn-edit" data-toggle="tooltip" title="{{ trans('global.edit') }}"><i class="fa fa-pencil"></i></a> ' + 
					'<a href="javascript:void(0);" class="btn btn-xs btn-danger row-btn-delete" data-toggle="tooltip" title="{{ trans('global.delete') }}"><i class="fa fa-trash"></i></a>' + 
					'</div></div>';
			},
			targets: 6 /* Column to re-render */
		}
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
})
.on('init.dt', function() {
	var count = $(this).dataTable().fnGetData().length;
	if(count == 0)
	{
		//$('.must-have-selection').prop('disabled', true);
		$('.must-have-selection').addClass('disabled');
	}
});

// Click
$('#dt-table-beacons').on('click', 'tr', function() {
	checkButtonVisibility();
});

$('#dt-table-beacons_wrapper .dataTables_filter input').attr('placeholder', "{{ trans('global.search_') }}");

$('#dt-table-beacons tbody').on('click dblclick', 'tr', function(e) {
    if(e.target.nodeName == 'TD')
    {
        var td_index = $(e.target).index();
    }
    else
    {
        var td_index = $(e.target).parents('td').index();
    }
    if(td_index == 6) return;

    var id = this.id.replace('row_', '');
    var index = $.inArray(id, selected_beacons);

    if (index === -1) {
        selected_beacons.push(id);
    } else {
        selected_beacons.splice(index, 1);
    }

    $(this).toggleClass('success');
});


checkButtonVisibility();

function checkButtonVisibility()
{
    var disabled = (parseInt(selected_beacons.length) > 0) ? false : true;
	if (disabled)
	{
		$('.must-have-selection').addClass('disabled');
	}
	else
	{
		$('.must-have-selection').removeClass('disabled');
	}
    //$('.must-have-selection').prop('disabled', disabled);
}
</script>
	<div class="table-primary">
		<table class="table table-striped table-bordered table-hover" id="dt-table-beacons">
			<thead>
				<tr>
					<th>{{ Lang::get('global.group') }}</th>
					<th>{{ Lang::get('global.name') }}</th>
					<th>{{ Lang::get('global.uuid') }}</th>
					<th>{{ Lang::get('global.major') }}</th>
					<th>{{ Lang::get('global.minor') }}</th>
					<th class="text-center">{{ Lang::get('global.active') }}</th>
					<th class="text-center" style="width:52px">{{ Lang::get('global.actions') }}</th>
				</tr>
			</thead>
		</table>
	</div>

<script>
$('#select-all').on('click', function() {
	selected_beacons = [];

	$('#dt-table-beacons tbody tr').each(function() {
		var id = this.id.replace('row_', '');
		selected_beacons.push(id);
	});

	checkButtonVisibility();
	beacons_table.ajax.reload();
});

$('#deselect-all').on('click', function() {
	selected_beacons = [];
	checkButtonVisibility();
	beacons_table.ajax.reload();
});

$('#dt-table-beacons').on('click', '.row-btn-delete', function() {
    var sl = $(this).parent('.row-actions').attr('data-sl');

	swal({
	  title: _lang['confirm'],
	  type: "warning",
	  showCancelButton: true,
	  cancelButtonText: _lang['cancel'],
	  confirmButtonColor: "#DD6B55",
	  confirmButtonText: _lang['yes_delete']
	}, 
	function(){
		blockUI();
	
		var jqxhr = $.ajax({
			url: "{{ url('/api/v1/beacon/delete') }}",
			data: { sl: sl},
			method: 'POST'
		})
		.done(function(data) {
            if(data.result == 'success')
            {
    			beacons_table.ajax.reload();
				// Set count
				var count = parseInt($('#count_beacons').text());
				$('#count_beacons').text(count-1);
            }
            else
            {
                swal(data.msg);
            }
		})
		.fail(function() {
			console.log('error');
		})
		.always(function() {
			unblockUI();
		});
	});
});

$('#selected-delete').on('click', function() {
	if (! $(this).parent('li').hasClass('disabled'))
	{
		swal({
		  title: _lang['confirm'],
		  type: "warning",
		  showCancelButton: true,
		  cancelButtonText: _lang['cancel'],
		  confirmButtonColor: "#DD6B55",
		  confirmButtonText: _lang['yes_delete']
		}, 
		function(){
			blockUI();
		
			var jqxhr = $.ajax({
				url: "{{ url('/api/v1/beacon/delete') }}",
				data: { ids: selected_beacons},
				method: 'POST'
			})
			.done(function() {
				selected_beacons = [];
				beacons_table.ajax.reload();
				checkButtonVisibility();
				// Set count
				$('#count_beacons').text(beacons_table.page.info().recordsTotal);
			})
			.fail(function() {
				console.log('error');
			})
			.always(function() {
				unblockUI();
			});
		});
	}
});

$('#selected-switch').on('click', function() {
    blockUI();

    var jqxhr = $.ajax({
        url: "{{ url('/api/v1/beacon/switch') }}",
        data: { ids: selected_beacons},
        method: 'POST'
    })
    .done(function() {
        selected_beacons = [];
        beacons_table.ajax.reload();
        checkButtonVisibility();
    })
    .fail(function() {
        console.log('error');
    })
    .always(function() {
        unblockUI();
    });
});
</script>

<?php
}
else
{
	// No records yet
?>
<div class="callout pull-left arrow-right-up">{{ Lang::get('global.add_first_beacon') }} <i class="fa fa-arrow-circle-up fa-2x fa-rotate-45"></i></div>
<?php
}
?>

@stop