<!-- eslint-disable vue/no-side-effects-in-computed-properties -->
<script setup lang="ts">
import ForecastModal from '@/Components/ForecastModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Leafet from '@/Components/Leafet.vue';
import TextInput from '@/Components/TextInput.vue';
import Toggle from '@/Components/Toggle.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import VsToast from '@vuesimple/vs-toast';
import {
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    Colors,
    Legend,
    LinearScale,
    LineController,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';
import { MapPinIcon } from 'lucide-vue-next';
import {
    computed,
    nextTick,
    onMounted,
    onUnmounted,
    ref,
    shallowRef,
    toRaw,
    watch,
} from 'vue';
import { ModelListSelect } from 'vue-search-select';
import {
    Alert,
    CurrentWeather,
    DayWeather,
    Location,
    TodayForecast,
} from '../../types';

Chart.register(
    BarController,
    BarElement,
    CategoryScale,
    Colors,
    Legend,
    LinearScale,
    LineController,
    LineElement,
    PointElement,
    Tooltip,
);

const props = defineProps<{
    nearestLocation: string;
    locations: Location[];
    countries: string[];
    location: string;
    location_id: number;
    weather: DayWeather[];
    messageType: string;
    message: string;
    currentWeather: CurrentWeather;
    todayHourlyForecast: TodayForecast[];
    triggeredAlerts: Alert[];
}>();

const form = useForm({
    location: '',
    country: '',
    lat: 0,
    long: 0,
});

const selectedForecast = ref<DayWeather | null>(null);
const showError = ref(false);
const errorMessage = ref('Error! Could not get the geolocation');
const todayHourlyForecastRef = ref<TodayForecast[] | null>(null);
const enterKey = () => {
    if (chartRef.value) chartRef.value.destroy();
    form.post(route('locations.submit'), {
        preserveState: true,
        onSuccess: (response) => {
            form.country = response.props.country as string;
            form.location = response.props.location as string;
            todayHourlyForecastRef.value = response.props
                .todayHourlyForecast as TodayForecast[];

            (response.props.triggeredAlerts as Alert[]).forEach((alert) => {
                setTimeout(() => {
                    VsToast.show({
                        title:
                            alert.condition === 'between'
                                ? `${alert.parameter} is ${alert.condition} ${alert.minValue}  and ${alert.maxValue}`
                                : `${alert.parameter} is ${alert.condition} than ${alert.value}`,
                        variant: 'info',
                        position: 'top-right',
                    });
                }, 5000);
            });
            if (response.props.message !== undefined) {
                showError.value = true;
                errorMessage.value = response.props.message as string;
                setTimeout(() => {
                    showError.value = false;
                }, 2000);
            }
        },
        onError: (error) => {
            console.log('Form submission failed:', error);
        },
    });
};

const getUserCoordinate = () => {
    if ('geolocation' in navigator) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const latitude = position.coords.latitude;
                const longitude = position.coords.longitude;
                form.lat = latitude;
                form.long = longitude;
                // form.lat = '1.352083';
                // form.long = '103.819836';
                enterKey();
            },
            (error) => {
                showError.value = true;
                setTimeout(() => {
                    showError.value = false;
                }, 4000);
            },
        );
    } else {
        showError.value = true;
        setTimeout(() => {
            showError.value = false;
        }, 4000);
    }
};

const filteredLocation = computed(() => {
    const result = props.locations.filter(
        (location) => location.country === form.country,
    );

    if (result.length === 0) {
        return props.locations;
    } else {
        form.lat = 0;
        form.long = 0;
        form.location = result[0].name;
        return result;
    }
});

function searchChange() {
    form.lat = 0;
    form.long = 0;
}

const isMetric = ref(false);
const isCustomCity = ref(false);

const openModal = (day: DayWeather | null) => {
    if (day !== null) selectedForecast.value = day;
};

const closeModal = () => {
    selectedForecast.value = null;
    document.body.classList.remove('overflow-hidden');
};

// Polling mechanism
let pollingInterval = 0;

const startPolling = () => {
    pollingInterval = setInterval(() => {
        form.post(route('locations.submit'), {
            preserveState: true,
            onSuccess: (response) => {
                form.country = response.props.country as string;
                form.location = response.props.location as string;
                todayHourlyForecastRef.value = response.props
                    .todayHourlyForecast as TodayForecast[];
                if (response.props.message !== undefined) {
                    showError.value = true;
                    errorMessage.value = response.props.message as string;
                    setTimeout(() => {
                        showError.value = false;
                    }, 2000);
                }
            },
            onError: (error) => {
                console.log('Polling failed:', error);
            },
        });
    }, 10000);
};

onUnmounted(() => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});
const canvasRef = ref<HTMLCanvasElement | null>(null);
const chartRef = shallowRef<Chart<'line' | 'bar', any, unknown> | null>(null);
watch(isMetric, () => {
    if (chartRef.value && todayHourlyForecastRef.value) {
        const chart = toRaw(chartRef.value);
        Object.assign(
            chart.config.data.datasets[1].data,
            todayHourlyForecastRef.value.map((hour) =>
                isMetric.value ? hour.temp_c : hour.temp_f,
            ),
        );
        Object.assign(
            chart.config.data.datasets[0].data,
            todayHourlyForecastRef.value.map((hour) =>
                isMetric.value ? hour.precip_mm : hour.precip_in,
            ),
        );
        nextTick(() => {
            chart.update();
        });
    }
});

onMounted(() => {
    if (form.location) startPolling();
});

watch(todayHourlyForecastRef, () => {
    if (todayHourlyForecastRef.value) {
        const lineDataset = todayHourlyForecastRef.value.map(
            (hour: TodayForecast) =>
                isMetric.value ? hour.temp_c : hour.temp_f,
        );
        const barDataset = todayHourlyForecastRef.value.map(
            (hour: TodayForecast) =>
                isMetric.value ? hour.precip_mm : hour.precip_in,
        );
        const labels = todayHourlyForecastRef.value.map(
            (hour: TodayForecast) => {
                //Revisit this whether we need to add + '+00:00' or not
                const date = new Date(hour.time);
                return date.toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                });
            },
        );
        if (canvasRef.value)
            chartRef.value = new Chart(canvasRef.value, {
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
    }
});
</script>

<template>
    <!-- <GuestLayout> -->

    <Head title="Weather Analytics" />
    <div class="min-h-screen bg-gradient-to-br from-sky-400 to-sky-200 p-8">
        <div class="mx-auto max-w-[90rem]">
            <h1 class="mb-8 text-4xl font-bold text-white">Weather Forecast</h1>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Left Column: Location Input and Current Weather -->
                <div class="space-y-6 lg:col-span-1">
                    <div class="rounded-lg bg-white p-6 shadow-lg">
                        <form @submit.prevent="enterKey">
                            <Toggle
                                v-model="isCustomCity"
                                offText="Old City"
                                onText="New City"
                            />
                            <div v-if="!isCustomCity">
                                <div class="mb-4 flex items-center">
                                    <select
                                        class="flex-grow rounded-l-md border p-2 focus:outline-none focus:ring-2 focus:ring-sky-500"
                                        v-model="form.country"
                                    >
                                        <option value="">
                                            Select a country
                                        </option>
                                        <option
                                            v-for="country in countries"
                                            :value="country"
                                            :key="country"
                                        >
                                            {{ country }}
                                        </option>
                                    </select>
                                </div>
                                <div class="mb-4 flex items-center">
                                    <ModelListSelect
                                        :list="filteredLocation"
                                        optionValue="name"
                                        optionText="name"
                                        id="id"
                                        v-model="form.location"
                                        placeholder="Select a City"
                                        @searchchange="searchChange"
                                    />
                                </div>
                            </div>
                            <div v-else class="mb-4 w-full">
                                <InputLabel
                                    value="Insert a City/Region/Country"
                                />
                                <TextInput v-model="form.location" />
                            </div>
                            <button
                                :disabled="form.processing"
                                type="submit"
                                class="mb-2 flex w-full items-center justify-center rounded-md bg-sky-500 p-2 text-white hover:bg-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-500"
                            >
                                Submit
                            </button>
                        </form>
                        <button
                            :disabled="form.processing"
                            @click="getUserCoordinate"
                            class="mb-2 flex w-full items-center justify-center rounded-md bg-sky-500 p-2 text-white hover:bg-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-500"
                        >
                            <MapPinIcon class="mr-2 h-5 w-5" />
                            Use Current Location
                        </button>

                        <Toggle
                            v-if="currentWeather"
                            v-model="isMetric"
                            offText="Imperial"
                            onText="Metric"
                        />
                        <button
                            @click="() => router.visit('/alerts')"
                            class="flex w-full items-center justify-center rounded-md bg-green-500 p-2 text-white"
                        >
                            Go to Alerts
                        </button>
                    </div>

                    <!-- Current Weather -->
                    <div
                        v-if="currentWeather"
                        class="rounded-lg bg-white p-6 shadow-lg"
                    >
                        <h2 class="mb-4 text-2xl font-semibold">
                            Current Weather
                        </h2>
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-semibold">
                                    {{ location }}
                                </h3>
                                <p class="text-4xl font-bold">
                                    {{
                                        isMetric
                                            ? currentWeather.current.temp_c
                                            : currentWeather.current.temp_f
                                    }}°{{ isMetric ? 'C' : 'F' }}
                                </p>
                                <p class="text-gray-600">
                                    {{ currentWeather.current.condition.text }}
                                </p>
                            </div>
                            <img
                                :src="`https:${currentWeather.current.condition.icon}`"
                                class="h-24 w-24 text-sky-500"
                            />
                        </div>
                        <canvas ref="canvasRef"></canvas>

                        <button
                            class="mt-3 flex w-full items-center justify-center rounded-md bg-sky-500 p-2 text-white hover:bg-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-500"
                            @click="
                                () =>
                                    openModal(
                                        weather.find(
                                            (w) =>
                                                new Date(
                                                    w.date,
                                                ).toLocaleDateString() ===
                                                new Date().toLocaleDateString(),
                                        ) ??
                                            ({
                                                date: '',
                                                maxtemp_c: 0,
                                                maxtemp_f: 0,
                                                mintemp_c: 0,
                                                mintemp_f: 0,
                                                condition_icon: '',
                                                condition_text: '',
                                            } as DayWeather),
                                    )
                            "
                        >
                            See Today's Forecast
                        </button>
                    </div>
                </div>

                <!-- Middle Column: Map Placeholder -->
                <div class="lg:col-span-1">
                    <div
                        v-if="currentWeather"
                        class="h-full rounded-lg bg-white p-6 shadow-lg lg:h-1/2"
                    >
                        <h2 class="mb-4 text-2xl font-semibold">Weather Map</h2>
                        <div
                            class="flex h-[calc(100%-2rem)]"
                            :class="{ hidden: selectedForecast }"
                        >
                            <Leafet
                                :currentWeather="currentWeather"
                                :isMetric="isMetric"
                            />
                        </div>
                    </div>
                </div>

                <!-- Right Column: 11-day Forecast -->
                <div class="lg:col-span-1">
                    <div
                        v-if="weather"
                        class="rounded-lg bg-white p-6 shadow-lg"
                    >
                        <h2 class="mb-4 text-2xl font-semibold">Forecast</h2>
                        <div
                            class="max-h-[calc(100vh-16rem)] space-y-4 overflow-y-auto"
                        >
                            <div
                                v-for="(day, index) in weather"
                                :key="index"
                                @click="openModal(day)"
                                class="flex cursor-pointer items-center justify-between rounded-lg border-b px-4 py-3 last:border-b-0 hover:bg-gray-100"
                            >
                                <div class="flex items-center">
                                    <img
                                        :src="`https:${day.condition_icon}`"
                                        class="mr-4 h-10 w-10 text-sky-500"
                                    />
                                    <div>
                                        <p class="text-lg font-medium">
                                            {{
                                                new Date(
                                                    day.date,
                                                ).toLocaleDateString() ===
                                                new Date().toLocaleDateString()
                                                    ? 'Today'
                                                    : day.date
                                            }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            {{ day.condition_text }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold">
                                        {{
                                            isMetric
                                                ? day.maxtemp_c
                                                : day.maxtemp_f
                                        }}°
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        {{
                                            isMetric
                                                ? day.mintemp_c
                                                : day.mintemp_f
                                        }}°
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ForecastModal
            v-if="selectedForecast"
            :selectedForecast="selectedForecast"
            :isMetric="isMetric"
            :location_id="location_id"
            @close:modal="closeModal"
        />
    </div>
    <div class="toast toast-end toast-top">
        <div
            class="alert"
            :class="{
                show: showError,
                hidden: !showError,
                'alert-warning': props.messageType === 'warning',
                'alert-error': props.messageType === 'error',
            }"
        >
            <span>{{ errorMessage }}</span>
        </div>
    </div>
    <!-- </GuestLayout> -->
</template>
