package it.unibg.jarfin.accounting_service.service;

import static org.junit.jupiter.api.Assertions.assertEquals;
import static org.junit.jupiter.api.Assertions.assertNotNull;
import static org.junit.jupiter.api.Assertions.assertThrows;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.Mockito.never;
import static org.mockito.Mockito.times;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.when;

import java.math.BigDecimal;
import java.time.LocalDate;
import java.util.Arrays;
import java.util.List;
import java.util.Optional;

import org.junit.jupiter.api.Test;
import org.junit.jupiter.api.extension.ExtendWith;
import org.mockito.InjectMocks;
import org.mockito.Mock;
import org.mockito.junit.jupiter.MockitoExtension;

import it.unibg.jarfin.accounting_service.exception.EntityNotFoundException;
import it.unibg.jarfin.accounting_service.model.Transaction;
import it.unibg.jarfin.accounting_service.repository.TransactionRepository;

@ExtendWith(MockitoExtension.class)
class TransactionServiceTest {

    @Mock
    private TransactionRepository repository;

    @InjectMocks
    private TransactionService service;

    /**
     * Verifies that the TransactionService saves a new transaction in the database.
     * 
     * @see TransactionService#saveTransaction(Transaction)
     */
    @Test
    void testSaveTransaction() {
        Transaction inputTransaction = new Transaction();
        inputTransaction.setAmount(new BigDecimal("100.00"));
        inputTransaction.setDescription("Spesa Test");
        inputTransaction.setCategory("Cibo");
        inputTransaction.setDate(LocalDate.now());

        Transaction savedTransaction = new Transaction();
        savedTransaction.setId(1L);
        savedTransaction.setAmount(new BigDecimal("100.00"));

        when(repository.save(any(Transaction.class))).thenReturn(savedTransaction);

        Transaction result = service.saveTransaction(inputTransaction);

        assertNotNull(result);
        assertEquals(1L, result.getId());
        assertEquals(new BigDecimal("100.00"), result.getAmount());
        verify(repository, times(1)).save(inputTransaction);
    }

    /**
     * Verifies that the TransactionService deletes a transaction from the database.
     * 
     * @see TransactionService#deleteTransaction(Long)
     */
    @Test
    void testDeleteTransaction() {
        Long idToDelete = 1L;
        when(repository.existsById(idToDelete)).thenReturn(true);

        service.deleteTransaction(idToDelete);

        verify(repository, times(1)).deleteById(idToDelete);
    }

    /**
     * Verifies that the TransactionService retrieves a list of all transactions
     * stored in the database.
     * 
     * @see TransactionService#getAllTransactions()
     */
    @Test
    void testGetAllTransactions() {
        Transaction t1 = new Transaction();
        t1.setId(1L);
        t1.setAmount(new BigDecimal("50.00"));
        t1.setCategory("Svago");
        t1.setDate(LocalDate.now());

        Transaction t2 = new Transaction();
        t2.setId(2L);
        t2.setAmount(new BigDecimal("120.00"));
        t2.setCategory("Spesa");
        t2.setDate(LocalDate.now());

        List<Transaction> mockList = Arrays.asList(t1, t2);

        when(repository.findAll()).thenReturn(mockList);

        List<Transaction> result = service.getAllTransactions();

        assertNotNull(result);
        assertEquals(2, result.size());
        assertEquals(new BigDecimal("50.00"), result.get(0).getAmount());

        verify(repository, times(1)).findAll();
    }

    /**
     * Verifies that the TransactionService throws an EntityNotFoundException when
     * trying to delete a transaction
     * that does not exist in the database.
     * 
     * @see TransactionService#deleteTransaction(Long)
     */
    @Test
    void testDeleteTransaction_NotFound() {
        Long idNonEsistente = 99L;
        when(repository.existsById(idNonEsistente)).thenReturn(false);

        assertThrows(EntityNotFoundException.class, () -> {
            service.deleteTransaction(idNonEsistente);
        });

        verify(repository, never()).deleteById(any());
    }

    /**
     * Verifies that the TransactionService retrieves a transaction by its ID from
     * the database.
     * The test mocks the repository to return a transaction entity when given the
     * ID,
     * and then performs the GET request and asserts that the response status is OK
     * and that the returned transaction has the correct ID and description.
     * 
     * @see TransactionService#getTransactionById(Long)
     */
    @Test
    void testGetTransactionById_Success() {
        Long id = 1L;
        Transaction foundTransaction = new Transaction();
        foundTransaction.setId(id);
        foundTransaction.setDescription("Trovata");

        when(repository.findById(id)).thenReturn(Optional.of(foundTransaction));

        Transaction result = service.getTransactionById(id);

        assertNotNull(result);
        assertEquals(id, result.getId());
        assertEquals("Trovata", result.getDescription());

        verify(repository, times(1)).findById(id);
    }

    /**
     * Verifies that the TransactionService throws an EntityNotFoundException when
     * trying to retrieve a transaction
     * that does not exist in the database.
     * 
     * @see TransactionService#getTransactionById(Long)
     */
    @Test
    void testGetTransactionById_NotFound() {
        Long idNonEsistente = 999L;

        when(repository.findById(idNonEsistente)).thenReturn(Optional.empty());

        assertThrows(EntityNotFoundException.class, () -> {
            service.getTransactionById(idNonEsistente);
        });

        verify(repository, times(1)).findById(idNonEsistente);
    }

    /**
     * Verifies that the TransactionService updates a transaction in the database.
     * The test mocks the repository to return a transaction entity when given the
     * ID,
     * and then performs the PUT request and asserts that the response status is OK
     * and that the returned transaction has the correct ID and description.
     * 
     * @see TransactionService#updateTransaction(Long, Transaction)
     */
    @Test
    void testUpdateTransaction_Success() {
        Long id = 1L;

        Transaction existingTransaction = new Transaction();
        existingTransaction.setId(id);
        existingTransaction.setAmount(new BigDecimal("50.00"));
        existingTransaction.setDescription("Vecchio");
        existingTransaction.setCategory("VecchioCat");

        Transaction updateDetails = new Transaction();
        updateDetails.setAmount(new BigDecimal("75.00"));
        updateDetails.setDescription("Nuovo");
        updateDetails.setCategory("NuovoCat");
        updateDetails.setDate(LocalDate.now());

        when(repository.findById(id)).thenReturn(Optional.of(existingTransaction));

        when(repository.save(any(Transaction.class))).thenAnswer(invocation -> invocation.getArgument(0));

        Transaction result = service.updateTransaction(id, updateDetails);

        assertNotNull(result);
        assertEquals(new BigDecimal("75.00"), result.getAmount());
        assertEquals("Nuovo", result.getDescription());
        assertEquals("NuovoCat", result.getCategory());

        verify(repository, times(1)).findById(id);
        verify(repository, times(1)).save(existingTransaction);
    }

    /**
     * Verifies that the TransactionService throws an EntityNotFoundException when
     * trying to update a transaction
     * that does not exist in the database.
     * 
     * @see TransactionService#updateTransaction(Long, Transaction)
     */
    @Test
    void testUpdateTransaction_NotFound() {
        Long idNonEsistente = 999L;
        Transaction updateDetails = new Transaction();

        when(repository.findById(idNonEsistente)).thenReturn(Optional.empty());

        assertThrows(EntityNotFoundException.class, () -> {
            service.updateTransaction(idNonEsistente, updateDetails);
        });

        verify(repository, times(1)).findById(idNonEsistente);
        verify(repository, never()).save(any());
    }
}