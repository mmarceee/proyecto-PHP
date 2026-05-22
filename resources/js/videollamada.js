import { Room, RoomEvent, createLocalVideoTrack, createLocalAudioTrack } from 'livekit-client';

document.addEventListener('alpine:init', () => {
    Alpine.data('salaVideollamada', (reservaId) => ({
        cargando: true,
        conectado: false,
        error: '',
        room: null,

        async iniciar() {
            try {
                // 1. Buscamos el token CSRF para seguridad
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                // 2. Pedimos el "Pase VIP" a nuestra API de Laravel
                const response = await fetch(`/api/reserva/${reservaId}/videollamada/token`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                if (!response.ok) throw new Error('No se pudo obtener el permiso de acceso');
                const data = await response.json();

                // 3. Preparamos la Sala Virtual
                this.room = new Room();

                // 4. ¿Qué hacer cuando entra la otra persona? 
                // Enganchamos su video a nuestra pantalla grande
                this.room.on(RoomEvent.TrackSubscribed, (track, publication, participant) => {
                    const element = track.attach();
                    element.classList.add('w-full', 'h-full', 'object-cover'); // Estilos de Tailwind
                    document.getElementById('video-remoto').appendChild(element);
                });

                // 5. Nos conectamos a los servidores de LiveKit Cloud
                await this.room.connect(data.url, data.token);
                this.conectado = true;
                this.cargando = false;

                // 6. Intentamos prender la cámara (si tiene)
                try {
                    const localVideoTrack = await createLocalVideoTrack();
                    await this.room.localParticipant.publishTrack(localVideoTrack);
                    
                    const localElement = localVideoTrack.attach();
                    localElement.classList.add('w-full', 'h-full', 'object-cover');
                    document.getElementById('video-local').appendChild(localElement);
                } catch (error) {
                    console.warn('El usuario no tiene cámara web conectada. Entrando sin video.');
                    // Opcional: Podrías poner una imagen de perfil por defecto en 'video-local' aquí
                }

                // 7. Intentamos prender el micrófono (si tiene)
                try {
                    const localAudioTrack = await createLocalAudioTrack();
                    await this.room.localParticipant.publishTrack(localAudioTrack);
                } catch (error) {
                    console.warn('El usuario no tiene micrófono conectado.');
                }

            } catch (error) {
                console.error('Error en videollamada:', error);
                this.error = 'No se pudo conectar a la sala.';
                this.cargando = false;
            }
        },

        desconectar() {
            if (this.room) {
                this.room.disconnect();
            }
            window.location.href = '/dashboard'; // Al colgar, volvemos al inicio
        }
    }));
});