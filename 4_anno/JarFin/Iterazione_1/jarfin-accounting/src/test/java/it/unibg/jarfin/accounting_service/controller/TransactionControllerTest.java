package it.unibg.jarfin.accounting_service.controller;

import static org.hamcrest.Matchers.containsString;
import static org.mockito.ArgumentMatchers.any;
import static org.mockito.ArgumentMatchers.eq;
import static org.mockito.Mockito.doNothing;
import static org.mockito.Mockito.verify;
import static org.mockito.Mockito.when;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.delete;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.get;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.post;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.put;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.header;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.jsonPath;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

import java.math.BigDecimal;
import java.time.LocalDate;
import java.util.Collections;
import java.util.List;

import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.autoconfigure.web.servlet.WebMvcTest;
import org.springframework.boot.test.mock.mockito.MockBean;
import org.springframework.http.MediaType;
import org.springframework.test.web.servlet.MockMvc;

import com.fasterxml.jackson.databind.ObjectMapper;

import it.unibg.jarfin.accounting_service.dto.TransactionRequest;
import it.unibg.jarfin.accounting_service.dto.TransactionResponse;
import it.unibg.jarfin.accounting_service.mapper.TransactionMapper;
import it.unibg.jarfin.accounting_service.model.Transaction;
import it.unibg.jarfin.accounting_service.model.TransactionType;
import it.unibg.jarfin.accounting_service.service.TransactionService;

@WebMvcTest(TransactionController.class)
class TransactionControllerTest {

    @Autowired
    private MockMvc mockMvc;

    @Autowired
    private ObjectMapper objectMapper;

    @MockBean
    private TransactionService service;

    @MockBean
    private TransactionMapper mapper;

    private TransactionRequest requestDto;
    private TransactionResponse responseDto;
    private Transaction entity;

    /**
     * Initializes the requestDto, entity and responseDto objects
     * before each test with sample data.
     */
    @BeforeEach
    void setUp() {
        requestDto = new TransactionRequest();
        requestDto.setAmount(new BigDecimal("150.00"));
        requestDto.setCategory("Spesa");
        requestDto.setDescription("Test JUnit");
        requestDto.setDate(LocalDate.now());
        requestDto.setType(TransactionType.EXPENSE);

        entity = new Transaction();
        entity.setId(1L);
        entity.setAmount(new BigDecimal("150.00"));
        entity.setCategory("Spesa");
        entity.setDescription("Test JUnit");

        responseDto = new TransactionResponse();
        responseDto.setId(1L);
        responseDto.setAmount(new BigDecimal("150.00"));
        responseDto.setCategory("Spesa");
        responseDto.setDescription("Test JUnit");
    }

    /**
     * Verifies that creating a new transaction returns an HTTP 201 Created response
     * with the Location header containing the URI of the newly created resource,
     * and that the returned data is correct.
     * * @throws Exception
     */
    @Test
    @DisplayName("POST /api/transactions - Dovrebbe ritornare 201 Created e Location Header")
    void createTest() throws Exception {
        when(mapper.toEntity(any(TransactionRequest.class))).thenReturn(entity);
        when(service.saveTransaction(any(Transaction.class))).thenReturn(entity);
        when(mapper.toResponse(any(Transaction.class))).thenReturn(responseDto);

        mockMvc.perform(post("/api/transactions")
                .contentType(MediaType.APPLICATION_JSON)
                .content(objectMapper.writeValueAsString(requestDto)))

                .andExpect(status().isCreated())

                .andExpect(header().string("Location", containsString("/api/transactions/1")))

                .andExpect(jsonPath("$.id").value(1))
                .andExpect(jsonPath("$.description").value("Test JUnit"));
    }

    /**
     * Verifies that the GET /api/transactions request returns a list of
     * transactions.
     * The list must contain a single element with an amount of 150.00.
     * * @throws Exception
     */
    @Test
    @DisplayName("GET /api/transactions - Dovrebbe ritornare lista di transazioni")
    void findAllTest() throws Exception {
        List<Transaction> transactions = Collections.singletonList(entity);

        when(service.getAllTransactions()).thenReturn(transactions);
        when(mapper.toResponse(any(Transaction.class))).thenReturn(responseDto);

        mockMvc.perform(get("/api/transactions")
                .accept(MediaType.APPLICATION_JSON))
                .andExpect(status().isOk())
                .andExpect(jsonPath("$.size()").value(1))
                .andExpect(jsonPath("$[0].amount").value(150.00));
    }

    /**
     * Verifies that the GET /api/transactions/{id} endpoint returns a single
     * transaction
     * matching the given id.
     * 
     * The test mocks the service to return a transaction entity when given the id,
     * and
     * the mapper to return a transaction response DTO when given the transaction
     * entity.
     * 
     * The test then performs the GET request and asserts that the response status
     * is OK
     * and that the returned transaction has the correct id.
     * 
     * @throws Exception
     */
    @Test
    @DisplayName("GET /api/transactions/{id} - Dovrebbe ritornare singola transazione")
    void getByIdTest() throws Exception {
        Long id = 1L;
        when(service.getTransactionById(id)).thenReturn(entity);
        when(mapper.toResponse(entity)).thenReturn(responseDto);

        mockMvc.perform(get("/api/transactions/{id}", id)
                .accept(MediaType.APPLICATION_JSON))
                .andExpect(status().isOk())
                .andExpect(jsonPath("$.id").value(1));
    }

    /**
     * Verifies that updating an existing transaction returns an HTTP 200 OK
     * response
     * with the updated data.
     * 
     * The test mocks the service to return a transaction entity when given the id,
     * and
     * the mapper to return a transaction response DTO when given the transaction
     * entity.
     * 
     * The test then performs the PUT request and asserts that the response status
     * is OK and that the returned transaction has the correct description.
     * 
     * @throws Exception
     */
    @Test
    @DisplayName("PUT /api/transactions/{id} - Dovrebbe aggiornare e ritornare 200 OK")
    void updateTest() throws Exception {
        Long id = 1L;

        when(mapper.toEntity(any(TransactionRequest.class))).thenReturn(entity);
        when(service.updateTransaction(eq(id), any(Transaction.class))).thenReturn(entity);
        when(mapper.toResponse(any(Transaction.class))).thenReturn(responseDto);

        mockMvc.perform(put("/api/transactions/{id}", id)
                .contentType(MediaType.APPLICATION_JSON)
                .content(objectMapper.writeValueAsString(requestDto)))
                .andExpect(status().isOk())
                .andExpect(jsonPath("$.description").value("Test JUnit"));
    }

    /**
     * Verifies that deleting an existing transaction returns an HTTP 204 No Content
     * response.
     * The test mocks the service to do nothing when given the id, and then
     * performs the DELETE request and asserts that the response status is No
     * Content.
     * Finally, the test verifies that the service's delete method was called with
     * the id.
     * 
     * @throws Exception
     */
    @Test
    @DisplayName("DELETE /api/transactions/{id} - Dovrebbe ritornare 204 No Content")
    void deleteTest() throws Exception {
        Long id = 1L;
        doNothing().when(service).deleteTransaction(id);

        mockMvc.perform(delete("/api/transactions/{id}", id))
                .andExpect(status().isNoContent());

        verify(service).deleteTransaction(id);
    }

    /**
     * Verifies that creating a transaction with a null amount returns an HTTP 400
     * Bad Request response.
     * The test sets the amount of the request DTO to null and then performs the
     * POST request.
     * Finally, the test asserts that the response status is Bad Request.
     * 
     * @throws Exception
     */
    @Test
    @DisplayName("POST /api/transactions - Validazione input errato (Amount null)")
    void createInvalidInputTest() throws Exception {
        requestDto.setAmount(null);

        mockMvc.perform(post("/api/transactions")
                .contentType(MediaType.APPLICATION_JSON)
                .content(objectMapper.writeValueAsString(requestDto)))
                .andExpect(status().isBadRequest());
    }
}