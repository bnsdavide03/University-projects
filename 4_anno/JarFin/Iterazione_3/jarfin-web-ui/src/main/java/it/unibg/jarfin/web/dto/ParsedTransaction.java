package it.unibg.jarfin.web.dto;

import java.math.BigDecimal;
import java.time.LocalDate;
import lombok.Data;

@Data
public class ParsedTransaction {

    private CommandType commandType = CommandType.CREATE;
    private Long targetId;

    private Long id; 
    private BigDecimal amount;
    private String type;
    private String category;
    private String description;
    private LocalDate date;
}