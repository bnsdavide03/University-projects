package it.unibg.jarfin.accounting_service.dto;

import java.math.BigDecimal;
import java.time.LocalDate;
import it.unibg.jarfin.accounting_service.model.TransactionType;
import jakarta.validation.constraints.DecimalMin;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import lombok.Data;

@Data
public class TransactionRequest {

    @NotNull(message = "L'importo è obbligatorio")
    @DecimalMin(value = "0.01", message = "L'importo deve essere positivo")
    private BigDecimal amount;

    @NotNull(message = "La data è obbligatoria")
    private LocalDate date;

    private String description;

    @NotBlank(message = "La categoria è obbligatoria")
    private String category;

    @NotNull(message = "Il tipo di transazione è obbligatorio (INCOME o EXPENSE)")
    private TransactionType type;
}