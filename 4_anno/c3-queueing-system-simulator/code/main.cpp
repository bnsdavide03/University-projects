#include <iostream>
#include <vector>
#include <string>
#include "Simulator.hpp"

// Stampa a video la sintassi corretta per l'avvio del programma
void print_usage() {
    std::cerr << "Uso: ./simulator <N> <lambda> <policy> <mu_0> [<mu_1> ... <mu_N-1>] [max_time]\n";
    std::cerr << "Policy:\n  0: Random\n  1: Round-Robin\n  2: Shortest Queue\n";
    std::cerr << "Assunzioni: Se max_time non specificato, default a 100000. Se i mu_i non sono sufficienti, vengono impostati a 1.0.\n";
}

int main(int argc, char* argv[]) {
    // Controllo del numero minimo di argomenti (almeno un mu_0 è richiesto)
    if (argc < 5) {
        print_usage();
        return 1;
    }

    // Parsing dei parametri obbligatori
    int N = std::stoi(argv[1]);
    double lambda = std::stod(argv[2]);
    int policy_int = std::stoi(argv[3]);
    
    // Configurazione della policy di instradamento
    RoutingPolicy policy;
    if (policy_int == 0) policy = RoutingPolicy::RANDOM;
    else if (policy_int == 1) policy = RoutingPolicy::ROUND_ROBIN;
    else if (policy_int == 2) policy = RoutingPolicy::SHORTEST_QUEUE;
    else {
        std::cerr << "Policy non valida. Uso Random come default.\n";
        policy = RoutingPolicy::RANDOM;
    }

    // Acquisizione dei tassi di servizio per ciascun servente
    std::vector<double> mus;
    for (int i = 0; i < N && (4 + i) < argc; ++i) {
        mus.push_back(std::stod(argv[4 + i]));
    }
    
    // Lettura parametro opzionale max_time
    double max_time = 100000.0;
    if (4 + N < argc) {
        max_time = std::stod(argv[4 + N]);
    }

    // Creazione ed esecuzione dell'istanza del simulatore
    Simulator sim(N, lambda, mus, policy, max_time);
    sim.run();
    sim.print_results();

    return 0;
}
