package it.unibg.jarfin.analytics_service.dto;

import java.math.BigDecimal;
import java.util.Collections;
import java.util.Map;
import lombok.Data;

@Data
public class FinancialReportDTO {
    private BigDecimal totalBalance = BigDecimal.ZERO;
    private BigDecimal totalIncomes = BigDecimal.ZERO;
    private BigDecimal totalExpenses = BigDecimal.ZERO;
    
    private Map<String, BigDecimal> breakdownByCategory = Collections.emptyMap();
    
    private BigDecimal projectedMonthlyExpenses = BigDecimal.ZERO;
    private BigDecimal savingsRate = BigDecimal.ZERO;
    
    private String financialAdvice = "Dati insufficienti.";
    private String alertLevel = "GRAY";
}