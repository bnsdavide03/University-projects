package it.unibg.jarfin.web.controller;

import it.unibg.jarfin.web.dto.FinancialReport;
import it.unibg.jarfin.web.dto.ParsedTransaction;
import lombok.RequiredArgsConstructor;
import lombok.extern.slf4j.Slf4j;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.core.ParameterizedTypeReference;
import org.springframework.http.HttpMethod;
import org.springframework.http.ResponseEntity;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.client.RestClientException;
import org.springframework.web.client.RestTemplate;

import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

@Controller
@RequiredArgsConstructor
@Slf4j
public class HomeController {

    private final RestTemplate restTemplate;

    private static final String VIEW_TRANSACTIONS = "transactions";
    private static final String VIEW_INDEX = "index";

    private static final String MODEL_ATTR_TRANSACTIONS = "transactions";
    private static final String MODEL_ATTR_REPORT = "report";
    private static final String MODEL_ATTR_ERROR = "error";

    @Value("${api.gateway.url:http://localhost:8080}")
    private String gatewayUrl;

    /**
     * Displays the main page containing the financial dashboard and transaction
     * history.
     *
     * @param model The model for the HTML page.
     * @return The HTML view to be displayed.
     */
    @GetMapping("/")
    public String home(Model model) {
        loadFinancialReport(model);
        loadTransactionsList(model);
        return VIEW_INDEX;
    }

    /**
     * Returns the HTML view displaying the transaction history.
     * * @param model The model for the HTML page.
     * 
     * @return The HTML view to be displayed.
     */
    @GetMapping("/transactions")
    public String allTransactions(Model model) {
        loadTransactionsList(model);
        return VIEW_TRANSACTIONS;
    }

    /**
     * Deletes the transaction with the specified ID.
     * Performs a GET request to the /api/transactions/{id} API to delete the
     * transaction.
     * If an error occurs, it logs the error and returns the transactions view.
     *
     * @param id The ID of the transaction to be deleted.
     * @return The transactions view.
     */
    @GetMapping("/delete/{id}")
    public String deleteTransaction(@PathVariable Long id) {
        try {
            restTemplate.delete(gatewayUrl + "/api/transactions/" + id);
        } catch (RestClientException e) {
            log.error("Errore delete: {}", e.getMessage());
        }
        return "redirect:/" + VIEW_TRANSACTIONS;
    }

    /**
     * Sends a PUT request to the /api/transactions/{id} API to update the
     * transaction.
     * If an error occurs, it logs the error and returns the transactions view.
     *
     * @param transaction The transaction to be updated.
     * @return The transactions view.
     */
    @PostMapping("/update")
    public String updateTransaction(@ModelAttribute ParsedTransaction transaction) {
        try {
            restTemplate.put(gatewayUrl + "/api/transactions/" + transaction.getId(), transaction);
        } catch (RestClientException e) {
            log.error("Errore update: {}", e.getMessage());
        }
        return "redirect:/" + VIEW_TRANSACTIONS;
    }

    /**
     * Loads the financial report and adds it to the model.
     * If the API call fails, adds an empty report and an error message to the
     * model.
     * 
     * @param model The model for the HTML page.
     */
    private void loadFinancialReport(Model model) {
        try {
            FinancialReport report = restTemplate.getForObject(gatewayUrl + "/api/analytics/report",
                    FinancialReport.class);
            model.addAttribute(MODEL_ATTR_REPORT, report != null ? report : new FinancialReport());
        } catch (RestClientException e) {
            model.addAttribute(MODEL_ATTR_REPORT, new FinancialReport());
            model.addAttribute(MODEL_ATTR_ERROR, "Servizio Analytics non disponibile.");
        }
    }

    /**
     * Loads the list of transactions from the API and adds it to the model.
     * If the API call fails, adds an empty list and an error message to the model.
     * 
     * @param model The model for the HTML page.
     */
    private void loadTransactionsList(Model model) {
        try {
            ResponseEntity<List<ParsedTransaction>> response = restTemplate.exchange(
                    gatewayUrl + "/api/transactions",
                    HttpMethod.GET,
                    null,
                    new ParameterizedTypeReference<List<ParsedTransaction>>() {
                    });

            List<ParsedTransaction> list = response.getBody() != null ? response.getBody() : new ArrayList<>();

            list.sort((t1, t2) -> {
                if (t1.getId() == null || t2.getId() == null)
                    return 0;
                return t2.getId().compareTo(t1.getId());
            });

            model.addAttribute(MODEL_ATTR_TRANSACTIONS, list);

        } catch (RestClientException e) {
            model.addAttribute(MODEL_ATTR_TRANSACTIONS, Collections.emptyList());
        }
    }
}