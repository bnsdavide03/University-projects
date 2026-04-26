package it.unibg.jarfin.accounting_service.mapper;

import static org.junit.jupiter.api.Assertions.assertEquals;
import static org.junit.jupiter.api.Assertions.assertNotNull;
import static org.junit.jupiter.api.Assertions.assertNull;

import java.math.BigDecimal;
import java.time.LocalDate;

import org.junit.jupiter.api.Test;

import it.unibg.jarfin.accounting_service.dto.TransactionRequest;
import it.unibg.jarfin.accounting_service.dto.TransactionResponse;
import it.unibg.jarfin.accounting_service.model.Transaction;
import it.unibg.jarfin.accounting_service.model.TransactionType;

class TransactionMapperTest {

	private final TransactionMapper mapper = new TransactionMapper();

	/**
	 * Test that the mapper correctly maps a TransactionRequest to a Transaction
	 * entity.
	 * 
	 * Verifies that the mapped entity is not null and that all fields are correctly
	 * mapped.
	 * 
	 * @see TransactionMapper#toEntity(TransactionRequest)
	 */
	@Test
	void testToEntity_CorrectMapping() {
		TransactionRequest request = new TransactionRequest();
		request.setAmount(new BigDecimal("150.00"));
		request.setCategory("Spesa");
		request.setDate(LocalDate.of(2026, 2, 7));
		request.setDescription("Supermercato");
		request.setType(TransactionType.EXPENSE);

		Transaction entity = mapper.toEntity(request);

		assertNotNull(entity, "L'entità mappata non deve essere null");

		assertEquals(new BigDecimal("150.00"), entity.getAmount());
		assertEquals("Spesa", entity.getCategory());
		assertEquals(LocalDate.of(2026, 2, 7), entity.getDate());
		assertEquals("Supermercato", entity.getDescription());
		assertEquals(TransactionType.EXPENSE, entity.getType());

		assertNull(entity.getId());
	}

	/**
	 * Test that the mapper correctly maps a Transaction entity to a
	 * TransactionResponse object.
	 * 
	 * Verifies that the mapped response is not null and that all fields are
	 * correctly mapped.
	 * 
	 * @see TransactionMapper#toResponse(transaction)
	 */
	@Test
	void testToResponse_CorrectMapping() {
		Transaction entity = new Transaction();
		entity.setAmount(new BigDecimal("2000.00"));
		entity.setCategory("Stipendio");
		entity.setDate(LocalDate.of(2026, 1, 27));
		entity.setDescription("Stipendio Gennaio");
		entity.setType(TransactionType.INCOME);

		TransactionResponse response = mapper.toResponse(entity);

		assertEquals(new BigDecimal("2000.00"), response.getAmount());
		assertEquals("Stipendio", response.getCategory());
		assertEquals(LocalDate.of(2026, 1, 27), response.getDate());
		assertEquals("Stipendio Gennaio", response.getDescription());
		assertEquals(TransactionType.INCOME, response.getType());
	}
}