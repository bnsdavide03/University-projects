package it.unibg.jarfin.analytics_service;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;

@SpringBootApplication
public class AnalyticsServiceApplication {

    /**
     * Starts the Analytics Service application using the configuration parameters
     * provided by Spring Boot.
     *
     * @param args the application configuration parameters.
     */
    public static void main(String[] args) {
        SpringApplication.run(AnalyticsServiceApplication.class, args);
    }
}