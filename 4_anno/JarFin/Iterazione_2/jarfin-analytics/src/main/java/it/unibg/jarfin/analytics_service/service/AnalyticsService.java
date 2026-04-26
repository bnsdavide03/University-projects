package it.unibg.jarfin.analytics_service.service;

import java.math.BigDecimal;
import java.math.RoundingMode;
import java.time.LocalDate;
import java.util.Arrays;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Service;
import org.springframework.web.client.RestTemplate;

import it.unibg.jarfin.analytics_service.dto.FinancialReportDTO;
import it.unibg.jarfin.analytics_service.dto.TransactionDTO;
import it.unibg.jarfin.analytics_service.dto.TransactionType;
import lombok.RequiredArgsConstructor;

@Service
@RequiredArgsConstructor
public class AnalyticsService {

    @Value("${accounting.service.url}")
    private String accountingUrl;

    private final RestTemplate restTemplate;

    /**
     * Generate a financial report based on the given transactions.
     * 
     * @return A FinancialReportDTO containing the total income, total expenses,
     *         total balance, and breakdown by category.
     */
    public FinancialReportDTO generateReport() {
        TransactionDTO[] response = restTemplate.getForObject(accountingUrl, TransactionDTO[].class);

        if (response == null || response.length == 0) {
            return new FinancialReportDTO();
        }

        List<TransactionDTO> transactions = Arrays.asList(response);

        BigDecimal incomes = BigDecimal.ZERO;
        BigDecimal expenses = BigDecimal.ZERO;
        Map<String, BigDecimal> categories = new HashMap<>();

        for (TransactionDTO t : transactions) {
            BigDecimal amount = t.getAmount();

            if (t.getType() == TransactionType.INCOME) {
                incomes = incomes.add(amount);
            } else if (t.getType() == TransactionType.EXPENSE) {
                expenses = expenses.add(amount);
                categories.merge(t.getCategory(), amount, BigDecimal::add);
            }
        }

        FinancialReportDTO report = new FinancialReportDTO();
        report.setTotalIncomes(incomes);
        report.setTotalExpenses(expenses);
        report.setTotalBalance(incomes.subtract(expenses));
        report.setBreakdownByCategory(categories);

        calculateProjections(report, incomes, expenses);

        return report;
    }

    /**
     * Calculates the projected monthly expenses and savings rate based on the given
     * incomes and expenses.
     * 
     * @param report   The FinancialReportDTO to be populated with the calculated
     *                 values.
     * @param incomes  The total income of the user.
     * @param expenses The total expenses of the user.
     */
    private void calculateProjections(FinancialReportDTO report, BigDecimal incomes, BigDecimal expenses) {
        LocalDate today = LocalDate.now();
        int dayOfMonth = today.getDayOfMonth();
        int daysInMonth = today.lengthOfMonth();

        if (dayOfMonth > 0) {
            BigDecimal dailyAverage = expenses.divide(BigDecimal.valueOf(dayOfMonth), 2, RoundingMode.HALF_UP);
            BigDecimal projected = dailyAverage.multiply(BigDecimal.valueOf(daysInMonth));
            report.setProjectedMonthlyExpenses(projected);
        }

        if (incomes.compareTo(BigDecimal.ZERO) > 0) {
            BigDecimal balance = incomes.subtract(expenses);
            BigDecimal rate = balance.divide(incomes, 4, RoundingMode.HALF_UP)
                    .multiply(BigDecimal.valueOf(100));
            report.setSavingsRate(rate);

            if (rate.compareTo(new BigDecimal("20")) > 0) {
                report.setAlertLevel("GREEN");
                report.setFinancialAdvice("Ottimo lavoro! Continua a risparmiare così.");
            } else if (rate.compareTo(BigDecimal.ZERO) > 0) {
                report.setAlertLevel("YELLOW");
                report.setFinancialAdvice("Cerca di ridurre le spese non essenziali.");
            } else {
                report.setAlertLevel("RED");
                report.setFinancialAdvice("Attenzione: le uscite superano le entrate.");
            }
        } else {
            if (expenses.compareTo(BigDecimal.ZERO) > 0) {
                report.setSavingsRate(new BigDecimal("-100"));
                report.setAlertLevel("RED");
                report.setFinancialAdvice("⚠️ Attenzione! Stai spendendo senza alcuna entrata registrata.");
            } else {
                report.setSavingsRate(BigDecimal.ZERO);
                report.setAlertLevel("GREY");
                report.setFinancialAdvice("Registra delle transazioni per iniziare l'analisi.");
            }
        }
    }
}