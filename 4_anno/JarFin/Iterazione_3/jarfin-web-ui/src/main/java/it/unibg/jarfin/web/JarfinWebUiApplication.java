package it.unibg.jarfin.web;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.context.annotation.Bean;
import org.springframework.web.client.RestTemplate;

@SpringBootApplication
public class JarfinWebUiApplication {

    /**
     * Main entry point for the web application.
     * 
     * Runs the Spring Boot application using the provided command line arguments.
     * 
     * @param args Command line arguments.
     */
    public static void main(String[] args) {
        SpringApplication.run(JarfinWebUiApplication.class, args);
    }

    /**
     * Returns a new instance of {@link RestTemplate} used to perform
     * RESTful calls to other microservices.
     *
     * @return a new instance of {@link RestTemplate}
     */
    @Bean
    public RestTemplate restTemplate() {
        return new RestTemplate();
    }
}