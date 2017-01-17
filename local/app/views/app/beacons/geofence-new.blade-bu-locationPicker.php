@extends('../app.layouts.partial')

@section('content')
	<ul class="breadcrumb breadcrumb-page">
		<div class="breadcrumb-label text-light-gray">{{ trans('global.you_are_here') }} </div>
		<li><a href="{{ trans('global.home_crumb_url') }}">{{ trans('global.home_crumb_text') }}</a></li>
		<li><a href="#/geofences">{{ trans('global.geofences') }}</a></li>
		<li class="active">{{ trans('global.new_geofence') }}</li>
	</ul>

	<div class="page-header">
		<h1><i class="fa fa-map-marker page-header-icon"></i> {{ trans('global.new_geofence') }}</h1>
	</div>

<?php
echo Former::open()
	->class('form ajax ajax-validate')
	->action(url('api/v1/geofence/save'))
	->method('POST');
?>
		  <div class="panel"> 
		   <div class="panel-body padding-sm"> 
			<div class="note note-info">{{ trans('global.add_geofence_global_info') }}</div>
<?php
echo Former::text()
    ->name('group')
    ->useDatalist($location_groups, 'name')
    /*->value('{"id": "1", "text": "Store" }')*/
	->class('select2-datalist form-control')
    ->autocomplete('off')
    ->help(trans('global.location_group_info'))
	->label(trans('global.location'));

echo '<hr class="no-grid-gutter-h" style="clear:both">';

echo Former::text()
    ->name('name')
    ->autocomplete('off')
    ->help(trans('global.geofence_name_info'))
	->dataBvNotempty()
    ->required()
	->label(trans('global.name'));

echo '<hr class="no-grid-gutter-h" style="clear:both">';

echo Former::text()
    ->name('location')
    ->autocomplete('off')
	->dataBvNotempty()
    ->required()
    ->help(trans('global.location_map_info'))
	->label(trans('global.region'));

echo Former::number()
    ->forceValue(25)
    ->name('radius')
    ->autocomplete('off')
    ->help(trans('global.radius_info'))
	->dataBvNotempty()
    ->required()
	->label(trans('global.radius'));

?>
<style type="text/css">
#map > div {
    position: absolute;
    width: 100%;
    height: 100%;
    z-index: 0;
}

#map.show-message {
    pointer-events: none;
}

#map .map-overlay {
    display: none;
    z-index: 2;
}

#map.show-message .map-overlay {
    display: block;
    background: rgba(0,0,0,0.5);   
    text-align: center;
    color: #eee;
    text-shadow: 0 -1px 1px black;
}

.center {
    width: 100%;
    height: 100%;
    display: table;
    text-align: center;
}

.center > div {
    display: table-cell;
    vertical-align: middle;
}

.leaflet-marker-icon,
.leaflet-marker-shadow {
  -webkit-transition: margin 0.2s;
     -moz-transition: margin 0.2s;
       -o-transition: margin 0.2s;
          transition: margin 0.2s;
}
</style>
<div class="form">
    <label>Operating Radius: </label>
    <select name="radius">
        <option value="5">5 Miles</option>
        <option value="10">10 Miles</option>
        <option value="25">25 Miles</option>
    </select>
</div>
<div id="map">
    <div class="map-overlay"></div>
    <div class="map-container"></div>        
</div>
<?php

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
// Animated marker via http://bl.ocks.org/4284949
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
    
    this.map = L.map(this.$map[0]).setView([lat, lng], 13);
    
    var osmUrl='//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
    var mapTiles = new L.TileLayer(osmUrl, {
        attribution: 'Map data &copy; '
        + '<a href="http://openstreetmap.org">OpenStreetMap</a> contributors, '
        + '<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
        maxZoom: 18
    });
    
    this.map.addLayer(mapTiles);
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
    
    this.map.setView([this.lat, this.lng], 16);
    this.setCircle([this.lat, this.lng], this.milesToMeters(5));
};

Map.prototype.getCurrentLocation = function(success, error) {
    
    var self = this;
    
    var onSuccess = function(lat, lng) {
        success(new L.LatLng(lat, lng));
    };
    
    // get location via geoplugin.net. 
    // Typically faster than browser's geolocation, but less accurate.
    var geoplugin = function() {
        jQuery.getScript('http://www.geoplugin.net/javascript.gp', function() {
            onSuccess(geoplugin_latitude(), geoplugin_longitude());
        });
    };
    
    // get location via browser's geolocation. 
    // Typically slower than geoplugin.net, but more accurate.
    var navGeoLoc = function() {
        navigator.geolocation.getCurrentPosition(function(position) {
            success(new L.LatLng(position.coords.latitude, position.coords.longitude));
        }, function(positionError) {
            geoplugin();
            //error(positionError.message);
        });
    };
    
    if(navigator.geolocation) {
        navGeoLoc();
    }
    else {
        geoplugin();
    }
};

// Overlay message methods

Map.prototype.dismissMessage = function() {
    this.$el.removeClass('show-message');
    this.$overlay.html('');       
};

Map.prototype.showMessage = function(html) {
    this.$overlay.html('<div class="center"><div>' + html + '</div></div>');
    this.$el.addClass('show-message');
};

// Conversion Helpers

Map.prototype.milesToMeters = function(miles) {
    return miles * 1069;
};

jQuery(function($) {
  
    // clear than temporary background image
    $('.map-container').css('background', 'transparent');

    var map = new Map('#map', 51.505, -0.09);  
    
    map.showMessage('<p><span>Acquiring Current Location.</span><br /><br />'
                    + '<span>Please ensure the app has permission to access your location.</span></p>');
    
    map.getCurrentLocation(function(latLng) {
        map.centerOnLocation(latLng.lat, latLng.lng);
        map.dismissMessage();
    }, function(errorMessage) {
        map.showMessage('<p><span>Location Error:</span><br /><br />'
                    + '<span>' + errorMessage + '</span></p>');
    });  

    var s = $('select').on('change', function(e) {
        var value = $(this).val();
        var meters = map.milesToMeters(value);
        map.setCircle([map.lat, map.lng], meters);
    });    
        
});




var optsMap = {
	zoom: {{ ($geo['latitude'] == 0) ? 1 : 16; }},
	center: L.latLng([ {{ $geo['latitude'] }}, {{ $geo['longitude'] }} ]),
	zoomControl: false,
	attributionControl: false
};

$('#location').leafletLocationPicker({
	locationFormat: '{lat},{lng}',
	width: 420,
	height: 280,
	position: 'bottomleft',
	cursorSize: '20px',
	map: optsMap
});

$('#generate_uuid').on('click', function() {
	$('#uuid').val( guid() );
});

function formSubmittedSuccess(r)
{
    if(r.result == 'error')
    {
        return;
    }

    // Increment Geofence count
    var count = parseInt($('#count_geofences').text());
    $('#count_geofences').text(count+1);

	// Open geofence overview
	document.location = '#/geofences';
}
</script>
@stop