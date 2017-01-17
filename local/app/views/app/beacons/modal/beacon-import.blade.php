<div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<button class="close" type="button" data-dismiss="modal">×</button>
			<?php echo trans('global.import_beacons') ?>
        </div>
		<div class="modal-body">
			<div class="container-fluid">
<?php
$user_id = Auth::user()->id;

echo Former::open_for_files()
	->class('form-horizontal validate')
    ->target('iSubmit')
	->action(url('api/v1/beacon/import'))
	->method('POST');

echo Former::file()
	->class('styled')
    ->name('avatar')
	->label(trans('global.upload_csv'))
	->help(trans('global.upload_csv_help'))
	->dataBvNotempty()
    ->required();

	echo Former::actions()
		->lg_primary_submit(trans('global.upload'));	

echo Former::close();
?>
                        <iframe name="iSubmit" id="iSubmit" frameborder="0" src="about:blank" style="display:none;width:0;height:0"></iframe>

			</div>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" type="button"><?php echo Lang::get('global.close') ?></button>
		</div>
	</div>
</div>
<script>
override_form = true;

function formSubmittedSuccess()
{

    $modal.modal('hide');
    //$('.pfi-clear').trigger('click');
}

$('form.validate').bootstrapValidator({
    feedbackIcons: {
        valid: null,
        invalid: null,
        validating: null
    }
});
</script>