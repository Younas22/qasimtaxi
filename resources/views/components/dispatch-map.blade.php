@props(['id' => 'dispatch-map-canvas'])

@php
    $googleMapsKey = config('services.google_maps.key');
@endphp

<div id="{{ $id }}" {{ $attributes->merge(['class' => 'w-full rounded-xl bg-luxury-graphite']) }}></div>

@once
    @if ($googleMapsKey)
        <script async src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsKey }}&callback=__initDispatchMaps"></script>
    @endif

    <script>
        window.__dispatchMapsReady = false;
        window.__dispatchMapsPending = [];
        window.__dispatchMapInstances = {};

        // A dark basemap that complements the luxury charcoal/gold theme —
        // Google's default map is bright white otherwise, which looks out
        // of place inside an all-dark panel. Markers/directions render
        // exactly the same regardless of this style array.
        window.__dispatchMapDarkStyle = [
            { elementType: 'geometry', stylers: [{ color: '#1a1c20' }] },
            { elementType: 'labels.text.stroke', stylers: [{ color: '#1a1c20' }] },
            { elementType: 'labels.text.fill', stylers: [{ color: '#8b8d87' }] },
            { featureType: 'administrative', elementType: 'geometry', stylers: [{ color: '#3a3c40' }] },
            { featureType: 'poi', stylers: [{ visibility: 'off' }] },
            { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#2c2e33' }] },
            { featureType: 'road', elementType: 'geometry.stroke', stylers: [{ color: '#1a1c20' }] },
            { featureType: 'road.highway', elementType: 'geometry', stylers: [{ color: '#3a3226' }] },
            { featureType: 'road.highway', elementType: 'geometry.stroke', stylers: [{ color: '#1a1c20' }] },
            { featureType: 'road.highway', elementType: 'labels.text.fill', stylers: [{ color: '#c9a24b' }] },
            { featureType: 'transit', stylers: [{ visibility: 'off' }] },
            { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#0e1013' }] },
            { featureType: 'water', elementType: 'labels.text.fill', stylers: [{ color: '#5c5e58' }] },
        ];

        window.__initDispatchMaps = function () {
            window.__dispatchMapsReady = true;
            window.__dispatchMapsPending.forEach((call) => call());
            window.__dispatchMapsPending = [];
        };

        /**
         * Renders (or updates in place) a small dispatch map: office/driver
         * marker (whichever applies), a pickup marker, and — when
         * showRoute is true — a live-drawn route between the dispatch
         * point and pickup. Scenario 3 (driver busy) intentionally never
         * calls this — showing a route to a driver who isn't dispatching
         * yet would be misleading.
         *
         * @param {string} elementId
         * @param {object} data - office/driver/pickup lat-lng points, a showRoute flag, and routeFrom.
         */
        window.renderDispatchMap = function (elementId, data) {
            const run = () => {
                const el = document.getElementById(elementId);
                if (! el || ! data || ! data.pickup) return;

                let instance = window.__dispatchMapInstances[elementId];

                if (! instance) {
                    const map = new google.maps.Map(el, {
                        center: data.pickup,
                        zoom: 12,
                        disableDefaultUI: true,
                        zoomControl: true,
                        styles: window.__dispatchMapDarkStyle,
                    });
                    instance = { map, markers: [], renderer: null };
                    window.__dispatchMapInstances[elementId] = instance;
                }

                instance.markers.forEach((marker) => marker.setMap(null));
                instance.markers = [];

                if (instance.renderer) {
                    instance.renderer.setMap(null);
                    instance.renderer = null;
                }

                const bounds = new google.maps.LatLngBounds();

                if (data.office) {
                    instance.markers.push(new google.maps.Marker({
                        position: data.office,
                        map: instance.map,
                        icon: { path: google.maps.SymbolPath.CIRCLE, scale: 8, fillColor: '#22c55e', fillOpacity: 1, strokeColor: '#ffffff', strokeWeight: 2 },
                        title: 'Head Office',
                    }));
                    bounds.extend(data.office);
                }

                if (data.driver) {
                    instance.markers.push(new google.maps.Marker({
                        position: data.driver,
                        map: instance.map,
                        icon: { path: google.maps.SymbolPath.CIRCLE, scale: 8, fillColor: '#3b82f6', fillOpacity: 1, strokeColor: '#ffffff', strokeWeight: 2 },
                        title: 'Driver',
                    }));
                    bounds.extend(data.driver);
                }

                instance.markers.push(new google.maps.Marker({
                    position: data.pickup,
                    map: instance.map,
                    icon: { path: google.maps.SymbolPath.CIRCLE, scale: 8, fillColor: '#ef4444', fillOpacity: 1, strokeColor: '#ffffff', strokeWeight: 2 },
                    title: 'Pickup',
                }));
                bounds.extend(data.pickup);

                if (data.showRoute && data.routeFrom) {
                    const directionsService = new google.maps.DirectionsService();
                    instance.renderer = new google.maps.DirectionsRenderer({ map: instance.map, suppressMarkers: true, preserveViewport: true });

                    directionsService.route({
                        origin: data.routeFrom,
                        destination: data.pickup,
                        travelMode: google.maps.TravelMode.DRIVING,
                    }, (result, status) => {
                        if (status === 'OK') {
                            instance.renderer.setDirections(result);
                        }
                    });
                }

                if (! bounds.isEmpty()) {
                    instance.map.fitBounds(bounds, 48);
                }
            };

            if (window.__dispatchMapsReady && window.google) {
                run();
            } else {
                window.__dispatchMapsPending.push(run);
            }
        };
    </script>
@endonce
