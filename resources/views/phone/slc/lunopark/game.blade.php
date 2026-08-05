<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    .lunopark-container { max-width: 800px; margin: 0 auto; padding: 20px; }
    #grid { gap: 2px; background: #333; border: 2px solid #000; }
    .tile { 
        aspect-ratio: 1/1; 
        background: #eee; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        cursor: pointer; 
        font-size: 10px;
        transition: background 0.2s;
    }
    .tile:hover { background: #ddd; }
    .tile.active { background: #ffd700; }
</style>

<div class="lunopark-container">
    <div id="status">Монет: <span id="coins">0</span> | Вагонов: <span id="wagons">0</span></div>
    <div id="grid">
        @for($i = 0; $i < 640; $i++)
            <div class="tile" data-id="{{ $i }}" onclick="playGame(this)">?</div>
        @endfor
    </div>
</div>

<script>
    async function playGame(element) {
        if (element.classList.contains('active')) return;

        const cellId = element.getAttribute('data-id');
        
        try {
            const response = await fetch('/api/lunopark/play', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ 
                    cell_id: cellId,
                    user_id: '{{ auth()->user()->id ?? "test_user_1" }}',
                    cluster_id: 'global_cluster'
                })
            });
            
            const result = await response.json();
            console.log('Ответ сервера:', result);

            // Обработка событий
            if (result.events) {
                result.events.forEach(event => {
                    if (event.type === 'base_slot') {
                        element.innerText = event.coins;
                        element.classList.add('active');
                    }
                    if (event.type === 'flag_trigger') alert('Бонус! 4 флага собрано.');
                    if (event.type === 'carousel') alert('Карусель! Выиграно: ' + event.coins_won);
                    if (event.type === 'ferris_wheel') alert('Колесо! Выиграно: ' + event.coins_won);
                });
            }

            // Обновление состояния UI
            if (result.state) {
                document.getElementById('coins').innerText = result.total_coins_won;
                document.getElementById('wagons').innerText = result.state.user_wagons;
            }

        } catch (err) {
            console.error('Ошибка связи:', err);
        }
    }
</script>