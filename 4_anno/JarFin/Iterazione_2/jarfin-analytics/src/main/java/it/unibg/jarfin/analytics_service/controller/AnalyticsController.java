package it.unibg.jarfin.analytics_service.controller;

import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;
import it.unibg.jarfin.analytics_service.dto.FinancialReportDTO;
import it.unibg.jarfin.analytics_service.service.AnalyticsService;
import lombok.RequiredArgsConstructor;

@RestController
@RequestMapping("/api/analytics")
@RequiredArgsConstructor
public class AnalyticsController {

    private final AnalyticsService service;

    /**
     * Returns a FinancialReportDTO object, containing the total balance, total
     * incomes, total expenses, breakdown by category, projected monthly expenses,
     * savings rate and financial advice.
     * 
     * @return a FinancialReportDTO object
     */
    @GetMapping("/report")
    public FinancialReportDTO getReport() {
        return service.generateReport();
    }
}