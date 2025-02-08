<template>
    <div style="height: 600px; width: 800px">
        <LMap ref="map" :zoom="10" :center="[lat, long]" :max-zoom="15">
            <LTileLayer
                url="https://b.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png"
                layer-type="base"
                name="OpenStreetMap"
            ></LTileLayer>

            <LMarker :lat-lng="[lat, long]">
                <LTooltip v:content="tooltip" v-if="tooltip" />
            </LMarker>
        </LMap>
    </div>
</template>

<script setup lang="ts">
import { LMap, LMarker, LTileLayer, LTooltip } from '@vue-leaflet/vue-leaflet';
defineProps<{
    lat: number;
    long: number;
    tooltip: HTMLDivElement;
}>();
</script>

<style></style>
