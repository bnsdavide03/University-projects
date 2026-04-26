package it.unibg.jarfin.accounting_service.mapper;

import it.unibg.jarfin.accounting_service.dto.TransactionRequest;
import it.unibg.jarfin.accounting_service.dto.TransactionResponse;
import it.unibg.jarfin.accounting_service.model.Transaction;
import org.springframework.stereotype.Component;

@Component
public class TransactionMapper {

    /**
     * Maps a TransactionRequest object to a Transaction entity.
     * 
     * @param request a TransactionRequest object containing the data to be mapped
     * @return a Transaction entity containing the mapped data
     */
    public Transaction toEntity(TransactionRequest request) {
        Transaction t = new Transaction();
        t.setAmount(request.getAmount());
        t.setDate(request.getDate());
        t.setCategory(request.getCategory());
        t.setDescription(request.getDescription());
        t.setType(request.getType());

        return t;
    }

    /**
     * Maps a Transaction entity to a TransactionResponse object.
     * 
     * @param entity a Transaction entity containing the data to be mapped
     * @return a TransactionResponse object containing the mapped data
     */
    public TransactionResponse toResponse(Transaction entity) {
        TransactionResponse r = new TransactionResponse();
        r.setId(entity.getId());
        r.setAmount(entity.getAmount());
        r.setDate(entity.getDate());
        r.setCategory(entity.getCategory());
        r.setDescription(entity.getDescription());

        r.setType(entity.getType());

        return r;
    }
}