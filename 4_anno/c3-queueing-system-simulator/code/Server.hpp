#ifndef SERVER_HPP
#define SERVER_HPP

#include <queue>

// Modella un singolo servente e la sua coda FIFO
struct Server {
    int id;                     // Identificativo univoco
    double mu;                  // Tasso di servizio (pacchetti/unità di tempo)
    bool is_busy;               // Stato attuale (true se in servizio)
    std::queue<double> queue;   // Coda che memorizza l'istante di arrivo dei pacchetti

    // Metriche per statistiche
    long long total_packets_served; // Numero totale di pacchetti processati
    double total_response_time;     // Somma dei tempi di permanenza (attesa + servizio)
    double area_queue_length;       // Integrale nel tempo della dimensione della coda
    double last_update_time;        // Ultimo istante in cui l'area è stata aggiornata
    
    Server(int id, double mu) : id(id), mu(mu), is_busy(false), total_packets_served(0), total_response_time(0.0), area_queue_length(0.0), last_update_time(0.0) {}

    // Aggiorna l'integrale della coda calcolando l'area del gradino (per media tempo continuo)
    void update_area(double current_time) {
        area_queue_length += queue.size() * (current_time - last_update_time);
        last_update_time = current_time;
    }
};

#endif
