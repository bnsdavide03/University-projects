#ifndef EVENT_HPP
#define EVENT_HPP

// Tipo di evento della simulazione
enum class EventType {
    ARRIVAL,    // Arrivo di un nuovo pacchetto
    DEPARTURE   // Completamento del servizio
};

// Rappresentazione di un singolo evento
struct Event {
    double time;    // Istante in cui si verifica l'evento
    EventType type;
    int server_id;  // ID del servente (usato solo per DEPARTURE)

    // Definisce l'ordinamento per la priority_queue (min-heap basato sul tempo)
    bool operator<(const Event& other) const {
        return time > other.time;
    }
};

#endif
