import { Config } from 'ziggy-js';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    ziggy: Config & { location: string };
};

export interface CurrentWeather {
    location: Location;
    current: Current;
}

export interface Location {
    id: string;
    name: string;
    region: string;
    country: string;
    lat: number;
    lon: number;
    tz_id: string;
    localtime_epoch: number;
    localtime: string;
}

export interface Current {
    last_updated_epoch: number;
    last_updated: string;
    temp_c: number;
    temp_f: number;
    is_day: number;
    condition: Condition;
    wind_mph: number;
    wind_kph: number;
    wind_degree: number;
    wind_dir: string;
    pressure_mb: number;
    pressure_in: number;
    precip_mm: number;
    precip_in: number;
    humidity: number;
    cloud: number;
    feelslike_c: number;
    feelslike_f: number;
    windchill_c: number;
    windchill_f: number;
    heatindex_c: number;
    heatindex_f: number;
    dewpoint_c: number;
    dewpoint_f: number;
    vis_km: number;
    vis_miles: number;
    uv: number;
    gust_mph: number;
    gust_kph: number;
}

export interface Condition {
    text: string;
    icon: string;
    code: number;
}

export interface Country {
    id: number;
    name: string;
    region: string;
    country: string;
    lat: number;
    lon: number;
    tz_id: string;
    created_at: string;
    updated_at: string;
}

export interface Alert {
    id: number;
    parameter: string;
    condition: string;
    value: number;
    minValue: number;
    maxValue: number;
    expiry: string;
    paused: boolean;
    triggered: boolean;
    triggered_at: any;
    created_at: string;
    updated_at: string;
    full_location: string;
}

export interface DayWeather {
    maxtemp_c: number;
    maxtemp_f: number;
    mintemp_c: number;
    mintemp_f: number;
    avgtemp_c: number;
    avgtemp_f: number;
    maxwind_mph: number;
    maxwind_kph: number;
    totalprecip_mm: number;
    totalprecip_in: number;
    totalsnow_cm: number;
    avgvis_km: number;
    avgvis_miles: number;
    avghumidity: number;
    daily_will_it_rain: number;
    daily_chance_of_rain: number;
    daily_will_it_snow: number;
    daily_chance_of_snow: number;
    condition: any;
    uv: number;
    condition_text: string;
    condition_icon: string;
    condition_code: number;
    date: string;
}

export interface TodayForecast {
    time: string;
    temp_c: number;
    temp_f: number;
    precip_mm: number;
    precip_in: number;
}

declare module '@vuesimple/vs-toast';
