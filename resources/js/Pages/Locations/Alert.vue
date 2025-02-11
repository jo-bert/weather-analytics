<template>
    <Head title="Create and Manage Alert" />

    <div
        class="relative min-h-screen bg-opacity-90 bg-gradient-to-br from-sky-400 to-sky-200 p-4 sm:p-6 md:p-8"
    >
        <div
            class="relative mx-auto max-w-5xl overflow-hidden rounded-lg bg-white shadow-2xl"
        >
            <div class="p-6 sm:p-8 md:p-10">
                <h1
                    class="mb-8 text-center text-4xl font-extrabold text-blue-800"
                >
                    Weather Alert Creator
                </h1>

                <form
                    @submit="onSubmit"
                    class="mb-10 space-y-6 rounded-xl bg-blue-50 p-6 shadow-inner"
                >
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <label
                                class="block text-sm font-medium text-gray-700"
                                for="parameter"
                            >
                                Weather Parameter
                            </label>
                            <select
                                v-model="weatherParam"
                                v-bind="weatherParamProps"
                                id="parameter"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition duration-150 ease-in-out focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="" key=""></option>
                                <option
                                    v-for="param in weatherParameters"
                                    :key="param.value"
                                    :value="param.value"
                                >
                                    {{ param.label }}
                                </option>
                            </select>
                            <span class="text-red-500">{{
                                errors.parameter
                            }}</span>
                        </div>

                        <div class="space-y-2">
                            <label
                                class="block text-sm font-medium text-gray-700"
                                for="condition"
                            >
                                Condition
                            </label>
                            <select
                                v-model="condition"
                                v-bind="conditionProps"
                                id="condition"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition duration-150 ease-in-out focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="" key=""></option>
                                <option value="equal">Equal to</option>
                                <option value="less">Less than</option>
                                <option value="more">More than</option>
                                <option value="between">Between</option>
                            </select>
                            <span class="text-red-500">{{
                                errors.condition
                            }}</span>
                        </div>

                        <div v-if="condition !== 'between'" class="space-y-2">
                            <label
                                class="block text-sm font-medium text-gray-700"
                                for="value"
                            >
                                Threshold Value
                            </label>
                            <input
                                v-model="thresholdValue"
                                v-bind="thresholdValueProps"
                                type="number"
                                id="value"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition duration-150 ease-in-out focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                step="0.1"
                            />
                            <span class="text-red-500">{{ errors.value }}</span>
                        </div>

                        <div
                            v-if="condition === 'between'"
                            class="grid grid-cols-2 gap-4"
                        >
                            <div class="space-y-2">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    for="minValue"
                                >
                                    Min Value
                                </label>
                                <input
                                    v-model="minThresholdValue"
                                    v-bind="minThresholdValueProps"
                                    type="number"
                                    id="minValue"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition duration-150 ease-in-out focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    step="0.1"
                                />
                                <span class="text-red-500">{{
                                    errors.minValue
                                }}</span>
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    for="maxValue"
                                >
                                    Max Value
                                </label>
                                <input
                                    v-model="maxThresholdValue"
                                    v-bind="maxThresholdValueProps"
                                    type="number"
                                    id="maxValue"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition duration-150 ease-in-out focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    step="0.1"
                                />
                                <span class="text-red-500">{{
                                    errors.maxValue
                                }}</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label
                                class="block text-sm font-medium text-gray-700"
                                for="location"
                            >
                                Location
                            </label>
                            <select
                                v-model="location"
                                v-bind="locationProps"
                                id="location"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition duration-150 ease-in-out focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="" key=""></option>
                                <option
                                    v-for="location in props.locations"
                                    :key="location.id"
                                    :value="location.id"
                                >
                                    {{ location.name }}, {{ location.country }}
                                </option>
                            </select>
                            <span class="text-red-500">{{
                                errors.location
                            }}</span>
                        </div>

                        <div class="space-y-2">
                            <label
                                class="block text-sm font-medium text-gray-700"
                                for="expiry"
                            >
                                Expiry Date and Time
                            </label>
                            <input
                                v-model="expiry"
                                v-bind="expiryProps"
                                type="datetime-local"
                                id="expiry"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition duration-150 ease-in-out focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            <span class="text-red-500">{{
                                errors.expiry
                            }}</span>
                        </div>
                        <div
                            class="flex items-center space-x-2 space-y-2"
                            v-if="id"
                        >
                            <span class="text-sm font-medium text-gray-700"
                                >Paused</span
                            >
                            <label class="switch">
                                <input
                                    type="checkbox"
                                    v-model="paused"
                                    v-bind="pausedProps"
                                />
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        :class="{ 'bg-green-500': !id, 'bg-amber-500': id }"
                        class="w-full rounded-lg bg-opacity-100 px-4 py-3 font-bold text-white"
                    >
                        <template v-if="isSubmitting">
                            <span class="loading loading-spinner loading-md" />
                        </template>
                        <template v-else>
                            {{ id ? 'Edit Alert' : 'Create Alert' }}
                        </template>
                    </button>
                </form>

                <div class="space-y-10">
                    <div>
                        <div class="mb-6 flex items-center justify-between">
                            <h2 class="text-3xl font-bold text-blue-800">
                                Ongoing Alerts
                            </h2>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium text-gray-700"
                                    >Pause all alerts</span
                                >
                                <label class="switch">
                                    <input
                                        type="checkbox"
                                        v-model="pauseAllAlerts"
                                    />
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <ul class="space-y-4">
                            <li
                                v-for="alert in ongoingAlerts"
                                :key="alert.id"
                                class="transform rounded-xl border-l-4 p-6 shadow-md transition duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg"
                                :class="{
                                    'border-green-500': !alert.paused,
                                    'border-yellow-500': alert.paused,
                                }"
                            >
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p
                                            class="mb-2 text-lg font-semibold text-blue-800"
                                        >
                                            {{
                                                getParameterLabel(
                                                    alert.parameter,
                                                )
                                            }}
                                        </p>
                                        <p class="mb-1 text-gray-600">
                                            {{
                                                alert.condition === 'between'
                                                    ? `Between ${alert.minValue} and ${alert.maxValue}`
                                                    : `${capitalizeFirstLetter(alert.condition)} ${alert.value}`
                                            }}
                                        </p>
                                        <p class="mb-1 text-gray-600">
                                            Location: {{ alert.full_location }}
                                        </p>
                                        <p class="mb-1 text-gray-600">
                                            Expires:
                                            {{ formatDateTime(alert.expiry) }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Created:
                                            {{
                                                formatDateTime(alert.created_at)
                                            }}
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <button
                                            @click="triggerAlert(alert)"
                                            class="w-full rounded-md bg-green-500 px-4 py-2 text-white transition duration-150 ease-in-out hover:bg-green-600"
                                            :disabled="
                                                alert.paused || pauseAllAlerts
                                            "
                                        >
                                            Trigger
                                        </button>
                                        <button
                                            @click="editAlert(alert.id)"
                                            class="w-full rounded-md bg-yellow-500 px-4 py-2 text-white transition duration-150 ease-in-out hover:bg-yellow-600"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="deleteAlert(alert.id)"
                                            class="w-full rounded-md bg-red-500 px-4 py-2 text-white transition duration-150 ease-in-out hover:bg-red-600"
                                        >
                                            Delete
                                        </button>
                                        <label
                                            class="flex cursor-pointer items-center justify-center space-x-2"
                                        >
                                            <span
                                                class="text-sm font-medium text-gray-700"
                                                >Pause</span
                                            >
                                            <div class="switch">
                                                <input
                                                    type="checkbox"
                                                    v-model="alert.paused"
                                                    :disabled="pauseAllAlerts"
                                                />
                                                <span
                                                    class="slider round"
                                                ></span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h2 class="mb-6 text-3xl font-bold text-blue-800">
                            Past Alerts
                        </h2>
                        <ul class="space-y-4">
                            <li
                                v-for="(alert, index) in pastAlerts"
                                :key="index"
                                class="rounded-xl border-l-4 border-gray-400 bg-gray-100 p-6 shadow-md"
                            >
                                <div>
                                    <p
                                        class="mb-2 text-lg font-semibold text-gray-800"
                                    >
                                        {{ getParameterLabel(alert.parameter) }}
                                    </p>
                                    <p class="mb-1 text-gray-600">
                                        {{
                                            alert.condition === 'between'
                                                ? `Between ${alert.minValue} and ${alert.maxValue}`
                                                : `${capitalizeFirstLetter(alert.condition)} ${alert.value}`
                                        }}
                                    </p>
                                    <p class="mb-1 text-gray-600">
                                        Location: {{ alert.full_location }}
                                    </p>
                                    <p class="mb-1 text-gray-600">
                                        Expired:
                                        {{ formatDateTime(alert.expiry) }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Created:
                                        {{ formatDateTime(alert.created_at) }}
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Alert, Location } from '@/types';
import { Head } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/yup';
import VsToast from '@vuesimple/vs-toast';
import axios from 'axios';
import { useForm } from 'vee-validate';
import { computed, ref } from 'vue';
import * as yup from 'yup';

const props = defineProps<{
    locations: Location[];
    alerts: Alert[];
    newAlert: Object;
}>();

const { errors, defineField, handleSubmit, isSubmitting, values, setValues } =
    useForm({
        initialValues: {
            id: 0,
            parameter: '',
            condition: '',
            value: 0,
            minValue: 0,
            maxValue: 100,
            location: '',
            expiry: '',
            paused: false,
        },
        validationSchema: toTypedSchema(
            yup.object({
                id: yup.number(),
                parameter: yup.string().required(),
                condition: yup.string().required(),
                value: yup
                    .number()
                    .test('invalidAmount', `Minimum 0`, function (value = 0) {
                        if (values.condition !== 'between' && value <= 0) {
                            return false;
                        }
                        return true;
                    }),
                minValue: yup
                    .number()
                    .test('invalidAmount', `Minimum 0`, function (value = 0) {
                        if (
                            values.condition === 'between' &&
                            !values.parameter?.includes('temp') &&
                            value <= 0
                        ) {
                            return false;
                        }
                        return true;
                    }),
                maxValue: yup
                    .number()
                    .test('invalidAmount', `Minimum 0`, function (value = 0) {
                        if (
                            values.condition === 'between' &&
                            !values.parameter?.includes('temp') &&
                            value <= 0
                        ) {
                            return false;
                        }
                        return true;
                    }),
                location: yup.string().required(),
                expiry: yup
                    .string()
                    .required()
                    .test(
                        'invalidDate',
                        'Expiry Date and Time must be future',
                        function (value) {
                            return new Date(value) > new Date();
                        },
                    ),
                paused: yup.boolean().required(),
            }),
        ),
    });

const [id, idProps] = defineField('id');
const [weatherParam, weatherParamProps] = defineField('parameter');
const [condition, conditionProps] = defineField('condition');
const [thresholdValue, thresholdValueProps] = defineField('value');
const [minThresholdValue, minThresholdValueProps] = defineField('minValue');
const [maxThresholdValue, maxThresholdValueProps] = defineField('maxValue');
const [location, locationProps] = defineField('location');
const [expiry, expiryProps] = defineField('expiry');
const [paused, pausedProps] = defineField('paused');
function convertToExpiryValue(dateTimeString: string) {
    const date = new Date(dateTimeString);
    const timezoneOffset = date.getTimezoneOffset();
    const timezoneOffsetHours = Math.abs(timezoneOffset / 60);
    const timezoneSign = timezoneOffset > 0 ? '-' : '+';
    const timezoneOffsetString =
        timezoneSign + String(timezoneOffsetHours).padStart(2, '0');
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = '00';

    const expiryValue = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}${timezoneOffsetString}`;

    return expiryValue;
}

type FormValues = typeof values;
async function onSuccess(values: FormValues) {
    const {
        expiry,
        location,
        maxValue,
        minValue,
        value,
        condition,
        parameter,
        paused,
    } = values;
    if (values.id) {
        axios
            .put('/alerts/' + values.id, {
                location_id: location,
                parameter,
                condition,
                value,
                minValue,
                maxValue,
                expiry: convertToExpiryValue(expiry as string),
                paused,
            })
            .then(function (response) {
                VsToast.show({
                    title: 'Modified the alert',
                    variant: 'success',
                    position: 'top-right',
                });
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(function (error) {
                console.log(error);
                const errors: string[] = [];
                Object.keys(error.response.data.errors).forEach((errorKey) => {
                    error.response.data.errors[errorKey].forEach(
                        (message: string) => errors.push(message),
                    );
                });
                VsToast.show({
                    title: 'Failed to add alert',
                    message: errors.length > 1 ? errors.join(', ') : errors[0],
                    variant: 'error',
                    position: 'top-right',
                });
            });
    } else
        axios
            .post('/alerts', {
                location_id: location,
                parameter,
                condition,
                value,
                minValue,
                maxValue,
                expiry: convertToExpiryValue(expiry as string),
                paused,
            })
            .then(function (response) {
                VsToast.show({
                    title: 'Added the alert',
                    variant: 'success',
                    position: 'top-right',
                });
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(function (error) {
                const errors: string[] = [];
                Object.keys(error.response.data.errors).forEach((errorKey) => {
                    error.response.data.errors[errorKey].forEach(
                        (message: string) => errors.push(message),
                    );
                });
                VsToast.show({
                    title: 'Failed to add alert',
                    message: errors.length > 1 ? errors.join(', ') : errors[0],
                    variant: 'error',
                    position: 'top-right',
                });
            });
}

function onInvalidSubmit({
    values,
    errors,
    results,
}: {
    values: FormValues;
    errors: any;
    results: any;
}) {
    console.log(values); // current form values
    console.error(errors); // a map of field names and their first error message
    console.log(results); // a detailed map of field names and their validation results
}
// This handles both valid and invalid submissions
const onSubmit = handleSubmit(onSuccess, onInvalidSubmit);
const weatherParameters = [
    { value: 'temp_c', label: 'Temperature (°C)' },
    { value: 'temp_f', label: 'Temperature (°F)' },
    { value: 'wind_mph', label: 'Wind Speed (mph)' },
    { value: 'wind_kph', label: 'Wind Speed (km/h)' },
    { value: 'wind_degree', label: 'Wind Degree' },
    { value: 'pressure_mb', label: 'Pressure (mb)' },
    { value: 'pressure_in', label: 'Pressure (in)' },
    { value: 'precip_mm', label: 'Precipitation (mm)' },
    { value: 'precip_in', label: 'Precipitation (in)' },
    { value: 'snow_cm', label: 'Snow (cm)' },
    { value: 'humidity', label: 'Humidity (%)' },
    { value: 'cloud', label: 'Cloud Cover (%)' },
    { value: 'feelslike_c', label: 'Feels Like (°C)' },
    { value: 'feelslike_f', label: 'Feels Like (°F)' },
    { value: 'windchill_c', label: 'Wind Chill (°C)' },
    { value: 'windchill_f', label: 'Wind Chill (°F)' },
    { value: 'heatindex_c', label: 'Heat Index (°C)' },
    { value: 'heatindex_f', label: 'Heat Index (°F)' },
    { value: 'dewpoint_c', label: 'Dew Point (°C)' },
    { value: 'dewpoint_f', label: 'Dew Point (°F)' },
    { value: 'will_it_rain', label: 'Will it Rain' },
    { value: 'chance_of_rain', label: 'Chance of Rain (%)' },
    { value: 'will_it_snow', label: 'Will it Snow' },
    { value: 'chance_of_snow', label: 'Chance of Snow (%)' },
    { value: 'vis_km', label: 'Visibility (km)' },
    { value: 'vis_miles', label: 'Visibility (miles)' },
    { value: 'gust_mph', label: 'Wind Gust (mph)' },
    { value: 'gust_kph', label: 'Wind Gust (km/h)' },
    { value: 'uv', label: 'UV Index' },
];

const pauseAllAlerts = ref(false);

const editAlert = (id: number) => {
    const selectedAlert = props.alerts.find((alert) => alert.id === id);
    if (selectedAlert) {
        const {
            id,
            expiry,
            full_location,
            maxValue,
            minValue,
            value,
            condition,
            parameter,
            paused,
        } = selectedAlert;
        setValues({
            id,
            expiry: expiry.slice(0, 16),
            location: props.locations.find(
                (location) => location.name === full_location.split(',')[0],
            )?.id,
            maxValue,
            minValue,
            value,
            condition,
            parameter,
            paused,
        });
    }
};

const deleteAlert = (id: number) => {
    axios
        .delete(`/alerts/${id}`)
        .then(function (response) {
            VsToast.show({
                title: 'Deleted the alert',
                variant: 'success',
                position: 'top-right',
            });
            setTimeout(() => window.location.reload(), 1000);
        })
        .catch(function (error) {
            VsToast.show({
                title: 'Failed to delete alert',
                message: error.response.data.errors.join(', '),
                variant: 'error',
                position: 'top-right',
            });
        });
};

const getParameterLabel = (paramValue: string) => {
    const param = weatherParameters.find((p) => p.value === paramValue);
    return param ? param.label : paramValue;
};

const formatDateTime = (date: string) => {
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const ongoingAlerts = computed(() => {
    const now = new Date();
    return props.alerts.filter(
        (alert) => new Date(alert.expiry) > now,
    ) as Alert[];
});

const pastAlerts = computed(() => {
    const now = new Date();
    return props.alerts.filter(
        (alert) => new Date(alert.expiry) <= now,
    ) as Alert[];
});

const triggerAlert = (alert: Alert) => {
    VsToast.show({
        title:
            alert.condition === 'between'
                ? `${alert.parameter} is ${alert.condition} ${alert.minValue}  and ${alert.maxValue}`
                : `${alert.parameter} is ${alert.condition} than ${alert.value}`,
        variant: 'info',
        position: 'top-right',
    });
};

const capitalizeFirstLetter = (string: string) => {
    return string.charAt(0).toUpperCase() + string.slice(1);
};
</script>

<style scoped>
.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.4s;
}

.slider:before {
    position: absolute;
    content: '';
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: 0.4s;
}

input:checked + .slider {
    background-color: #2196f3;
}

input:focus + .slider {
    box-shadow: 0 0 1px #2196f3;
}

input:checked + .slider:before {
    transform: translateX(26px);
}

.slider.round {
    border-radius: 34px;
}

.slider.round:before {
    border-radius: 50%;
}
</style>
