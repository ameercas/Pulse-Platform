@extends('../app.layouts.partial')

@section('content')
  <ul class="breadcrumb breadcrumb-page">
    <div class="breadcrumb-label text-light-gray">{{ trans('global.you_are_here') }} </div>
    <li><a href="{{ trans('global.home_crumb_url') }}">{{ trans('global.home_crumb_text') }}</a></li>
    <li>{{ trans('admin.system_administration') }}</li>
    <li><a href="#/admin/resellers">{{ trans('admin.resellers') }}</a></li>
    <li class="active">{{ trans('admin.edit_reseller') }}</li>
  </ul>

  <div class="page-header">
    <h1><i class="fa fa-user-secret page-header-icon"></i> {{ trans('admin.edit_reseller') }}</h1>
  </div>
<?php
echo Former::open()
  ->class('form-horizontal validate')
  ->action(url('api/v1/admin/reseller-new'))
  ->method('POST');

?>
  <div class="panel">
    <div class="panel-body padding-sm">
<?php

echo '<div class="row"><div class="col-md-6"><div class="col-lg-offset-2 col-sm-offset-4"><h2>' . trans('global.general') . '</h2></div><br style="clear:both"></div></div>';

echo '<div class="row">';
echo '<div class="col-md-6">';

echo Former::text()
  ->name('domain')
  ->forceValue('')
  ->label(trans('admin.domain'))
  ->dataBvNotempty()
  ->dataFvNotemptyMessage(trans('global.please_enter_a_value'))
  ->autocapitalize('off')
  ->autocorrect('off')
  ->autocomplete('off')
  ->prepend('http://')
  ->help(trans('admin.reseller_domain_info', ['host' => $master_reseller->domain]))
  ->required();

echo Former::checkbox()
  ->name('active')
  ->label(trans('global.active'))
  ->dataClass('switcher-success')
  ->help(trans('admin.reseller_active_info'))
  ->check(true);

echo '</div>';
echo '<div class="col-md-6">';

echo Former::select('user_id')
  ->class('select2-required form-control')
  ->name('user_id')
  ->forceValue('')
  ->addOption(null)
  ->fromQuery(\User::orderBy('email')->get(), 'email', 'id')
  ->help(trans('admin.reseller_info'))
  ->label(trans('admin.reseller'));

echo '</div>';
echo '</div>';

echo '<hr>';

echo '<div class="row"><div class="col-md-6"><div class="col-lg-offset-2 col-sm-offset-4"><h2>' . trans('admin.email_configuration') . '</h2></div><br style="clear:both"></div></div>';

echo '<div class="row">';
echo '<div class="col-md-6">';

echo Former::email()
  ->name('mail_from_address')
  ->forceValue('')
  ->label(trans('admin.mail_from_address'))
  ->dataBvNotempty()
  ->dataFvNotemptyMessage(trans('global.please_enter_a_value'))
  ->dataFvRegexp(true)
  ->dataFvRegexpRegexp('^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+$')
  ->dataFvRegexpMessage(trans('global.please_enter_a_valid_email_address'))
  ->autocomplete('off')
  ->required();

echo Former::text()
  ->name('mail_from_name')
  ->forceValue('')
  ->label(trans('admin.mail_from_name'))
  ->dataBvNotempty()
  ->dataFvNotemptyMessage(trans('global.please_enter_a_value'))
  ->autocapitalize('off')
  ->autocorrect('off')
  ->autocomplete('off')
  ->required();

echo Former::text()
  ->name('mail_username')
  ->forceValue('')
  ->label(trans('global.username'))
  ->dataBvNotempty()
  ->dataFvNotemptyMessage(trans('global.please_enter_a_value'))
  ->autocapitalize('off')
  ->autocorrect('off')
  ->autocomplete('off')
  ->required();

echo Former::password()
  ->name('mail_password')
  ->forceValue('')
  ->label(trans('global.password'))
  ->append(Former::button('<i class="fa fa-eye"></i>')->id('show_password')->tooltip(trans('global.show_password'))->tooltipPlacement('top')->dataToggle('button'))
  ->dataBvNotempty()
  ->dataFvNotemptyMessage(trans('global.please_enter_a_value'))
  ->autocapitalize('off')
  ->autocorrect('off')
  ->autocomplete('off')
  ->required();

echo '</div>';
echo '<div class="col-md-6">';

echo Former::text()
  ->name('mail_host')
  ->forceValue('')
  ->autocomplete('off')
  ->label(trans('admin.smtp_host'))
  ->forceValue('')
  ->dataBvNotempty()
  ->dataFvNotemptyMessage(trans('global.please_enter_a_value'))
  ->autocapitalize('off')
  ->autocorrect('off')
  ->autocomplete('off')
  ->required();

echo Former::number()
  ->name('mail_port')
  ->forceValue('465')
  ->autocomplete('off')
  ->label(trans('admin.smtp_port'))
  ->forceValue('')
  ->dataBvNotempty()
  ->dataFvNotemptyMessage(trans('global.please_enter_a_value'))
  ->autocapitalize('off')
  ->autocorrect('off')
  ->autocomplete('off')
  ->required();

echo Former::select('mail_encryption')
  ->class('select2-required form-control')
  ->name('mail_encryption')
  ->forceValue('ssl')
  ->options(['tls' => 'tls', 'ssl' => 'ssl'])
  ->label(trans('admin.encryption'))
  ->required();

echo '</div>';
echo '</div>';

echo '<hr>';

echo '<div class="row"><div class="col-md-6"><div class="col-lg-offset-2 col-sm-offset-4"><h2>' . trans('admin.app_configuration') . '</h2><p class="lead">' . trans('admin.app_configuration_info') . '</p></div><br style="clear:both"></div></div>';

echo '<div class="row">';
echo '<div class="col-md-6">';

echo Former::text()
  ->name('app_name')
  ->forceValue('')
  ->label(trans('admin.app_name'));

echo '</div>';
echo '<div class="col-md-6">';

echo Former::text()
  ->name('app_link_ios')
  ->forceValue('')
  ->label(trans('admin.app_link_ios'));

echo Former::text()
  ->name('app_link_android')
  ->forceValue('')
  ->label(trans('admin.app_link_android'));

echo '</div>';
echo '</div>';

echo '<hr>';

echo '<div class="row"><div class="col-md-6"><div class="col-lg-offset-2 col-sm-offset-4"><h2>' . trans('admin.contact_information') . '</h2></div><br style="clear:both"></div></div>';

echo '<div class="row">';
echo '<div class="col-md-6">';

echo Former::text()
  ->name('contact_business')
  ->forceValue('')
  ->label(trans('admin.business'))
  ->dataBvNotempty()
  ->dataFvNotemptyMessage(trans('global.please_enter_a_value'))
  ->autocapitalize('off')
  ->autocorrect('off')
  ->autocomplete('off')
  ->required();

echo Former::text()
  ->name('contact_name')
  ->forceValue('')
  ->label(trans('admin.contact_name'))
  ->dataBvNotempty()
  ->dataFvNotemptyMessage(trans('global.please_enter_a_value'))
  ->autocapitalize('off')
  ->autocorrect('off')
  ->autocomplete('off')
  ->required();

echo Former::text()
  ->name('contact_mail')
  ->forceValue('')
  ->label(trans('admin.contact_mail'))
  ->dataBvNotempty()
  ->dataFvNotemptyMessage(trans('global.please_enter_a_value'))
  ->autocapitalize('off')
  ->autocorrect('off')
  ->autocomplete('off')
  ->required();

echo Former::text()
  ->name('contact_phone')
  ->forceValue('')
  ->label(trans('admin.phone'))
  ->autocapitalize('off')
  ->autocorrect('off')
  ->autocomplete('off');

echo '</div>';
echo '<div class="col-md-6">';

echo Former::text()
  ->name('contact_address1')
  ->forceValue('')
  ->label(trans('admin.address1'))
  ->autocapitalize('off')
  ->autocorrect('off')
  ->autocomplete('off');

echo Former::text()
  ->name('contact_address2')
  ->forceValue('')
  ->label(trans('admin.address2'))
  ->autocapitalize('off')
  ->autocorrect('off')
  ->autocomplete('off');

echo Former::text()
  ->name('contact_zip')
  ->forceValue('')
  ->label(trans('admin.zip'))
  ->autocapitalize('off')
  ->autocorrect('off')
  ->autocomplete('off');

echo Former::text()
  ->name('contact_city')
  ->forceValue('')
  ->label(trans('admin.city'))
  ->autocapitalize('off')
  ->autocorrect('off')
  ->autocomplete('off');

echo Former::text()
  ->name('contact_country')
  ->forceValue('')
  ->label(trans('admin.country'))
  ->autocapitalize('off')
  ->autocorrect('off')
  ->autocomplete('off');

echo '</div>';
echo '</div>';

echo '<hr>';

echo '<div class="row">';
echo '<div class="col-md-6">';

echo Former::actions()
  ->lg_primary_submit(trans('global.save'))
  ->lg_default_link(trans('global.cancel'), '#/admin/resellers');

echo '</div>';
echo '<div class="col-md-6">';

echo '</div>';
echo '</div>';

?>
    </div>
  </div>
<?php
echo Former::close();
?>
<script>
function formSubmittedSuccess(result)
{
  if(result == 'error')
  {
    return;
  }
  document.location = '#/admin/resellers';
}

$('#show_password').on('click', function()
{
  if(! $(this).hasClass('active'))
  {
    togglePassword('mail_password', true);
  }
  else
  {
    togglePassword('mail_password', false);
  }
});
</script>
@stop