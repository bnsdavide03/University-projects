#include "Simulator.hpp"
#include <iostream>
#include <limits>
#include <algorithm>

Simulator::Simulator(int N, double lambda, const std::vector<double>& mus, RoutingPolicy policy, double max_time)
    : N(N), lambda(lambda), policy(policy), max_time(max_time), current_time(0.0), round_robin_index(0), total_packets_generated(0) {
    
    // Valori di fallback in caso di parametri di base non validi
    if (this->N <= 0) this->N = 1;
    if (this->lambda <= 0) this->lambda = 1.0;
    
    // Inizializza ciascun servente e la sua distribuzione del tempo di servizio
    for (int i = 0; i < this->N; ++i) {
        double mu = (i < (int)mus.size() && mus[i] > 0) ? mus[i] : 1.0; // Fallback mu = 1.0
        servers.emplace_back(i, mu);
        service_dists.emplace_back(mu);
    }

    // Inizializzazione dei generatori di variabili aleatorie
    std::random_device rd;
    rng.seed(rd());
    arrival_dist = std::exponential_distribution<double>(this->lambda);
    random_server_dist = std::uniform_int_distribution<int>(0, this->N - 1);
}

void Simulator::run() {
    // Schedulazione primo arrivo
    double first_arrival_time = arrival_dist(rng);
    event_queue.push({first_arrival_time, EventType::ARRIVAL, -1});

    while (!event_queue.empty() && current_time <= max_time) {
        Event e = event_queue.top();
        event_queue.pop();
        
        current_time = e.time;

        if (e.type == EventType::ARRIVAL) {
            handle_arrival(e);
        } else if (e.type == EventType::DEPARTURE) {
            handle_departure(e);
        }
    }
    
    // Aggiornamento finale degli integrali per computare correttamente le medie
    for (int i = 0; i < N; ++i) {
        servers[i].update_area(current_time);
    }
}

int Simulator::route_packet() {
    switch (policy) {
        case RoutingPolicy::RANDOM:
            return random_server_dist(rng);
        case RoutingPolicy::ROUND_ROBIN: {
            int selected = round_robin_index;
            round_robin_index = (round_robin_index + 1) % N;
            return selected;
        }
        case RoutingPolicy::SHORTEST_QUEUE: {
            int shortest = 0;
            size_t min_len = servers[0].queue.size() + (servers[0].is_busy ? 1 : 0);
            // Cerca il servente con il minor numero di richieste totali (coda + servizio)
            for (int i = 1; i < N; ++i) {
                size_t current_len = servers[i].queue.size() + (servers[i].is_busy ? 1 : 0);
                if (current_len < min_len) {
                    min_len = current_len;
                    shortest = i;
                }
            }
            return shortest;
        }
    }
    return 0;
}

void Simulator::handle_arrival(const Event& e) {
    total_packets_generated++;
    
    // Schedula immediatamente il successivo arrivo
    double next_arrival = current_time + arrival_dist(rng);
    if (next_arrival <= max_time) {
        event_queue.push({next_arrival, EventType::ARRIVAL, -1});
    }

    // Instradamento del pacchetto secondo la policy scelta
    int server_id = route_packet();
    Server& s = servers[server_id];
    
    s.update_area(current_time);

    if (!s.is_busy) {
        // Se il servente è libero, inizia il servizio immediatamente
        s.is_busy = true;
        double departure_time = current_time + service_dists[server_id](rng);
        event_queue.push({departure_time, EventType::DEPARTURE, server_id});
        s.total_response_time += (departure_time - current_time);
        s.total_packets_served++;
    } else {
        // Se il servente è occupato, il pacchetto si accoda
        s.queue.push(current_time);
    }
}

void Simulator::handle_departure(const Event& e) {
    Server& s = servers[e.server_id];
    
    s.update_area(current_time);

    if (!s.queue.empty()) {
        // Estrae il pacchetto più vecchio dalla coda (FIFO)
        double arrival_time = s.queue.front();
        s.queue.pop();
        
        // Calcola e schedula l'istante di termine servizio
        double departure_time = current_time + service_dists[e.server_id](rng);
        event_queue.push({departure_time, EventType::DEPARTURE, e.server_id});
        
        // Aggiorna i contatori prestazionali per il pacchetto servito
        s.total_response_time += (departure_time - arrival_time);
        s.total_packets_served++;
    } else {
        // Nessun pacchetto in attesa: il servente passa allo stato libero
        s.is_busy = false;
    }
}

void Simulator::print_results() const {
    double total_resp_time = 0;
    long long total_served = 0;
    double max_q = -1.0;
    double min_q = std::numeric_limits<double>::max();

    std::cout << "=== Risultati Simulazione ===\n";
    std::cout << "Politica di Routing: ";
    if (policy == RoutingPolicy::RANDOM) std::cout << "Random\n";
    else if (policy == RoutingPolicy::ROUND_ROBIN) std::cout << "Round-Robin\n";
    else std::cout << "Shortest Queue\n";
    std::cout << "Tempo di simulazione: " << current_time << "\n";
    
    for (int i = 0; i < N; ++i) {
        const Server& s = servers[i];
        double mean_q = current_time > 0 ? s.area_queue_length / current_time : 0;
        
        total_resp_time += s.total_response_time;
        total_served += s.total_packets_served;
        
        max_q = std::max(max_q, mean_q);
        min_q = std::min(min_q, mean_q);
        
        std::cout << "Servente " << i << ":\n"
                  << "  Lunghezza media coda: " << mean_q << "\n"
                  << "  Pacchetti serviti: " << s.total_packets_served << "\n";
    }

    double global_mean_response_time = (total_served > 0) ? (total_resp_time / total_served) : 0;
    
    // Calcolo indice di sbilanciamento (differenza tra la massima e minima lunghezza media tra le code)
    double imbalance_index = max_q - min_q;
    if (N == 1) imbalance_index = 0.0;
    
    std::cout << "-----------------------------\n";
    std::cout << "Tempo medio di permanenza (W): " << global_mean_response_time << "\n";
    std::cout << "Indice di sbilanciamento: " << imbalance_index << "\n";
    std::cout << "Pacchetti totali serviti: " << total_served << "\n";
}
