package it.unibg.jarfin.accounting_service;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;

@SpringBootApplication
public class AccountingServiceApplication {
    /**
     * Starts the application using the configuration parameters provided by Spring
     * Boot.
     *
     * @param args the application configuration parameters.
     */
    public static void main(String[] args) {
        SpringApplication.run(AccountingServiceApplication.class, args);
    }
}