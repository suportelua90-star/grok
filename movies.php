<?php

$api_key = '042ca3561d20c34adcc98489df9cc4b2';

$language = "en-EN";

$cache_folder = 'cache/';
$cache_file = $cache_folder . 'combined_cache.json';

if (!file_exists($cache_folder)) {
    mkdir($cache_folder, 0777, true);
}

$cache_duration = 12 * 60 * 60;

if (file_exists($cache_file) && time() - filemtime($cache_file) < $cache_duration) {
    $cached_data = file_get_contents($cache_file);
    $combined_data = json_decode($cached_data, true);
} else {
    $movies_url = "https://api.themoviedb.org/3/trending/movie/week?api_key=$api_key&language=$language";
    $movies_curl = curl_init($movies_url);
    curl_setopt($movies_curl, CURLOPT_RETURNTRANSFER, true);
    $movies_response = curl_exec($movies_curl);
    curl_close($movies_curl);

    $movies_data = json_decode($movies_response, true);

    $shows_url = "https://api.themoviedb.org/3/trending/tv/week?api_key=$api_key&language=$language";
    $shows_curl = curl_init($shows_url);
    curl_setopt($shows_curl, CURLOPT_RETURNTRANSFER, true);
    $shows_response = curl_exec($shows_curl);
    curl_close($shows_curl);

    $shows_data = json_decode($shows_response, true);

    $combined_data = [];

    $numMovies = count($movies_data['results']);
    $numShows = count($shows_data['results']);
    $maxCount = max($numMovies, $numShows);

    for ($i = 0; $i < $maxCount; $i++) {
        if ($i < $numMovies) {
            $movie = $movies_data['results'][$i];
            $backdrop_path = 'https://image.tmdb.org/t/p/original' . $movie['backdrop_path'];
            $poster_path = 'https://image.tmdb.org/t/p/original' . $movie['poster_path'];
            $title = $movie['title'];
            $subtitle = $movie['overview'];
            $url = 'https://www.themoviedb.org/movie/' . $movie['id'];

            $combined_data[] = array(
                "image" => $backdrop_path,
                "artWork" => $poster_path,
                "title" => $title,
                "subtitle" => $subtitle,
                "url" => $url
            );
        }

        if ($i < $numShows) {
            $show = $shows_data['results'][$i];
            $backdrop_path = 'https://image.tmdb.org/t/p/original' . $show['backdrop_path'];
            $poster_path = 'https://image.tmdb.org/t/p/original' . $show['poster_path'];
            $title = $show['name'];
            $subtitle = $show['overview'];
            $url = 'https://www.themoviedb.org/movie/' . $show['id'];

            $combined_data[] = array(
                "image" => $backdrop_path,
                "artWork" => $poster_path,
                "title" => $title,
                "subtitle" => $subtitle,
                "url" => $url
            );
        }
    }

    $encoded_data = json_encode($combined_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    file_put_contents($cache_file, $encoded_data);
}

header("Content-Type: application/json");
echo json_encode($combined_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
