<template>
    <div
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 p-4"
        @click="() => emit('close:modal')"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-8 shadow-lg">
            <h3 class="mb-6 text-2xl font-semibold">
                {{
                    new Date(selectedForecast.date).toLocaleDateString() ===
                    new Date().toLocaleDateString()
                        ? "Today's"
                        : selectedForecast.date
                }}
                Forecast
            </h3>

            <div class="mb-6 flex items-center justify-between">
                <img
                    :src="selectedForecast.condition_icon"
                    :alt="selectedForecast.condition_text"
                    class="h-24 w-24"
                />
                <div class="text-right">
                    <p class="text-4xl font-bold">
                        {{
                            isMetric
                                ? selectedForecast.avgtemp_c
                                : selectedForecast.avgtemp_f
                        }}°{{ isMetric ? 'C' : 'F' }}
                    </p>
                    <p class="text-xl text-gray-600">
                        {{ selectedForecast.condition_text }}
                    </p>
                </div>
            </div>

            <div @click.stop>
                <canvas ref="forecastCanvasRef"></canvas>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <h4 class="mb-2 font-semibold">Temperature</h4>
                    <p>
                        Max:
                        {{
                            isMetric
                                ? selectedForecast.maxtemp_c
                                : selectedForecast.maxtemp_f
                        }}°{{ isMetric ? 'C' : 'F' }}
                    </p>
                    <p>
                        Min:
                        {{
                            isMetric
                                ? selectedForecast.mintemp_c
                                : selectedForecast.mintemp_f
                        }}°{{ isMetric ? 'C' : 'F' }}
                    </p>
                    <p>
                        Avg:
                        {{
                            isMetric
                                ? selectedForecast.avgtemp_c
                                : selectedForecast.avgtemp_f
                        }}°{{ isMetric ? 'C' : 'F' }}
                    </p>
                </div>
                <div>
                    <h4 class="mb-2 font-semibold">Wind</h4>
                    <p>
                        Max:
                        {{
                            isMetric
                                ? selectedForecast.maxwind_kph
                                : selectedForecast.maxwind_mph
                        }}
                        {{ isMetric ? 'km/h' : 'mph' }}
                    </p>
                </div>
                <div>
                    <h4 class="mb-2 font-semibold">Precipitation</h4>
                    <p>
                        Total:
                        {{
                            isMetric
                                ? selectedForecast.totalprecip_mm
                                : selectedForecast.totalprecip_in
                        }}
                        {{ isMetric ? 'mm' : 'in' }}
                    </p>
                    <p>Snow: {{ selectedForecast.totalsnow_cm }} cm</p>
                </div>
                <div>
                    <h4 class="mb-2 font-semibold">Visibility</h4>
                    <p>
                        Avg:
                        {{
                            isMetric
                                ? selectedForecast.avgvis_km
                                : selectedForecast.avgvis_miles
                        }}
                        {{ isMetric ? 'km' : 'miles' }}
                    </p>
                </div>
                <div>
                    <h4 class="mb-2 font-semibold">Humidity</h4>
                    <p>Avg: {{ selectedForecast.avghumidity }}%</p>
                </div>
                <div>
                    <h4 class="mb-2 font-semibold">UV Index</h4>
                    <p>{{ selectedForecast.uv }}</p>
                </div>
            </div>

            <div class="mt-4">
                <h4 class="mb-2 font-semibold">Precipitation Chance</h4>
                <p>
                    Rain: {{ selectedForecast.daily_chance_of_rain }}% ({{
                        selectedForecast.daily_will_it_rain ? 'Yes' : 'No'
                    }})
                </p>
                <p>
                    Snow: {{ selectedForecast.daily_chance_of_snow }}% ({{
                        selectedForecast.daily_will_it_snow ? 'Yes' : 'No'
                    }})
                </p>
            </div>
            <p class="mb-6 text-lg text-gray-600">
                {{ selectedForecast.condition_text }}
            </p>
            <button
                @click="() => emit('close:modal')"
                class="w-full rounded-md bg-sky-500 p-3 text-lg text-white hover:bg-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-500"
            >
                Close
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Chart } from 'chart.js';
import { onMounted, ref, shallowRef } from 'vue';
import { DayWeather, TodayForecast } from '../types/index';
const forecastCanvasRef = ref<HTMLCanvasElement | null>(null);
const chartRef = shallowRef<Chart<'line' | 'bar', any, unknown> | null>(null);
const props = defineProps<{
    selectedForecast: DayWeather;
    isMetric: Boolean;
    location_id: Number;
}>();
const emit = defineEmits(['close:modal']);
onMounted(async () => {
    document.body.classList.add('overflow-hidden');
    const selectedDate = props.selectedForecast.date;
    const startDate = new Date(selectedDate).getTime() / 1000;
    const endDate =
        new Date(selectedDate).setDate(new Date(selectedDate).getDate() + 1) /
        1000;
    const url = `/hourly-forecasts/range?location_id=${props.location_id}&start_time=${startDate}&end_time=${endDate}`;
    try {
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const selectedHourlyForecast = await response.json();
        const lineDataset = selectedHourlyForecast.map((hour: TodayForecast) =>
            props.isMetric ? hour.temp_c : hour.temp_f,
        );
        const barDataset = selectedHourlyForecast.map((hour: TodayForecast) =>
            props.isMetric ? hour.precip_mm : hour.precip_in,
        );
        const labels = selectedHourlyForecast.map((hour: TodayForecast) => {
            //Revisit this whether we need to add + '+00:00' or not
            const date = new Date(hour.time);
            return date.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
            });
        });
        if (forecastCanvasRef.value)
            chartRef.value = new Chart(forecastCanvasRef.value, {
                data: {
                    datasets: [
                        {
                            type: 'bar',
                            label: 'Percipation',
                            data: barDataset,
                        },
                        {
                            type: 'line',
                            label: 'Temperature',
                            data: lineDataset,
                        },
                    ],
                    labels: labels,
                },
            });
    } catch (error) {
        console.error('Error fetching hourly forecasts:', error);
    }
});
</script>
