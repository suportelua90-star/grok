<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full-Screen Content Banner</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: transparent;
            backdrop-filter: blur(5px);
            overflow: hidden;
        }
        #content-container {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        #content-poster {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
             border-radius: 15px;
        }
        .content-info {
            position: absolute;
            top: 70px;
            left: 133px;
            color: white;
            text-shadow: 1px 1px 2px black, 0 0 25px blue, 0 0 10px darkblue;
            font-size: 18px;
            font-weight: bold;
            font-family: Arial, sans-serif;
            text-transform: uppercase;
            letter-spacing: 1px;
            z-index: 1;
            padding: 5px 10px;
        }
        .content-details {
            position: absolute;
            top: 110px;
            left: 140px;
            color: white;
            font-size: 13px;
            z-index: 1;
        }
        .content-description {
            position: absolute;
            bottom: 20px;
            top: 140px;
            left: 100px;
            color: white;
            text-shadow: 1px 1px 2px black, 0 0 25px blue, 0 0 5px darkblue;
            width: 75%;
            font-size: 12px;
            z-index: 1;
            display: flex;
            align-items: center;
        }
        .content-thumbnail {
            max-width: 12%;
            height: auto;
            margin-right: 30px;
            border: 3px solid white;
            border-radius: 5px;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div id="content-container">
        <img id="content-poster" src="" alt="Content Poster">
        <div id="content-info" class="content-info"></div>
        <div id="content-details" class="content-details"></div>
        <div id="content-description" class="content-description">
            <img id="content-thumbnail" src="" alt="Content Thumbnail">
            <div id="content-description-text"></div>
        </div>
    </div>
 
    <script>
        const apiKey = '042ca3561d20c34adcc98489df9cc4b2';
        let currentIndex = 0;
        let contentIds = [];
        let intervalId = null;
        const transitionInterval = 10000;

        async function fetchContentIds() {
            try {
                const [
                    popularMoviesResponse,
                    popularShowsResponse,
                    superheroMoviesResponse,
                    childrenMoviesResponse
                ] = await Promise.all([
                    fetch(`https://api.themoviedb.org/3/movie/popular?api_key=${apiKey}&language=en-EN`),
                    fetch(`https://api.themoviedb.org/3/tv/popular?api_key=${apiKey}&language=en-EN`),
                    fetch(`https://api.themoviedb.org/3/discover/movie?api_key=${apiKey}&with_genres=28&language=en-EN`),
                    fetch(`https://api.themoviedb.org/3/discover/movie?api_key=${apiKey}&with_genres=16&language=en-EN`)
                ]);

                const [
                    popularMoviesData,
                    popularShowsData,
                    superheroMoviesData,
                    childrenMoviesData
                ] = await Promise.all([
                    popularMoviesResponse.json(),
                    popularShowsResponse.json(),
                    superheroMoviesResponse.json(),
                    childrenMoviesResponse.json()
                ]);

                const popularMoviesIds = popularMoviesData.results.map(movie => movie.id);
                const popularShowsIds = popularShowsData.results.map(show => show.id);
                const superheroMoviesIds = superheroMoviesData.results.map(movie => movie.id);
                const childrenMoviesIds = childrenMoviesData.results.map(movie => movie.id);

                contentIds = [
                    ...popularMoviesIds,
                    ...popularShowsIds,
                    ...superheroMoviesIds,
                    ...childrenMoviesIds
                ];
            } catch (error) {
                console.error(error);
            }
        }

        function preloadNextImage() {
            if (contentIds.length === 0) {
                console.error('Falha ao obter IDs dos conteúdos.');
                return;
            }

            const nextIndex = (currentIndex + 1) % contentIds.length;
            const nextContentId = contentIds[nextIndex];

            Promise.all([
                fetch(`https://api.themoviedb.org/3/movie/${nextContentId}?api_key=${apiKey}&language=pt-BR`),
                fetch(`https://api.themoviedb.org/3/tv/${nextContentId}?api_key=${apiKey}&language=pt-BR`)
            ])
            .then(([movieResponse, showResponse]) => {
                return movieResponse.ok ? movieResponse.json() : showResponse.json();
            })
            .then((contentData) => {
                const nextImage = new Image();
                nextImage.onload = () => {
                    document.getElementById('content-poster').src = nextImage.src;
                    updateContentInfo(contentData);
                };
                nextImage.src = `https://image.tmdb.org/t/p/original${contentData.backdrop_path}`;
            })
            .catch((error) => console.error(error));
        }

        function updateContentInfo(contentData) {
            const contentType = contentData.title ? 'Filme' : 'Série';
            const contentTitle = `${contentData.title || contentData.name} (${new Date(contentData.release_date || contentData.first_air_date).getFullYear()})`;
            const contentGenre = contentData.genres ? contentData.genres.map(genre => genre.name).join(', ') : 'Gênero não especificado';
            const contentDuration = contentData.runtime ? `${contentData.runtime} min` : 'Duração não especificada';
            const contentDescription = contentData.overview.substring(0, 80) + (contentData.overview.length > 80 ? '...' : '');
            const contentThumbnail = `https://image.tmdb.org/t/p/w200${contentData.poster_path}`;

            document.getElementById('content-info').textContent = `${contentType}: ${contentTitle}`;
            document.getElementById('content-details').textContent = `Gênero: ${contentGenre} | Duração: ${contentDuration}`;
            document.getElementById('content-description-text').textContent = contentDescription;
            document.getElementById('content-thumbnail').src = contentThumbnail;

            const contentPosterWidth = document.getElementById('content-poster').clientWidth;
            const maxThumbnailWidth = contentPosterWidth * 0.12;
            document.getElementById('content-thumbnail').style.maxWidth = `${maxThumbnailWidth}px`;

            clearInterval(intervalId);
            intervalId = setInterval(() => {
                currentIndex = (currentIndex + 1) % contentIds.length;
                preloadNextImage();
            }, transitionInterval);
        }

        fetchContentIds().then(() => {
            preloadNextImage();
            intervalId = setInterval(() => {
                currentIndex = (currentIndex + 1) % contentIds.length;
                preloadNextImage();
            }, transitionInterval);
        });
    </script>
</body>
</html>