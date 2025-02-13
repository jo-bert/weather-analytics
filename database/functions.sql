DROP FUNCTION IF EXISTS find_nearest_location (double precision, double precision);

CREATE
OR REPLACE FUNCTION public.find_nearest_location (input_lat double precision, input_lon double precision) RETURNS TABLE (id BIGINT, name VARCHAR(255), region VARCHAR(255), country VARCHAR(255), distance double precision) LANGUAGE plpgsql AS $function$
BEGIN
    RETURN QUERY
    SELECT l.id, l.name, l.region, l.country,
           6371 * acos(
               cos(radians(input_lat)) * cos(radians(lat)) *
               cos(radians(lon) - radians(input_lon)) +
               sin(radians(input_lat)) * sin(radians(lat))
           ) AS distance
    FROM locations l
    ORDER BY distance
    LIMIT 1; -- Add this line to return only the closest location
END;
$function$;

DROP FUNCTION IF EXISTS get_daily_stat (int, int, bigint);

CREATE OR REPLACE FUNCTION public.get_daily_stat(start_time integer, end_time integer, location bigint)
 RETURNS TABLE(maxtemp_c double precision, maxtemp_f double precision, mintemp_c double precision, mintemp_f double precision, avgtemp_c numeric, avgtemp_f numeric, maxwind_mph double precision, maxwind_kph double precision, totalprecip_mm numeric, totalprecip_in numeric, totalsnow_cm numeric, avgvis_km numeric, avgvis_miles numeric, avghumidity numeric, daily_will_it_rain integer, daily_chance_of_rain integer, daily_will_it_snow integer, daily_chance_of_snow integer, uv double precision, condition text, condition_icon text)
 LANGUAGE plpgsql
AS $function$
BEGIN
    RETURN QUERY
    SELECT
        MAX(temp_c) AS maxtemp_c,
        MAX(temp_f) AS maxtemp_f,
        MIN(temp_c) AS mintemp_c,
        MIN(temp_f) AS mintemp_f,
         ROUND(AVG(temp_c)::numeric, 2) AS avgtemp_c,
        ROUND(AVG(temp_f)::numeric,2) AS avgtemp_f,
        MAX(wind_mph) AS maxwind_mph,
        MAX(wind_kph) AS maxwind_kph,
        ROUND(SUM(precip_mm)::numeric, 2) AS totalprecip_mm,
        ROUND(SUM(precip_in)::numeric,2) AS totalprecip_in,
        ROUND(SUM(snow_cm)::numeric,2) AS totalsnow_cm,
        ROUND(AVG(vis_km)::numeric,2) AS avgvis_km,
        ROUND(AVG(vis_miles)::numeric,2) AS avgvis_miles,
        ROUND(AVG(humidity)::numeric,2) AS avghumidity,
        MAX(will_it_rain) AS daily_will_it_rain,
        MAX(chance_of_rain) AS daily_chance_of_rain,
        MAX(will_it_snow) AS daily_will_it_snow,
        MAX(chance_of_snow) AS daily_chance_of_snow,
        MAX(hf.uv) AS uv,
        subquery.condition_text AS condition,
        subquery.condition_icon AS condition_icon
    FROM hourly_forecasts hf
    INNER JOIN locations l ON hf.location_id = l.id
    LEFT JOIN (
        SELECT hf.condition_text, hf.condition_icon, COUNT(*) AS freq
        FROM hourly_forecasts hf
        WHERE time_epoch >= start_time AND time_epoch <= end_time AND location_id = location
        GROUP BY hf.condition_text, hf.condition_icon
        ORDER BY freq DESC
        LIMIT 1
    ) AS subquery ON true
    WHERE hf.time_epoch >= start_time AND hf.time_epoch <= end_time AND hf.location_id = location   GROUP BY subquery.condition_text, subquery.condition_icon
    LIMIT 1
    ;
END;
$function$;
