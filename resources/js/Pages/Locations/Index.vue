<script setup lang="ts">
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ModelListSelect } from 'vue-search-select';

const props = defineProps<{
  nearestLocation: string;
  locations: Object[];
  countries: string[];
  location: string;
  weather: Object[];
}>();
const form = useForm({
  location: '',
  country: '',
  lat: 0,
  long: 0,
});

const showError = ref(false);

const enterKey = () => {
  form.post(route('locations.submit'), {
    onSuccess: (response) => {
      form.country = response.props.country;
      form.location = response.props.location;
      console.log('Form submitted successfully:', response);
    },
    onError: (error) => {
      console.log('Form submission failed:', error);
    },
  });
};

const getUserCoordinate = () => {
  if ("geolocation" in navigator) {
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
        }, 2000);
      }
    );
  } else {
    showError.value = true;
    setTimeout(() => {
      showError.value = false;
    }, 2000);
  }
}

const filteredLocation = computed(() => {
  const result = props.locations.filter((location) => location.country === form.country);

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
  console.log("object");
  form.lat = 0;
  form.long = 0;
}
</script>

<template>
  <GuestLayout>

    <Head title="Weather Analytics" />
    <button class="btn btn-circle btn-outline" @click="getUserCoordinate">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
        class="size-6">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
      </svg>
    </button>
    <InputLabel for="country" value="Country" />
    <select class="select select-bordered w-full max-w-xs" v-model="form.country">
      <option v-for="country in countries" :key="country">{{ country }}</option>
    </select>
    <form @submit.prevent="enterKey">

      <InputLabel for="location" value="Location" />
      <ModelListSelect :list="filteredLocation" optionValue="name" optionText="name" id="id" v-model="form.location"
        placeholder="Select City" @searchchange=searchChange />

      <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
        Get Weather
      </PrimaryButton>

    </form>
    <p>{{ nearestLocation }}</p>
    <p> {{ weather }}</p>

    <!-- <div class="min-h-screen bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center p-4" v-if="weather">
    <div class="bg-white bg-opacity-20 backdrop-blur-lg rounded-xl shadow-lg p-6 w-full max-w-4xl">
      <h2 class="text-3xl font-bold text-white mb-6 text-center">11-Day Weather Forecast</h2>
      <div class="overflow-x-auto pb-4">
        <div class="flex space-x-4">
          <div
            v-for="(day, index) in weather"
            :key="index"
            @click="selectDay(index)"
            class="flex-shrink-0 w-24 bg-white bg-opacity-30 rounded-lg p-4 cursor-pointer transition-all duration-300 hover:bg-opacity-50"
            :class="{ 'ring-2 ring-yellow-400': selectedDay === index }"
          >
            <p class="text-sm font-semibold text-white text-center mb-2">{{ day.date }}</p>
            <div class="flex justify-center mb-2">
              <component :is="getWeatherIcon(day.weather)" class="w-10 h-10 text-white" />
            </div>
            <p class="text-lg font-bold text-white text-center">{{ day.temperature }}°C</p>
          </div>
        </div>
      </div>
      <div v-if="selectedDay !== null" class="mt-6 bg-white bg-opacity-30 rounded-lg p-4">
        <h3 class="text-xl font-semibold text-white mb-2">{{ weatherData[selectedDay].date }}</h3>
        <p class="text-white">{{ weatherData[selectedDay].description }}</p>
        <div class="mt-4 grid grid-cols-2 gap-4">
          <div class="flex items-center">
            <ThermometerIcon class="w-5 h-5 text-white mr-2" />
            <span class="text-white">High: {{ weatherData[selectedDay].high }}°C</span>
          </div>
          <div class="flex items-center">
            <DropletIcon class="w-5 h-5 text-white mr-2" />
            <span class="text-white">Humidity: {{ weatherData[selectedDay].humidity }}%</span>
          </div>
          <div class="flex items-center">
            <WindIcon class="w-5 h-5 text-white mr-2" />
            <span class="text-white">Wind: {{ weatherData[selectedDay].wind }} km/h</span>
          </div>
          <div class="flex items-center">
            <UmbrellaIcon class="w-5 h-5 text-white mr-2" />
            <span class="text-white">Precipitation: {{ weatherData[selectedDay].precipitation }}%</span>
          </div>
        </div>
      </div>
    </div>
  </div> -->
    <div class="toast toast-top toast-end">
      <div class="alert alert-info" :class="{ 'show': showError, 'hidden': !showError }">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>Error! Could not get the geolocation</span>
      </div>
    </div>
  </GuestLayout>
</template>
