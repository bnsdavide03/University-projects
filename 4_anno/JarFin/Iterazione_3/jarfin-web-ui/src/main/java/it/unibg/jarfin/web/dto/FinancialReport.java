package it.unibg.jarfin.web.dto;

import com.fasterxml.jackson.annotation.JsonProperty;
import lombok.Data;
import java.math.BigDecimal;
import java.util.Map;
import java.util.LinkedHashMap;

@Data
public class FinancialReport {
    
    @JsonProperty("totalIncomes") 
    private BigDecimal totalIncome = BigDecimal.ZERO;

    @JsonProperty("totalExpenses")
    private BigDecimal totalExpense = BigDecimal.ZERO;

    @JsonProperty("totalBalance") 
    private BigDecimal balance = BigDecimal.ZERO;

    @JsonProperty("breakdownByCategory")
    private Map<String, BigDecimal> expenseByCategory = new LinkedHashMap<>();

    @JsonProperty("savingsRate")
    private BigDecimal savingsRate = BigDecimal.ZERO;

    @JsonProperty("financialAdvice")
    private String financialAdvice = "Nessun dato sufficiente per l'analisi.";

    @JsonProperty("alertLevel")
    private String alertLevel = "GRAY";
}