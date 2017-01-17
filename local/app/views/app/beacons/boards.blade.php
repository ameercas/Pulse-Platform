@extends('../app.layouts.partial')

@section('content')
	<ul class="breadcrumb breadcrumb-page">
		<div class="breadcrumb-label text-light-gray">{{ trans('global.you_are_here') }} </div>
		<li><a href="{{ trans('global.home_crumb_url') }}">{{ trans('global.home_crumb_text') }}</a></li>
		<li class="active">{{ trans('global.scenario_boards') }}</li>
	</ul>

    <div class="page-header">
        <div class="row">
            <h1 class="col-xs-12 col-sm-4 text-center text-left-sm"><i class="ion-android-notifications page-header-icon"></i> {{ trans('global.scenario_boards') }}</h1>
            <div class="col-xs-12 col-sm-8">
                <div class="row">
                    <hr class="visible-xs no-grid-gutter-h">

                    <div class="pull-right col-xs-12 col-sm-auto"><a href="#/board" class="btn btn-primary btn-labeled" style="width: 100%;"><span class="btn-label icon fa fa-plus"></span> {{ trans('global.new_scenario_board') }}</a></div>
                    <div class="visible-xs clearfix form-group-margin"></div>

<?php
if($scenario_boards->count() > 0)
{
?>
                    <form action="" class="pull-right col-xs-12 col-sm-6">
                        <div class="input-group no-margin">
                            <span class="input-group-addon" style="border:none;background: #fff;background: rgba(0,0,0,.05);"><i class="fa fa-search"></i></span>
                            <input type="text" id="search_grid" placeholder="{{ trans('global.search_') }}" class="form-control no-padding-hr" style="border:none;background: #fff;background: rgba(0,0,0,.05);">
                        </div>
                    </form>
<?php
}
?>
                </div>
            </div>
        </div>
    </div>

<?php
if($scenario_boards->count() > 0)
{
?>
	<div class="row" id="grid">
<?php
$i = 0;
foreach($scenario_boards as $scenario_board)
{
	$sl_board = \App\Core\Secure::array2string(array('scenario_board_id' => $scenario_board['id']));
    $photo = $scenario_board['photo_file_name'];
    if($photo == '')
    {
        $photo = url('/assets/images/interface/scenario-board.png');
    }
	else
	{
		$photo = $scenario_board->photo->url('thumbnail');
	}
?>
	<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4" id="board{{ $i }}">
		<div class="panel panel-default panel-dark panel-body-colorful widget-profile widget-profile-centered widget-profile-default">
			<div class="panel-heading">
				<a href="#/board/{{ $sl_board }}">
					<img src="{{ $photo }}" alt="" class="widget-profile-avatar" style="width:auto; height:150px">
				</a>
				<div class="widget-profile-header">
					<span class="ellipsis-oneline">{{ $scenario_board['name'] }}</span>
				</div>
			</div> <!-- / .panel-heading -->
            <div class="widget-profile-text">
<?php
foreach($scenario_board->apps as $app)
{
    $sl_app = \App\Core\Secure::array2string(array('app_id' => $app->id));
    echo '<a href="#/app/edit/' . $sl_app . '" class="label label-info" style="margin:2px 0;"><i class="fa fa-th"></i> ' . $app->name . '</a> ';
}
foreach($scenario_board->sites as $site)
{
    $sl_site = \App\Core\Secure::array2string(array('site_id' => $site->id));
    echo '<a href="#/site/edit/' . $sl_site . '" class="label label-info" style="margin:2px 0;"><i class="fa fa-laptop"></i> ' . $site->name . '</a> ';
}
if(count($scenario_board->apps) == 0 && count($scenario_board->sites) == 0)
{
    echo '<div style="margin:3px" class="text-muted">' . trans('global.no_content_linked_to_board') . '</div>';
}
?>
		    </div>
			<div class="widget-profile-counters">
				<div class="col-xs-4 stat-block">
                    <span class="stat-interactions"><div class="small-throbber"> </div></span>
                    <div class="ellipsis-oneline-small">{{ trans('global.interactions') }}</div>
                </div>
				<div class="col-xs-4 stat-block">
                    <span class="stat-scenarios">{{ $scenario_board->scenarios->count() }}</span>
                    <div class="ellipsis-oneline-small">{{ trans('global.scenarios') }}</div>
                </div>
<?php /*
				<div class="col-xs-4"><span><i class="fa fa-check icon-active"></i></span><br>PUBLISHED {{ \App\Core\Help::popover('app_published') }}</div>
				<div class="col-xs-4"><span><i class="fa fa-times icon-nonactive"></i></span><br>CHANGES {{ \App\Core\Help::popover('app_changes') }}</div>
*/ ?>
				<div class="col-xs-4">
					<a href="#/board/{{ $sl_board }}" class="btn btn-default btn-xs" data-toggle="tooltip" title="{{ trans('global.edit_scenario_board') }}"><i class="fa fa-pencil fa-1x"></i></a>
					<a href="javascript:void(0);" data-sl="{{ $sl_board }}" class="btn btn-danger btn-xs btn-delete" data-toggle="tooltip" title="{{ trans('global.delete_scenario_board') }}"><i class="fa fa-trash fa-1x"></i></a>
				</div>
			</div>
			<br style="clear:both">

		</div> <!-- / .panel -->
	</div>

<script>
<?php /*
$.getJSON("{{ url('/api/v1/interaction-analytics/interactions?sl=' . $sl_board) }}", function(data) {
	$('#board{{ $i }} .stat-interactions').html('<a href="#/stats/board/{{ $sl_board }}">' + data.interactions + '</a>');
});
*/?>
$.getJSON("{{ url('/api/v1/interaction-analytics/interactions?sl=' . $sl_board) }}", function(data) {
	$('#board{{ $i }} .stat-interactions').html('' + data.interactions + '');
});
</script>

<?php
	$i++;
}
?>
	</div>
<script>

$('#grid').liveFilter('#search_grid', 'div.col-xs-12', {
  filterChildSelector: '.widget-profile-header'
});

</script>

<?php
}
else
{
	// No records yet
?>
<div class="callout pull-left arrow-right-up">{{ Lang::get('global.add_first_scenario') }} <i class="fa fa-arrow-circle-up fa-2x fa-rotate-45"></i></div>
<?php
}
?>

<script>

$('.btn-delete').on('click', function() {
	var sl = $(this).attr('data-sl');
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
          data: {data : sl},
          dataType: 'json'
        });

        request.done(function(json) {

            /* Decrement count */
            var count = parseInt($('#count_boards').text());
            $('#count_boards').text(count-1);

            /* Open boards overview */
			angular.element($('#content-wrapper')).injector().get("$route").reload();
            unblockUI();
        });

        request.fail(function(jqXHR, textStatus) {
            alert('Request failed, please try again (' + textStatus, ')');
            unblockUI();
        });
      }
    });
});

</script>
@stop