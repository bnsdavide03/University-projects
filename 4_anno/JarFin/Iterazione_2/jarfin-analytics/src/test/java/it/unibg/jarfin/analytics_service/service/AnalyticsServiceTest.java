package it.unibg.jarfin.analytics_service.service;

import static org.junit.jupiter.api.Assertions.assertEquals;
import static org.junit.jupiter.api.Assertions.assertNotNull;
import static org.junit.jupiter.api.Assertions.assertTrue;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.ArgumentMatchers.eq;
import static org.mockito.Mockito.when;

import java.math.BigDecimal;
import java.util.Map;

import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.extension.ExtendWith;
import org.mockito.InjectMocks;
import org.mockito.Mock;
import org.mockito.junit.jupiter.MockitoExtension;
import org.springframework.test.util.ReflectionTestUtils;
import org.springframework.web.client.RestTemplate;

import it.unibg.jarfin.analytics_service.dto.FinancialReportDTO;
import it.unibg.jarfin.analytics_service.dto.TransactionDTO;
import it.unibg.jarfin.analytics_service.dto.TransactionType;

@ExtendWith(MockitoExtension.class)
class AnalyticsServiceTest {

    @Mock
    private RestTemplate restTemplate;

    @InjectMocks
    private AnalyticsService analyticsService;

    private final String fakeUrl = "http://fake-accounting-service/api/transactions";

    /**
     * Sets up the test environment by setting the 'accountingUrl' field
     * of the {@link AnalyticsService} to the fake URL.
     */
    @BeforeEach
    void setUp() {
        ReflectionTestUtils.setField(analyticsService, "accountingUrl", fakeUrl);
    }

    /**
     * Tests the {@link AnalyticsService#generateReport()} method in the scenario
     * where the total income is greater than the total expense (green scenario).
     * The test verifies that the method returns a valid {@link FinancialReportDTO}
     * object
     * with the correct total income, total expense, total balance, savings rate,
     * and
     * breakdown by category.
     */
    @Test
    @DisplayName("Scenario GREEN: Entrate > Uscite (Risparmio Alto)")
    void generateReport_GreenScenario() {
        TransactionDTO income = new TransactionDTO();
        income.setAmount(new BigDecimal("2000.00"));
        income.setType(TransactionType.INCOME);

        TransactionDTO expense = new TransactionDTO();
        expense.setAmount(new BigDecimal("500.00"));
        expense.setType(TransactionType.EXPENSE);
        expense.setCategory("Svago");

        TransactionDTO[] mockResponse = { income, expense };

        when(restTemplate.getForObject(eq(fakeUrl), eq(TransactionDTO[].class)))
                .thenReturn(mockResponse);

        FinancialReportDTO report = analyticsService.generateReport();

        assertNotNull(report);
        assertEquals(new BigDecimal("2000.00"), report.getTotalIncomes());
        assertEquals(new BigDecimal("500.00"), report.getTotalExpenses());

        assertEquals(new BigDecimal("1500.00"), report.getTotalBalance());

        assertEquals(new BigDecimal("75.0000"), report.getSavingsRate());

        assertTrue(report.getAlertLevel().contains("GREEN"));

        Map<String, BigDecimal> categories = report.getBreakdownByCategory();
        assertEquals(new BigDecimal("500.00"), categories.get("Svago"));
    }

    /**
     * Tests the {@link AnalyticsService#generateReport()} method in the scenario
     * where the total expense is greater than the total income (red scenario).
     * The test verifies that the method returns a valid {@link FinancialReportDTO}
     * object with the correct total income, total expense, total balance, savings
     * rate,
     * and alert level.
     */
    @Test
    @DisplayName("Scenario RED: Uscite > Entrate")
    void generateReport_RedScenario() {
        TransactionDTO income = new TransactionDTO();
        income.setAmount(new BigDecimal("1000.00"));
        income.setType(TransactionType.INCOME);

        TransactionDTO expense = new TransactionDTO();
        expense.setAmount(new BigDecimal("1200.00"));
        expense.setType(TransactionType.EXPENSE);
        expense.setCategory("Affitto");

        TransactionDTO[] mockResponse = { income, expense };

        when(restTemplate.getForObject(any(String.class), any()))
                .thenReturn(mockResponse);

        FinancialReportDTO report = analyticsService.generateReport();

        assertEquals(new BigDecimal("-200.00"), report.getTotalBalance());

        assertTrue(report.getSavingsRate().compareTo(BigDecimal.ZERO) < 0);

        assertTrue(report.getAlertLevel().contains("RED"));
    }

    /**
     * Tests the {@link AnalyticsService#generateReport()} method in the scenario
     * where there are expenses but no income (critical scenario).
     * The test verifies that the method returns a valid {@link FinancialReportDTO}
     * object with the correct total income, total expense, total balance, savings
     * rate, and alert level.
     */
    @Test
    @DisplayName("Scenario CRITICAL: Solo Spese, Zero Entrate")
    void generateReport_CriticalScenario() {

        TransactionDTO expense = new TransactionDTO();
        expense.setAmount(new BigDecimal("100.00"));
        expense.setType(TransactionType.EXPENSE);
        expense.setCategory("Cibo");

        TransactionDTO[] mockResponse = { expense };

        when(restTemplate.getForObject(any(String.class), any()))
                .thenReturn(mockResponse);

        FinancialReportDTO report = analyticsService.generateReport();

        assertEquals(BigDecimal.ZERO, report.getTotalIncomes());
        assertEquals(new BigDecimal("-100"), report.getSavingsRate());
        assertTrue(report.getAlertLevel().contains("RED"));
    }

    /**
     * Verifies that the method returns a valid FinancialReportDTO object
     * even when there are no transactions (empty scenario).
     */
    @Test
    @DisplayName("Scenario EMPTY: Nessuna transazione")
    void generateReport_EmptyScenario() {
        when(restTemplate.getForObject(any(String.class), any()))
                .thenReturn(new TransactionDTO[0]);

        FinancialReportDTO report = analyticsService.generateReport();

        assertNotNull(report);
    }

    /**
     * Tests the {@link AnalyticsService#generateReport()} method with a
     * scenario having multiple expense categories.
     * The test verifies that the method returns a valid
     * {@link FinancialReportDTO} object with the correct total income, total
     * expense, total balance, savings rate, and breakdown by category.
     */
    @Test
    @DisplayName("Integrazione Categorie Multiple")
    void generateReport_CategoryAggregation() {
        TransactionDTO t1 = new TransactionDTO();
        t1.setAmount(new BigDecimal("50.00"));
        t1.setType(TransactionType.EXPENSE);
        t1.setCategory("Cibo");

        TransactionDTO t2 = new TransactionDTO();
        t2.setAmount(new BigDecimal("30.00"));
        t2.setType(TransactionType.EXPENSE);
        t2.setCategory("Cibo");

        TransactionDTO t3 = new TransactionDTO();
        t3.setAmount(new BigDecimal("20.00"));
        t3.setType(TransactionType.EXPENSE);
        t3.setCategory("Trasporti");

        TransactionDTO[] mockResponse = { t1, t2, t3 };

        when(restTemplate.getForObject(any(String.class), any()))
                .thenReturn(mockResponse);

        FinancialReportDTO report = analyticsService.generateReport();

        Map<String, BigDecimal> cats = report.getBreakdownByCategory();

        assertEquals(new BigDecimal("80.00"), cats.get("Cibo"));

        assertEquals(new BigDecimal("20.00"), cats.get("Trasporti"));
    }
}