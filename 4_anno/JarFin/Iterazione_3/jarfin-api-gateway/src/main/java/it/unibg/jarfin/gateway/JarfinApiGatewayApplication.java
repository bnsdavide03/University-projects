package it.unibg.jarfin.gateway;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;

@SpringBootApplication
public class JarfinApiGatewayApplication {

	/**
	 * Main entry point for the application. It starts the Spring Boot application
	 * with the given arguments.
	 *
	 * @param args the command line arguments passed to the application
	 */
	public static void main(String[] args) {
		SpringApplication.run(JarfinApiGatewayApplication.class, args);
	}
}