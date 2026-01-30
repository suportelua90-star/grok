<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full-Screen Content Banner</title>
    <style>
        /* Estilos Globais */
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: transparent; /* Editar cor de fundo */
            backdrop-filter: blur(5px);
            overflow: hidden;
            font-family: Arial, sans-serif; /* Editar fonte global */
        }

        /* Marca d'água do site */
       #start-3d-midia {
            position: absolute;
            top: 75%;
            left: 80%;
            transform: translate(-50%, -50%);
            color: white; /* Editar cor do texto */
            font-size: 15px; /* Editar tamanho da fonte */
            font-weight: bold; /* Editar peso da fonte */
            text-align: center;
            z-index: 1;
            text-shadow: 2px 3px 6px black, 0 0 15px white, 0 0 1px darkblue; /* Editar sombra do texto */
            opacity: 0.3; /* Adicionar transparência de 50% /* Adicione as seguintes linhas para controle adicional */
            border-radius: 5px; /* Editar arredondamento do contorno */
        }

        /* Container principal do conteúdo */
        #content-container {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        /* Poster de fundo do conteúdo */
        #content-poster {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
            border-radius: 15px; /* Editar borda */
        }

        /* Caixa de informações do conteúdo */
        .content-info-box {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            z-index: 1;
            padding: 20px; /* Editar padding */
            background-color: rgba(0, 0, 0, 0.3); /* Editar cor de fundo */
            border-radius: 10px; /* Editar borda */
            width: 79%; /* Editar largura */
            height: 50%; /* Editar altura */
            box-shadow: 1px 1px 2px black, 0 0 4px white, 0 0 11px darkblue; /* Editar sombra */
            display: flex;
            flex-direction: row;
            align-items: flex-start;
        }

        /* Miniatura do conteúdo */
        .content-thumbnail {
            width: auto;
            height: auto;
            margin-right: 4px; /* Editar margem da descricao */
            border: 1px solid darkblue; /* Editar borda */
            border-radius: 4px; /* Editar borda */
            box-shadow: 0 4px 8px black, 0 0 10px white; /* Editar sombra */
        }

        /* Texto com detalhes do conteúdo */
        .content-text {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Título do conteúdo */
        .content-title {
            margin: 0;
            font-size: 20px; /* Editar tamanho da fonte */
            font-weight: bold; /* Editar peso da fonte */
            text-transform: uppercase; /* Editar transformação de texto */
            letter-spacing: 1px; /* Editar espaçamento entre letras */
            text-shadow: 1px 1px 2px black, 0 0 25px black, 0 0 10px darkblue; /* Editar sombra do texto */
        }

        /* Detalhes do conteúdo (Gênero e Duração) */
        .content-details, .content-description {
            margin: 5px 0; /* Editar margem */
            font-size: 14px; /* Editar tamanho da fonte */
            text-shadow: 1px 1px 2px black, 0 0 25px black, 0 0 5px darkblue; /* Editar sombra do texto */
            font-family: 'Roboto', sans-serif; /* Editar fonte */
        }

        /* Tipo de conteúdo (Filme ou Série) */
        .content-type {
            position: absolute;
            top: 65%;
            left: 17%;
            color: white; /* Editar cor do texto */
            font-size: 17px; /* Editar tamanho da fonte */
            font-weight: bolder; /* Editar peso da fonte */
            z-index: 2;
            background-color: rgba(195, 59, 59, 1); /* Editar cor de fundo */
            padding: 5px 10px; /* Editar padding */
            box-shadow: 0 2px 12px black, 0 0 15px white; /* Editar sombra */
            border-radius: 3px; /* Editar borda */
            text-align: center;
            text-transform: uppercase; /* Editar transformação de texto */
            width: 70px; /* Editar largura */
            height: 15px; /* Editar altura */
            display: flex; /* Adicionado para centralizar */
            justify-content: center; /* Adicionado para centralizar */
            align-items: center; /* Adicionado para centralizar */
            white-space: nowrap; /* Impedir quebra de linha */
            
        }

        /* Controles */
        .color-picker-container {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div id="content-container">
        <!-- Poster de fundo do conteúdo -->
        <img id="content-poster" src="" alt="Content Poster">
        
        <!-- Marca d'água do site -->
        <div id="start-3d-midia" style="color: white;">Start 3D Midia</div>
        
        <!-- Tipo de conteúdo (Filme ou Série) -->
        <div id="content-type" class="content-type"></div>
        
        <!-- Caixa de informações do conteúdo -->
        <div id="content-info-box" class="content-info-box">
            <!-- Miniatura do conteúdo -->
            <img id="content-thumbnail" class="content-thumbnail" src="" alt="Content Thumbnail">
            
            <!-- Texto com detalhes do conteúdo -->
            <div class="content-text">
                <!-- Título do conteúdo -->
                <h1 id="content-title" class="content-title"></h1>
                <!-- Detalhes do conteúdo (Gênero e Duração) -->
                <p id="content-details" class="content-details"></p>
                <!-- Descrição do conteúdo -->
                <p id="content-description" class="content-description"></p>
            </div>
        </div>
    </div>

    <script>
        const apiKey = 'ec8237f367023fbadd38ab6a1596b40c';
        let currentIndex = 0;
        let contentIds = [];
        let intervalId = null;
        const transitionInterval = 10000; // Intervalo de transição em milissegundos (10 segundos)

        async function fetchContentIds() {
            try {
                const [
                    popularMoviesResponse,
                    popularTvResponse,
                    upcomingMoviesResponse,
                    topRatedMoviesResponse,
                    oscarMoviesResponse
                ] = await Promise.all([
                    fetch(`https://api.themoviedb.org/3/movie/popular?api_key=${apiKey}&language=pt-BR&release_date.gte=2012`),
                    fetch(`https://api.themoviedb.org/3/tv/popular?api_key=${apiKey}&language=pt-BR&first_air_date.gte=2012`),
                    fetch(`https://api.themoviedb.org/3/movie/upcoming?api_key=${apiKey}&language=pt-BR&region=BR`),
                    fetch(`https://api.themoviedb.org/3/movie/top_rated?api_key=${apiKey}&language=pt-BR&release_date.gte=2012`),
                    fetch(`https://api.themoviedb.org/3/movie/now_playing?api_key=${apiKey}&language=pt-BR&region=BR`)
                ]);

                const [
                    popularMoviesData,
                    popularTvData,
                    upcomingMoviesData,
                    topRatedMoviesData,
                    oscarMoviesData
                ] = await Promise.all([
                    popularMoviesResponse.json(),
                    popularTvResponse.json(),
                    upcomingMoviesResponse.json(),
                    topRatedMoviesResponse.json(),
                    oscarMoviesResponse.json()
                ]);

                const popularMoviesIds = popularMoviesData.results.map(movie => ({ id: movie.id, type: 'movie' }));
                const popularTvIds = popularTvData.results.map(tv => ({ id: tv.id, type: 'tv' }));
                const upcomingMoviesIds = upcomingMoviesData.results.map(movie => ({ id: movie.id, type: 'movie' }));
                const topRatedMoviesIds = topRatedMoviesData.results.map(movie => ({ id: movie.id, type: 'movie' }));
                const oscarMoviesIds = oscarMoviesData.results.map(movie => ({ id: movie.id, type: 'movie' }));

                contentIds = [
                    ...popularMoviesIds,
                    ...popularTvIds,
                    ...upcomingMoviesIds,
                    ...topRatedMoviesIds,
                    ...oscarMoviesIds
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
            const { id: nextContentId, type: contentType } = contentIds[nextIndex];

            fetch(`https://api.themoviedb.org/3/${contentType}/${nextContentId}?api_key=${apiKey}&language=pt-BR`)
                .then(response => response.json())
                .then(contentData => {
                    const nextImage = new Image();
                    nextImage.onload = () => {
                        document.getElementById('content-poster').src = nextImage.src;
                        updateContentInfo(contentData, contentType); // Atualiza as informações após carregar a imagem
                    };
                    nextImage.src = `https://image.tmdb.org/t/p/original${contentData.backdrop_path}`;
                })
                .catch(error => console.error(error));
        }

        function updateContentInfo(contentData, contentType) {
            const contentTitle = `${contentData.title || contentData.name} (${new Date(contentData.release_date || contentData.first_air_date).getFullYear()})`;
            const contentGenre = contentData.genres ? contentData.genres.map(genre => genre.name).join(', ') : 'Gênero não especificado';
            const contentDuration = contentData.runtime ? `${contentData.runtime} min` : 'Duração não especificada';
            const contentDescription = contentData.overview.substring(0, 150) + (contentData.overview.length > 150 ? '...' : ''); // Limita a descrição a 150 caracteres
            const contentThumbnail = `https://image.tmdb.org/t/p/w200${contentData.poster_path}`; // Miniatura do conteúdo

            document.getElementById('content-type').textContent = contentType === 'movie' ? 'Filme' : 'TV série';
            document.getElementById('content-title').textContent = contentTitle;
            document.getElementById('content-details').textContent = `Gênero: ${contentGenre} | Duração: ${contentDuration}`;
            document.getElementById('content-description').textContent = contentDescription;
            document.getElementById('content-thumbnail').src = contentThumbnail;

            // Calcula dinamicamente o tamanho máximo da miniatura em relação à largura da imagem de fundo
            const contentPosterWidth = document.getElementById('content-poster').clientWidth;
            const maxThumbnailWidth = contentPosterWidth * 0.13; // 13% da largura da imagem de fundo
            document.getElementById('content-thumbnail').style.maxWidth = `${maxThumbnailWidth}px`;

            // Reinicia o intervalo de transição
            clearInterval(intervalId);
            intervalId = setInterval(() => {
                currentIndex = (currentIndex + 1) % contentIds.length;
                preloadNextImage();
            }, transitionInterval);
        }

        window.onload = async () => {
            await fetchContentIds(); // Carrega os IDs dos conteúdos

            // Inicia o carregamento do primeiro conteúdo
            preloadNextImage();
        };
    </script>
</body>
</html>
