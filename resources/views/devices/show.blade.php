@extends('dashboard.layout')
@section('title','Device | Location')
@section('content')

@if(auth()->user()->role == 1 && $device->status == 0)
    <div class="pcoded-inner-content">
        <!-- Primary-color Breadcrumb card start -->
        <div class="card borderless-card">
            <div class="card-block primary-breadcrumb">
                <div class="breadcrumb-header">
                    <h5>Hello! {{auth()->user()->name}}, Your Device, registered as <u><b>{{$device->name}}</b></u> has been Temporarily suspended</h5>
                    <span>Try contacting your system administrators for reinstatement to access our services</span>
                </div>
            </div>
        </div>
        <!-- Primary-color Breadcrumb card end -->
    </div>
@else
    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page body start --> 
                <div class="page-body">
                    <div class="row">
                        <div class="col-lg-12 col-xl-12">
                            <!-- Basic map start -->
                            <div class="card" style="height: 520px">
                                <div class="card-header">
                                    <h5>Current Location (Map View) for {{ $device->name}} as of {{ $timeNow }}</h5>
                                    <button class="btn waves-effect waves-light btn-primary btn-sm" onclick="myGeoFence()">Geofence</button>
                                    @if(($geofence) != null)
                                        <button class="btn waves-effect waves-light btn-danger btn-sm" onclick="deleteGeoFence()"><i class="fas fa-trash"></i></button>
                                    @endif
                                    <a class="btn waves-effect waves-light btn-success btn-sm" href="{{route('trip.history',$device->id)}}">
                                        Trip Histroy
                                    </a>
                                </div>
                                <div class="card-block">
                                    <div id="map" class="set-map"></div>
                                </div>
                            </div>
                            <!-- Basic map end -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 col-xl-12">
                            <!-- Basic map start -->
                            <div class="card" style="height: 520px">
                                <div class="card-header">
                                    <h5>Current Location (Street View)</h5>
                                    <span>for {{ $device->name}} as of {{ $timeNow }}</span>
                                </div>
                                <div class="card-block">
                                    <div id="map2" class="set-map"></div>
                                </div>
                            </div>
                            <!-- Basic map end -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- Page body end -->
        </div>
    </div>
@endif

@endsection
@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css"
integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI="
crossorigin=""/>
@endsection
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"
integrity="sha256-WBkoXOwTeyKclOHuWtc+i2uENFpDZ9YPdf5Hf+D7ewM="
crossorigin=""></script>
<!-- leaflet draw Plugin -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/0.4.2/leaflet.draw.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/0.4.2/leaflet.draw.js"></script>
<!-- Turf.js Libraries -->
<script src='https://unpkg.com/@turf/turf@6/turf.min.js'></script>

<script>

        var map;
        map = L.map('map');
        var map2;
        map2 = L.map('map2');

        //Map view continues
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);
        //Google street markers
        var googleStreets = L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',{
            maxZoom: 20,
            subdomains:['mt0','mt1','mt2','mt3']
        });
        googleStreets.addTo(map);

        //for street view
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map2);



        //google street view
        var googleStreets = L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',{
            maxZoom: 20,
            subdomains:['mt0','mt1','mt2','mt3']
        });
        googleStreets.addTo(map2);

        //google satellite
        var googleSat = L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}',{
            maxZoom: 20,
            subdomains:['mt0','mt1','mt2','mt3']
        });
        googleSat.addTo(map2);


        //global coordinates for the user's current position
        var lat = null;
        var lng = null;
        navigator.geolocation.getCurrentPosition(function(position) {
            // Get latitude and longitude values from position object
            lat = position.coords.latitude;
            lng = position.coords.longitude;
        }, function(error) {
            // Handle any errors that occur while getting the position
            console.error("Error getting position:", error);
        });

        // Define the marker outside of the $.getJSON callback function so we can update it later
        var marker = L.marker([0, 0]).addTo(map);
        var mark = L.marker([0, 0]).addTo(map2);
        //marker fot the parent
        var marker_parent = L.marker([0, 0]).addTo(map);
        var mark_parent = L.marker([0, 0]).addTo(map2);

        var counter = 0;
        var notify = 0;


        // Retrieve the updated coordinates from the DB every 5 second
        setInterval(function() {
            $.getJSON('/marker', function(data) {
                var coordinates = data.currentCoordinate;    
                var filteredCoordinates = coordinates.filter(obj => obj.device_id === {{$device->id}});
                if (filteredCoordinates.length > 0) {
                    latitude = filteredCoordinates[0].latitude;
                    longitude = filteredCoordinates[0].longitude;

                    // Update the marker's position for the device
                    marker.setLatLng([latitude, longitude]);
                    mark.setLatLng([latitude, longitude]);
                    console.log(lat,lng)

                    //Icons for the parent's location
                    marker_parent.setLatLng([lat, lng]);
                    mark_parent.setLatLng([lat, lng]);

                    if(lat !== null)
                    {
                        // create a red polyline from an array of LatLng points
                        var latlngs = [
                            [lat, lng],
                            [latitude, longitude]
                        ];
                        L.polyline(latlngs, {color: 'red'}).addTo(map);
                        L.polyline(latlngs, {color: 'red'}).addTo(map2);
                        //map.fitBounds(polyline.getBounds());
                    }
                }
                //Map view
                marker.on('click', mapClick);
                var pop = L.popup();

                function mapClick(e) {
                    pop
                        .setLatLng(e.latlng)
                        .setContent("The current location of " +filteredCoordinates[0].coordinates.name+ " is " + e.latlng.toString())
                        .openOn(map);
                }

                //Street view
                mark.on('click', streetClick);
                var popup = L.popup();
                function streetClick(e) {
                    popup
                        .setLatLng(e.latlng)
                        .setContent("The current location of " +filteredCoordinates[0].coordinates.name+ " is " + e.latlng.toString())
                        .openOn(map2);
                }

                //increment counter and notify
                counter++;
                notify++;

                //GeoFence Violation Check by turf.js
                var presentData = {!! json_encode($geofence) !!}
                if(presentData !== null) {
                    var myType = {!! $geofence !!}
                    type = myType.geometry.type
                    // check if the point is within the polygon using turf.js library
                    coordinates = turf.point([longitude, latitude])
                    jsonData = {!!$geofence!!}
                    poly = jsonData.geometry.coordinates[0]
                    //console.log(poly)
                    polygon = turf.polygon([poly])
                    isInside = turf.booleanPointInPolygon(coordinates, polygon);
                    console.log(isInside)
                    if(!isInside)
                    {
                        //delay notifications and SMS by 10 minutes(120 loops) to avoid overfloading email, every 5 seconds
                        if(notify >= 2)
                        {
                            return;
                        }else{
                            notifications(filteredCoordinates);
                        }
                    }else{
                        console.log("Device Still in Position")
                    }
                }else{
                    console.log("No geofence set")
                }
                
                if(counter <= 2)
                {
                    // Update the map's view to center on the marker
                    map.setView([latitude, longitude], 13);
                    map2.setView([latitude, longitude], 13);
                }else {
                    return;
                }

            });
        }, 5000)

        function notifications(filteredCoordinates) {
            console.log("Device Out of Designated Area")
            $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            //Email notifications
            $.ajax({
                url: "{{ route('geofence.alert') }}",
                type: "POST",
                data: {
                    user_id: {{auth()->user()->id}},
                    device_id: filteredCoordinates[0].coordinates.id
                },
                success: function(response) {
                    console.log(response);
                },
                error: function(xhr, status, error) {
                    console.log(error);
                }
            });

            $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            //SMS notifications ajax
            $.ajax({
                url: "{{ route('send.sms') }}",
                type: "POST",
                data: {
                    user_id: {{auth()->user()->id}},
                    device_id: filteredCoordinates[0].coordinates.id
                },
                success: function(response) {
                    console.log(response);
                },
                error: function(xhr, status, error) {
                    console.log(error);
                }
            });
            setTimeout(notifications, 300000);
        }

        //leaflet.draw to help in Geo-fencing techniques
        var drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);
        
        var drawControl = new L.Control.Draw({
            position: "topright",
            edit: {
                featureGroup: drawnItems,
                remove: true
            },
            draw: {
                polygon: {

                    shapeOptions: {
                        color: 'purple'
                    },
                },
                polyline: false,
                rectangle: {
                    shapeOptions: {
                        color: 'green'
                    },
                },
                circle: false,
                marker: false,
            }
        });
        map.addControl(drawControl);

        map.on("draw:created",function(e){
            var type = e.layerType;
            var layer = e.layer;
            //console.log(layer.toGeoJSON);
            layer.bindPopup(JSON.stringify(layer.toGeoJSON()))
            var  feature = layer.toGeoJSON();
            // console.log(JSON.stringify(feature))

            //sets the default headers for all subsequent AJAX requests, including the CSRF token
            $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: "{{ route('geojson.store') }}",
                type: "POST",
                data: {
                    geojson: JSON.stringify(feature),
                    device_id: {{$device->id}}
                },
                dataType: 'json',
                success: function(response) {
                    console.log(response);
                },
                error: function(xhr, status, error) {
                    console.log(error);
                }
            });

            drawnItems.addLayer(layer);
            document.location.reload();
        });

        map.on("draw:edited",function(e){
            var type = e.layerType;
            var layers = e.layers;
            layers.eachLayer(function(layer){
                console.log(layer.toGeoJSON())
                var updatedGeoFence = layer.toGeoJSON();
                //Lets edit the geofence
                $.ajaxSetup({
                    headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: "{{ route('geojson.store') }}",
                    type: "POST",
                    data: {
                        geojson: JSON.stringify(updatedGeoFence),
                        device_id: {{$device->id}}
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log(response);
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });
            });
            
        });

    function myGeoFence() 
    {
        //displaying the geofences within the map
        if({!! json_encode($geofence) !!} !== null){
            var myGeo = {!! $geofence !!}
            L.geoJSON(myGeo).addTo(map);
        }else{
            alert("No geofence set")
        }    
    }    

    //delete geofences
    function deleteGeoFence()
    {
        //console.log({{$device->id}})
        var id = {{$device->id}}
        //sets the default headers for all subsequent AJAX requests, including the CSRF token
        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: "/delete/geofence/"+id,
            type: "DELETE",
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                console.log(response);
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
        location.reload();
    }

    
        

</script>

@endpush