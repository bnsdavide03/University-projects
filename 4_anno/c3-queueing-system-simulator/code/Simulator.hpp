#ifndef SIMULATOR_HPP
#define SIMULATOR_HPP

#include <vector>
#include <queue>
#include <random>
#include <string>
#include "Event.hpp"
#include "Server.hpp"

// Politiche di instradamento disponibili
enum class RoutingPolicy {
    RANDOM,         // Assegnazione casuale
    ROUND_ROBIN,    // Assegnazione ciclica
    SHORTEST_QUEUE  // Assegnazione alla coda più corta (JSQ)
};

// Motore principale della simulazione a eventi discreti
class Simulator {
private:
    int N;                      // Numero di serventi
    double lambda;              // Tasso globale di arrivo (Poisson)
    std::vector<Server> servers;// I serventi del sistema
    RoutingPolicy policy;       // Politica di routing scelta
    double max_time;            // Tempo limite di simulazione

    double current_time;        // Orologio interno della simulazione
    std::priority_queue<Event> event_queue; // Coda eventi (min-heap)
    int round_robin_index;      // Indice per politica Round-Robin

    // Generazione numeri pseudo-casuali
    std::mt19937 rng;
    std::exponential_distribution<double> arrival_dist;                 // Arrivi
    std::vector<std::exponential_distribution<double>> service_dists;   // Servizi (uno per servente)
    std::uniform_int_distribution<int> random_server_dist;              // Supporto per routing random

    long long total_packets_generated; // Statistica globale pacchetti generati

public:
    Simulator(int N, double lambda, const std::vector<double>& mus, RoutingPolicy policy, double max_time = 100000.0);

    void run();                 // Avvia e gestisce il ciclo di simulazione
    void print_results() const; // Stampa i KPI richiesti a terminale

private:
    void handle_arrival(const Event& e);    // Gestore evento arrivo
    void handle_departure(const Event& e);  // Gestore evento partenza
    int route_packet();                     // Seleziona il servente per un nuovo pacchetto in base alla policy
};

#endif
