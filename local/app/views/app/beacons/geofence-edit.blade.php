@extends('../app.layouts.partial')

@section('content')
	<ul class="breadcrumb breadcrumb-page">
		<div class="breadcrumb-label text-light-gray">{{ trans('global.you_are_here') }} </div>
		<li><a href="{{ trans('global.home_crumb_url') }}">{{ trans('global.home_crumb_text') }}</a></li>
		<li><a href="#/geofences">{{ trans('global.geofences') }}</a></li>
		<li class="active">{{ trans('global.edit_geofence') }}</li>
	</ul>

	<div class="page-header">
		<h1><i class="fa fa-map-marker page-header-icon"></i> {{ trans('global.edit_geofence') }}</h1>
	</div>

<?php
echo Former::open()
	->class('form ajax ajax-validate')
	->action(url('api/v1/geofence/save'))
	->method('POST');

echo Former::hidden()
		->name('sl')
		->forceValue($sl);
?>
		  <div class="panel"> 
		   <div class="panel-body padding-sm padding-t">
<?php
$value = (isset($geofence->locationGroup->id)) ? json_encode(['id' => $geofence->locationGroup->id, 'text' => $geofence->locationGroup->name]) : '';

echo Former::text()
    ->name('name')
    ->autocomplete('off')
    ->placeholder(trans('global.geofence_name_info'))
	->dataBvNotempty()
    ->autofocus()
    ->required()
    ->help(trans('global.beacon_name_info_info'))
    ->forceValue($geofence->name)
	->label(trans('global.name'));

echo Former::text()
    ->name('group')
    ->useDatalist($location_groups, 'name')
    ->value($value)
	->class('select2-datalist form-control')
    ->autocomplete('off')
    ->help(trans('global.location_group_info'))
	->label(trans('global.group'));

echo '<hr class="no-grid-gutter-h" style="clear:both">';

echo Former::number()
    ->forceValue($geofence->radius)
    ->name('radius')
    ->min(75)
    ->autocomplete('off')
	->dataBvNotempty()
    ->required()
    ->append(trans('global.meter'))
	->label(trans('global.radius'));

echo Former::hidden()
    ->name('location')
    ->forceValue(($geo != NULL) ? '' : $geofence_location)
    ->id('location');

echo '<div id="map-picker-container">';
echo '<div id="map-picker">';
echo '<div class="map-overlay"></div>';
echo '<div class="map-container"></div>';
echo '</div>';
echo '<p class="help-block">' . trans('global.marker_info') . '</p>';
echo '</div>';

echo '<hr class="no-grid-gutter-h" style="clear:both">';

echo Former::actions(
    Former::submit(trans('global.save'))->class('btn-lg btn-primary btn')->id('btn-submit'),
    Former::link(trans('global.cancel'))->class('btn-lg btn-default btn')->href('#/geofences')
);
?>
			 </div>
		   </div>
		</div>
<?php
echo Former::close();
?>
<script>
/* Prevent form submit on press enter map search */
$('#map-picker-container').on('keydown', '.search-input', function(event){
	if(event.keyCode == 13) {
	  event.preventDefault();
	  return false;
	}
});

/* Animated marker via http://bl.ocks.org/4284949 */
L.Marker.prototype.animateDragging = function () {
  
  var iconMargin, shadowMargin;
  
  this.on('dragstart', function () {
    if (!iconMargin) {
      iconMargin = parseInt(L.DomUtil.getStyle(this._icon, 'marginTop'));
      shadowMargin = parseInt(L.DomUtil.getStyle(this._shadow, 'marginLeft'));
    }
  
    this._icon.style.marginTop = (iconMargin - 15)  + 'px';
    this._shadow.style.marginLeft = (shadowMargin + 8) + 'px';
  });
  
  return this.on('dragend', function () {
    this._icon.style.marginTop = iconMargin + 'px';
    this._shadow.style.marginLeft = shadowMargin + 'px';
  });
};

var Map = function(elem, lat, lng) {
    this.$el = $(elem);
    this.$overlay = this.$el.find('.map-overlay');
    this.$map = this.$el.find('.map-container');
    this.init(lat, lng);
};

Map.prototype.init = function(lat, lng) {
    
    this.lat = lat;
    this.lng = lng;
    
	$('#location').val(this.lat + ',' + this.lng);
    this.map = L.map(this.$map[0]).setView([lat, lng], 13);
    
	this.map.scrollWheelZoom.disable();

    var osmUrl='//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
    var mapTiles = new L.TileLayer(osmUrl, {
        attribution: 'Map data &copy; '
        + '<a href="http://openstreetmap.org">OpenStreetMap</a>, '
        + '<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
        maxZoom: 19
    });
    
    this.map.addLayer(mapTiles);

	this.map.addControl( new L.Control.Search({
		url: '//nominatim.openstreetmap.org/search?format=json&q={s}',
		jsonpParam: 'json_callback',
		propertyName: 'display_name',
		propertyLoc: ['lat','lon'],
		circleLocation: false,
		markerLocation: false,			
		autoType: false,
		autoCollapse: true,
		minLength: 2,
		zoom:16
	}) );
};

Map.prototype.setCircle = function(latLng, meters) {
    if(!this.circle) {
        this.circle = L.circle(latLng, meters, {
            color: 'red',
            fillColor: '#f03',
            fillOpacity: 0.3
        }).addTo(this.map);
    }
    else {
        this.circle.setLatLng(latLng);
        this.circle.setRadius(meters);
        this.circle.redraw();
    }
    this.map.fitBounds(this.circle.getBounds());
};

Map.prototype.setLatLng = function(latLng) {
    this.lat = latLng.lat;
    this.lng = latLng.lng;

	$('#location').val(this.lat + ',' + this.lng);

    if(this.circle) {
        this.circle.setLatLng(latLng);
    }
};

Map.prototype.centerOnLocation = function(lat, lng) {
    
    var self = this;
    
    this.lat = lat;
    this.lng = lng;
    
    if(!this.marker) {
        this.marker = L.marker([this.lat, this.lng], {
            draggable: true
        });
    
        this.marker.on('drag', function(event) {
            self.setLatLng(event.target.getLatLng());            
        });
        
        this.marker
            .animateDragging()
            .addTo(this.map);
    }

	this.map.on('click', function(e) {
		var newLatLng = new L.LatLng(e.latlng.lat, e.latlng.lng);
		self.setLatLng(newLatLng);
    	self.marker.setLatLng(newLatLng);
	});
    this.map.setView([this.lat, this.lng]);
    this.setCircle([this.lat, this.lng], $('#radius').val());
};

/* Overlay message methods */
Map.prototype.dismissMessage = function() {
    this.$el.removeClass('show-message');
    this.$overlay.html('');       
};

Map.prototype.showMessage = function(html) {
    this.$overlay.html('<div class="center"><div>' + html + '</div></div>');
    this.$el.addClass('show-message');
};

/* Conversion Helpers */
Map.prototype.milesToMeters = function(miles) {
    return miles * 1069;
};

jQuery(function($) {
  
    /* clear temporary background image */
    $('.map-container').css('background', 'transparent');

    var map = new Map('#map-picker', {{ $lat }}, {{ $lng }});
    map.centerOnLocation({{ $lat }}, {{ $lng }});

    var s = $('#radius').on('change keyup', function(e) {
        var value = $(this).val();
        /*var meters = map.milesToMeters(value);*/
        var meters = value;
        map.setCircle([map.lat, map.lng], meters);
    });    

});

function formSubmittedSuccess(r)
{
    if(r.result == 'error')
    {
        return;
    }

	// Open geofence overview
	document.location = '#/geofences';
}
</script>
@stop