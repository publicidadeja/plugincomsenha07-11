class GMANotifications {
    constructor() {
        this.audio = new Audio(gmaNotifications.audioUrl);
    }

    playNotification() {
        this.audio.play().catch(error => {
            console.error('Erro ao tocar notificação:', error);
        });
    }
}

const gmaNotify = new GMANotifications();