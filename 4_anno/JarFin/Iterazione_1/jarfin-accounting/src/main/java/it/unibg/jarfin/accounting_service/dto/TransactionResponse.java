package it.unibg.jarfin.accounting_service.dto;

import java.math.BigDecimal;
import java.time.LocalDate;
import it.unibg.jarfin.accounting_service.model.TransactionType;
import lombok.Data;

@Data
public class TransactionResponse {
    private Long id;
    private BigDecimal amount;
    private LocalDate date;
    private String description;
    private String category;
    
    private TransactionType type;
}