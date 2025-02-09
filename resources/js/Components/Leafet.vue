<template>
    <div class="min-h-48 w-full lg:h-1/3">
        <LMap
            ref="map"
            :minZoom="10"
            :zoom="10"
            :maxZoom="10"
            :center="[currentWeather.location.lat, currentWeather.location.lon]"
            :options="{ zoomControl: false }"
        >
            <LTileLayer
                url="https://b.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png"
                layer-type="base"
                name="OpenStreetMap"
            ></LTileLayer>
            <LMarker
                :lat-lng="[
                    currentWeather.location.lat,
                    currentWeather.location.lon,
                ]"
            >
                <LTooltip permanent>
                    <div>
                        <h1>{{ currentWeather.location.name }}</h1>
                        <ul>
                            <li>
                                Temperature:
                                {{
                                    isMetric
                                        ? currentWeather.current.temp_c
                                        : currentWeather.current.temp_f
                                }}°{{ isMetric ? 'C' : 'F' }}
                            </li>
                            <li>
                                Wind Speed:
                                {{
                                    isMetric
                                        ? currentWeather.current.wind_kph
                                        : currentWeather.current.wind_mph
                                }}
                                {{ isMetric ? 'kph' : 'mph' }}
                            </li>
                            <li>
                                Humidity: {{ currentWeather.current.humidity }}%
                            </li>
                        </ul>
                    </div>
                </LTooltip>
            </LMarker>
        </LMap>
    </div>
</template>

<script setup lang="ts">
import { CurrentWeather } from '@/types';
import { LMap, LMarker, LTileLayer, LTooltip } from '@vue-leaflet/vue-leaflet';
const props = defineProps<{
    currentWeather: CurrentWeather;
    isMetric: boolean;
}>();
</script>

<style></style>
