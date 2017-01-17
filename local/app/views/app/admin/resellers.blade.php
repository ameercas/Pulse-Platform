@extends('../app.layouts.partial')

@section('content')
  <ul class="breadcrumb breadcrumb-page">
    <div class="breadcrumb-label text-light-gray">{{ trans('global.you_are_here') }} </div>
    <li><a href="{{ trans('global.home_crumb_url') }}">{{ trans('global.home_crumb_text') }}</a></li>
    <li>{{ trans('admin.system_administration') }}</li>
    <li class="active">{{ trans('admin.resellers') }}</li>
  </ul>


  <div class="page-header">
    <div class="row">
      <h1 class="col-xs-12 col-sm-4 text-center text-left-sm"><i class="fa fa-user-secret page-header-icon"></i> {{ trans('admin.resellers') }}</h1>
<?php if ($reseller->id == 1) { ?>
      <div class="pull-right col-xs-12 col-sm-auto">
        <a href="#/admin/reseller" class="btn btn-primary btn-labeled" style="width: 100%;"><span class="btn-label icon fa fa-plus"></span> {{ trans('admin.new_reseller') }}</a>
      </div>
<?php } ?>
    </div>
  </div>


<?php
if($resellers->count() > 0)
{
?>

<script>
var admin_resellers_table = $('#dt-table-admin_resellers').DataTable({
  ajax: "{{ url('/api/v1/admin/reseller-data') }}",
  order: [
    [0, "asc"]
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
  columns: [
  {
    data: "domain"
  },
  {
    data: "mail_from_address"
  },
  {
    data: "contact_name"
  },
  {
    data: "contact_business"
  },
  {
    data: "reseller",
    sortable: false
  },
  {
    data: "created_at"
  },
  {
    data: "active"
  },
  {
    data: "sl",
    sortable: false
  }],
  fnDrawCallback: function() {
    onDataTableLoad();
  },
  columnDefs: [
    {
      render: function (data, type, row) {
        return '<span data-moment="fromNowDateTime">' + data + '</span>';
      },
      targets: [5] /* Column to re-render */
    },
    {
      render: function (data, type, row) {
        return '<a href="{{ url('/api/v1/admin/login-as') }}/' + row.reseller_sl + '" class="" data-toggle="tooltip" title="{{ trans('global.login') }}">' + data + ' <i class="fa fa-sign-in"></i></a> ';      },
      targets: 4 /* Column to re-render */
    },
    {
      render: function (data, type, row) {
        var disabled = (row.undeletable == '1') ? ' disabled' : '';
        return '<div class="row-actions-wrap"><div class="text-right row-actions" data-sl="' + data + '">' +
          '<a href="#/admin/reseller/' + data + '" class="btn btn-xs btn-success row-btn-edit" data-toggle="tooltip" title="{{ trans('global.edit') }}"><i class="fa fa-pencil"></i></a> ' + 
          '<a href="javascript:void(0);" class="btn btn-xs btn-danger row-btn-delete" data-toggle="tooltip" title="{{ trans('global.delete') }}"' + disabled + '><i class="fa fa-trash"></i></a>' + 
          '</div></div>';
      },
      targets: 7 /* Column to re-render */
    },
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
      targets: 6
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
});

$('#dt-table-admin_resellers_wrapper .dataTables_filter input').attr('placeholder', "{{ trans('global.search_') }}");

</script>
  <div class="table-primary">
    <table class="table table-striped table-bordered table-hover" id="dt-table-admin_resellers">
      <thead>
        <tr>
          <th>{{ Lang::get('global.domain') }}</th>
          <th>{{ Lang::get('global.email') }}</th>
          <th>{{ Lang::get('admin.contact') }}</th>
          <th>{{ Lang::get('global.business') }}</th>
          <th>{{ Lang::get('admin.reseller') }}</th>
          <th>{{ Lang::get('global.created') }}</th>
          <th>{{ Lang::get('global.active') }}</th>
          <th class="text-right">{{ Lang::get('global.actions') }}</th>
        </tr>
      </thead>
    </table>
  </div>

<script>

$('#dt-table-admin_resellers').on('click', '.row-btn-delete', function() {
  var sl = $(this).parent('.row-actions').attr('data-sl');

  swal({
    title: "{{ trans('admin.delete_reseller_confirm') }}",
    type: "warning",
    showCancelButton: true,
    cancelButtonText: _lang['cancel'],
    confirmButtonColor: "#DD6B55",
    confirmButtonText: _lang['yes_delete']
  }, 
  function(){
    if (confirm(_lang['confirm'])) {
      blockUI();
  
      var jqxhr = $.ajax({
        url: "{{ url('/api/v1/admin/reseller-delete') }}",
        data: { sl: sl},
        method: 'POST'
      })
      .done(function(data) {
        if(data.result == 'success')
        {
          admin_resellers_table.ajax.reload();
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
    }
  });
});

</script>
<?php
}
?>

@stop