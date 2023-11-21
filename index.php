<?php
/*
Plugin Name: World Travel Planner
Description: A simple WordPress plugin that lists countries of the world.
Version: 1.0
Author: Andrew Hosegood
*/

// Function to enqueue stylesheets
function ah65_world_travel_enqueue_styles() {
    // Enqueue your stylesheet
    wp_enqueue_style('ah65-world-travel-style', plugin_dir_url(__FILE__) . 'css/style.css');
}

// Hook into WordPress enqueue scripts action
add_action('wp_enqueue_scripts', 'ah65_world_travel_enqueue_styles');



// Function to fetch country data from the API
function fetch_country_data() {
    $api_url = 'https://restcountries.com/v3.1/all';
    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return array(); // Return an empty array if there's an error
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    return $data;
}



function flatten_nested_array($array) {
    return is_array($array) ? implode(', ', $array) : $array;
}




function world_travel_shortcode() {
   // Fetch country data from the API
   $countries = fetch_country_data();

    // Sort the countries array by the common name
    usort($countries, function ($a, $b) {
        return strcmp($a['name']['common'], $b['name']['common']);
    });

   $output = '<div class="ah65-world-travel">';
   $output .= '<select id="country-select">';
   $output .= '<option value="">-- Please Select --</option>';

   foreach ($countries as $country) {
       $output .= '<option value="' . esc_html($country['name']['common']) . '">' . esc_html($country['name']['common']) . '</option>';
   }

   $output .= '</select>';
   $output .= '<div id="card-stage" class="card-stage"></div>'; // Container for cards

   // display country map modal
//    $output .= '<div class="modal modal--active">';
//    $output .= '<div class="modal__inner">';
//    $output .= '<a class="modal__close">x</a>';
//    $output .= '<div class="modal__content">';
//    $output .= '<h3 class="modal__title">Modal Title</h3>';
//    $output .= '<p class="modal__description">Country Map goes in here</p>';
//    $output .= '</div>';
//    $output .= '</div>';
//    $output .= '</div>';

   // Pass country data directly to JavaScript
   $output .= '<script>';
   $output .= 'var worldTravelData = ' . json_encode($countries) . ';';
   $output .= '</script>';

   

   return $output;

    // if (!empty($countries)) {

        // $output .= '<select id="country-select">';
        // $output .= '<option>-- Please Select --</option>';

        //     foreach ($countries as $country) {
        //         $output .= '<option value="'.esc_html($country['name']['common']).'">' . esc_html($country['name']['common']) . '</option>';
        //     }

        // $output .= '</select>';

        // $output .= '<div id="card-stage" class="card-stage"></div>';

        //$output .= '<ul>';
        //foreach ($countries as $country) {

            //if (isset($country['name']['common'], $country['currencies'], $country['unMember'], $country['capital'], $country['latlng'], $country['population'], $country['continents'], $country['flags'])) {
                // $output .= '<li>';
                // $output .= '<strong>' . esc_html($country['name']['common']) . '</strong>: ';

                // $output .= 'UN Member: ' . ($country['unMember'] ? 'Yes' : 'No') . ', ';

                // $capital = flatten_nested_array($country['capital']);
                // $output .= 'Capital: ' . esc_html($capital) . ', ';

                // $output .= 'Population: ' . number_format($country['population']) . ', ';

                // $output .= 'Continents: ' . implode(', ', $country['continents']) . ', ';

                // $countryName = $country['name']['common'];
                // $flagUrl = $country['flags']['png'];


                // $output .= '<img style="width: 30px; height: auto;" src="'.$flagUrl.'" alt="Flag">';

                // $output .= 'Google Map: <a href="https://www.google.com/maps/search/' . urlencode($capital) . '" target="_blank">View on Google Maps</a>, ';


                // $currencies = array();
                // foreach ($country['currencies'] as $currency) {
                //     if (isset($currency['name'])) {
                //         $currencies[] = esc_html($currency['name']);
                //     }
                // }

                // $currencies_string = implode(', ', $currencies);
                // $output .= $currencies_string;
                // $output .= '</li>';
            //}
        //}
        //$output .= '</ul>';
    // } else {
    //     $output .= '<p>Failed to retrieve country data.</p>';
    // }

    // $output .= '</div>';

    // return $output;

    wp_localize_script('world-travel-script', 'worldTravelData', array(
        'countries' => $countries,
    ));
}

?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Event listener for country selection
    document.getElementById('country-select').addEventListener('change', function () {
        var selectedCountry = this.value;

        if (selectedCountry !== '') {
            // Fetch the selected country data
            var selectedCountryData = worldTravelData.find(function (country) {
                return country.name.common === selectedCountry;
            });

            // Create a new card
            createCard(selectedCountryData);
        }
    });

    // Function to create a new card and add it to the stage
    function createCard(countryData) {
        // Create a new card element
        var card = document.createElement('div');
        card.className = 'country-card';

        var unMemberStatus = countryData.unMember ? 'Yes' : 'No';

        var currency = countryData.currencies.XOF;

        var currencyInfo = currency ? 'Currency: ' + currency.name + ' (' + currency.symbol + ')' : 'Not available';

        // Populate the card content
        card.innerHTML = '<span class="country-card__title">' + countryData.name.common + '</span>' +
            '<div class="country-card__content"><div class="country-card__col-left"><p>Continent: '+ number_format(countryData.continents) +'</p><p>Population: ' + number_format(countryData.population) + '</p><p>Currency: ' + currencyInfo + '</p><p>UN Member: '+ unMemberStatus +'</p><a class="view-map" target="_blank" href="' + countryData.maps.googleMaps + '">View Map</a></div><div class="country-card__col-right">' +
            '<img src="' + countryData.flags.png + '" alt="Flag"></div></div>';

        // Add the card to the stage
        document.getElementById('card-stage').appendChild(card);
    }

    // Function to format numbers with commas
    function number_format(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
});

</script>

<?php

// Register the shortcode
add_shortcode('world_travel_plugin', 'world_travel_shortcode');

?>
